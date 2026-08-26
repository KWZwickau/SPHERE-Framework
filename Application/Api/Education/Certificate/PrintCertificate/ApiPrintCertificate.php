<?php

namespace SPHERE\Application\Api\Education\Certificate\PrintCertificate;

use Exception;
use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generator\Creator;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Service\Entity\TblBinary;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\PrintCertificate\PrintCertificate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\PdfMerge;

class ApiPrintCertificate extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('searchPerson');
        $Dispatcher->registerMethod('waitContent');
        $Dispatcher->registerMethod('loadCertificate');

        $Dispatcher->registerMethod('getReload');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineSearchPerson(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SearchContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'searchPerson',
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen.');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function searchPerson($Data = null): string
    {
        return PrintCertificate::useFrontend()->loadPersonSearch(isset($Data['Search']) ? trim($Data['Search']) : '');
    }

    /**
     * @param $prepareStudentId
     * @param array $tblPrepareStudentList
     * @param string $filePointerList
     * @param string $name
     * @param string $type
     *
     * @return Pipeline
     */
    public static function pipelineLoadCertificate($prepareStudentId, array $tblPrepareStudentList, string $filePointerList, string $name, string $type): Pipeline
    {
        $pipeline = new Pipeline();
        $receiver = self::receiverBlock('', 'Content_' . $prepareStudentId);

        // show waiting
        $emitter = new ServerEmitter($receiver, self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'waitContent',
        ));
        $emitter->setPostPayload(array(
            'prepareStudentId' => $prepareStudentId,
            'type' => $type,
        ));
        $pipeline->appendEmitter($emitter);

        // show content
        $emitter = new ServerEmitter($receiver, self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadCertificate',
        ));
        $emitter->setPostPayload(array(
            'prepareStudentId' => $prepareStudentId,
            'tblPrepareStudentList' => $tblPrepareStudentList,
            'filePointerList' => $filePointerList,
            'name' => $name,
            'type' => $type
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $prepareStudentId
     * @param string $type
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function waitContent($prepareStudentId, string $type): string
    {
        $content = 'Inhalt lädt...';

        if (str_contains($type, 'PREPARE_STUDENT')) {
            if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentById($prepareStudentId))
                && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            ) {
                $content = "Zeugnis für {$tblPerson->getLastFirstName()} wird erstellt. Bitte warten...";
            }
        } else {
            if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($prepareStudentId))
                && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
            ) {
                $content = "Abgangszeugnis für {$tblPerson->getLastFirstName()} wird erstellt. Bitte warten...";
            }
        }

        return new Info($content . new ProgressBar(0, 100, 0, 12));
    }

    /**
     * @param $prepareStudentId
     * @param array $tblPrepareStudentList
     * @param string $filePointerList
     * @param string $name
     * @param string $type
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadCertificate($prepareStudentId, array $tblPrepareStudentList, string $filePointerList, string $name, string $type): string
    {
        $filePointerList = json_decode($filePointerList, true);

        $message = '';
        $isPreview = true;
        // tblPrepareStudent (Normales Zeugnis)
        if ($type == 'PREPARE_STUDENT_PREVIEW') {
            $message = $this->setPrepareStudentPreview($prepareStudentId, $filePointerList);
        } elseif ($type == 'PREPARE_STUDENT_DOWNLOAD') {
            $message = $this->setPrepareStudentDownload($prepareStudentId, $filePointerList, $name);
            $isPreview = false;
        // tblLeaveStudent (Abgangszeugnis)
        } elseif ($type == 'LEAVE_STUDENT_PREVIEW') {
            $message = $this->setLeaveStudentPreview($prepareStudentId, $filePointerList);
        } elseif ($type == 'LEAVE_STUDENT_DOWNLOAD') {
            $message = $this->setLeaveStudentDownload($prepareStudentId, $filePointerList, $name);
            $isPreview = false;
        }

        unset($tblPrepareStudentList[$prepareStudentId]);
        if (count($tblPrepareStudentList) > 0) {
            $prepareStudentNextId = array_key_first($tblPrepareStudentList);

            return $message
                . self::receiverBlock(self::pipelineLoadCertificate($prepareStudentNextId, $tblPrepareStudentList, json_encode($filePointerList), $name, $type),
                    'Content_' . $prepareStudentNextId);
        }

        // PdfMerger kann keine PNG's sondern nur JPEG
        $MergeFile = Storage::createFilePointer('pdf', 'SPHERE-Temporary-Merge', false);
        $PdfMerger = new PdfMerge();
        $tblFilePointerDeleteList = [];
        foreach ($filePointerList as $fileLocation) {
            if ($fileLocation) {
                $tblFile = FilePointer::fromFileLocation($fileLocation, false);
                $tblFilePointerDeleteList[] = $tblFile;

                $PdfMerger->addPdf($tblFile);
            }
        }
        // mergen aller hinzugefügten PDF-Dateien
        $PdfMerger->mergePdf($MergeFile);
        // aufräumen der Temp-Files
        foreach ($tblFilePointerDeleteList as $tblFile) {
            $tblFile->setDestruct();
        }

        // Redirect für warte Seite
        if (!$isPreview) {
            Consumer::useService()->createAccountSetting('IsPrintCertificateReload', 'True');
        }

        return $message . new Redirect('/Api/Education/Certificate/Generator/FileLocation/DownloadPdf', Redirect::TIMEOUT_SUCCESS, [
            'FileLocation' => $MergeFile->getRealPath(),
            'Name' => $name
        ]);
    }

    /**
     * @param $prepareStudentId
     * @param array $filePointerList
     *
     * @return string
     */
    private function setPrepareStudentPreview($prepareStudentId, array &$filePointerList): string
    {
        $message = '';
        if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentById($prepareStudentId))
            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblYear = $tblPrepare->getYear())
        ) {
            $message = new Success("Zeugnis für {$tblPerson->getLastFirstName()} erfolgreich erstellt.", new Check());

            ini_set('memory_limit', '2G');
            $Data = [];
            $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
            if (class_exists($CertificateClass)) {
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                /** @var Certificate $Certificate */
                $Certificate = new $CertificateClass($tblStudentEducation ?: null, $tblPrepare);

                // get Content
                Prepare::useService()->createCertificateContent($tblPerson, $tblPrepareStudent, null, $Data);
                $personId = $tblPerson->getId();
                if (isset($Data['P' . $personId]['Grade'])) {
                    $Certificate->setGrade($Data['P' . $personId]['Grade']);
                }
                if (isset($Data['P' . $personId]['AdditionalGrade'])) {
                    $Certificate->setAdditionalGrade($Data['P' . $personId]['AdditionalGrade']);
                }

                $page = $Certificate->buildPages($tblPerson);
                $pageList[$tblPerson->getId()] = $page;

                if (isset($certificateList[$tblCertificate->getCertificate()])) {
                    $certificateList[$tblCertificate->getCertificate()]++;
                } else {
                    $certificateList[$tblCertificate->getCertificate()] = 1;
                }

                if (($filePointer = Creator::buildMultiDummyFile($Data, $pageList, $certificateList, false))) {
                    $filePointerList[$prepareStudentId] = $filePointer->getRealPath();
                }
            }
        }

        return $message;
    }

    /**
     * @param $prepareStudentId
     * @param array $filePointerList
     * @param string $name
     *
     * @return string
     */
    private function setPrepareStudentDownload($prepareStudentId, array &$filePointerList, string $name): string
    {
        $message = '';
        if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentById($prepareStudentId))
            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
            && ($tblPrepare = $tblPrepareStudent->getTblPrepareCertificate())
            && ($tblYear = $tblPrepare->getYear())
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && !$tblPrepareStudent->isPrinted()
        ) {
            if (($tblCertificateType = $tblPrepare->getCertificateType())
                && $tblCertificateType->isAutomaticallyApproved()
            ) {
                $isAutomaticallyApproved = true;
            } else {
                $isAutomaticallyApproved = false;
            }

            $isApproved = $tblPrepareStudent->isApproved();
            // bei automatischer Freigabe → freigeben + kopieren der Fehlzeiten (optional)
            if (!$isApproved && $isAutomaticallyApproved) {
                Prepare::useService()->updatePrepareStudentSetApproved($tblPrepareStudent);
                $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson, true);
            }

            $message = new Success("Zeugnis für {$tblPerson->getLastFirstName()} erfolgreich erstellt.", new Check());

            ini_set('memory_limit', '2G');
            $Data = [];
            $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
            if (class_exists($CertificateClass)) {
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                /** @var Certificate $Certificate */
                $Certificate = new $CertificateClass($tblStudentEducation ?: null, $tblPrepare, false);

                // get Content
                Prepare::useService()->createCertificateContent($tblPerson, $tblPrepareStudent, null, $Data);
                $personId = $tblPerson->getId();
                if (isset($Data['P' . $personId]['Grade'])) {
                    $Certificate->setGrade($Data['P' . $personId]['Grade']);
                }
                if (isset($Data['P' . $personId]['AdditionalGrade'])) {
                    $Certificate->setAdditionalGrade($Data['P' . $personId]['AdditionalGrade']);
                }

                $page = $Certificate->buildPages($tblPerson);

                $File = Storage::createFilePointer('pdf', $name . '-' . $this->getCertificatePersonName($tblPerson)
                    . '-' . date('Y-m-d') . '--', false);
                /** @var DomPdf $Document */
                $Document = Document::getPdfDocument($File->getFileLocation());
                $Content = $Certificate->createCertificate($Data, array(0 => $page));
                $Document->setContent($Content);
                // hier den hash erzeugen
                $hash = TblBinary::getHashByContent($Document->getSource());
                $Document->saveFile(new FileParameter($File->getFileLocation()));

                try {
                    $fileSizeKiloByte = intdiv(filesize($File->getFileLocation()), 1024);
                } catch (Exception) {
                    $fileSizeKiloByte = 0;
                }

                if (Storage::useService()->saveCertificateRevision($tblPerson, $tblDivisionCourse, $Certificate, $File, $fileSizeKiloByte, $hash, $tblPrepare)) {
                    Prepare::useService()->updatePrepareStudentSetPrinted($tblPrepareStudent);
                }

                $filePointerList[$prepareStudentId] = $File->getRealPath();
            }
        }

        return $message;
    }

    /**
     * @param $leaveStudentId
     * @param array $filePointerList
     *
     * @return string
     */
    private function setLeaveStudentPreview($leaveStudentId, array &$filePointerList): string
    {
        $message = '';
        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($leaveStudentId))
            && ($tblCertificate = $tblLeaveStudent->getServiceTblCertificate())
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
            && ($tblYear = $tblLeaveStudent->getServiceTblYear())
        ) {
            $message = new Success("Abgangszeugnis für {$tblPerson->getLastFirstName()} erfolgreich erstellt.", new Check());

            ini_set('memory_limit', '2G');
            $Data = [];
            $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
            if (class_exists($CertificateClass)) {
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                /** @var Certificate $Certificate */
                $Certificate = new $CertificateClass($tblStudentEducation ?: null);

                // get Content
                Prepare::useService()->createCertificateContent($tblPerson, null, $tblLeaveStudent, $Data);
                $personId = $tblPerson->getId();
                if (isset($Data['P' . $personId]['Grade'])) {
                    $Certificate->setGrade($Data['P' . $personId]['Grade']);
                }

                $page = $Certificate->buildPages($tblPerson);
                $pageList[$personId] = $page;

                if (isset($certificateList[$tblCertificate->getCertificate()])) {
                    $certificateList[$tblCertificate->getCertificate()]++;
                } else {
                    $certificateList[$tblCertificate->getCertificate()] = 1;
                }

                if (($filePointer = Creator::buildMultiDummyFile($Data, $pageList, $certificateList, false))) {
                    $filePointerList[$leaveStudentId] = $filePointer->getRealPath();
                }
            }
        }

        return $message;
    }

    /**
     * @param $leaveStudentId
     * @param array $filePointerList
     * @param string $name
     *
     * @return string
     */
    private function setLeaveStudentDownload($leaveStudentId, array &$filePointerList, string $name): string
    {
        $message = '';
        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($leaveStudentId))
            && ($tblCertificate = $tblLeaveStudent->getServiceTblCertificate())
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
            && ($tblYear = $tblLeaveStudent->getServiceTblYear())
            && ($tblDivisionCourse = $tblLeaveStudent->getTblDivisionCourse())
            && !$tblLeaveStudent->isPrinted()
        ) {
            $message = new Success("Abgangszeugnis für {$tblPerson->getLastFirstName()} erfolgreich erstellt.", new Check());

            ini_set('memory_limit', '2G');
            $Data = [];
            $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
            if (class_exists($CertificateClass)) {
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                /** @var Certificate $Certificate */
                $Certificate = new $CertificateClass($tblStudentEducation ?: null, null, false);

                // get Content
                Prepare::useService()->createCertificateContent($tblPerson, null, $tblLeaveStudent, $Data);
                $personId = $tblPerson->getId();
                if (isset($Data['P' . $personId]['Grade'])) {
                    $Certificate->setGrade($Data['P' . $personId]['Grade']);
                }

                $page = $Certificate->buildPages($tblPerson);


                $File = Storage::createFilePointer('pdf', $name . '-' . $this->getCertificatePersonName($tblPerson)
                    . '-' . date('Y-m-d') . '--', false);
                /** @var DomPdf $Document */
                $Document = Document::getPdfDocument($File->getFileLocation());
                $Content = $Certificate->createCertificate($Data, array(0 => $page));
                $Document->setContent($Content);
                // hier den hash erzeugen
                $hash = TblBinary::getHashByContent($Document->getSource());
                $Document->saveFile(new FileParameter($File->getFileLocation()));

                try {
                    $fileSizeKiloByte = intdiv(filesize($File->getFileLocation()), 1024);
                } catch (Exception) {
                    $fileSizeKiloByte = 0;
                }

                // Revisionssicher speichern
                if (Storage::useService()->saveCertificateRevision($tblPerson, $tblDivisionCourse, $Certificate, $File, $fileSizeKiloByte, $hash)) {
                    Prepare::useService()->updateLeaveStudent($tblLeaveStudent, true, true);
                }

                $filePointerList[$leaveStudentId] = $File->getRealPath();
            }
        }

        return $message;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    private function getCertificatePersonName(TblPerson $tblPerson): string
    {
        $personLastName = str_replace('ä', 'ae', $tblPerson->getLastName());
        $personLastName = str_replace('ü', 'ue', $personLastName);
        $personLastName = str_replace('ö', 'oe', $personLastName);

        return str_replace('ß', 'ss', $personLastName);
    }

    /**
     * @param string $BackRoute
     * @param int $Time
     *
     * @return Pipeline
     */
    public static function pipelineReload(string $BackRoute, int $Time = 5): Pipeline
    {
        $Pipeline = new Pipeline();
        // reload
        $Emitter = new ServerEmitter(self::receiverBlock('', 'reload'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getReload'
        ));
        $Emitter->setPostPayload(array(
            'BackRoute' => $BackRoute
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->repeatPipeline($Time);

        return $Pipeline;
    }

    /**
     * @param string $BackRoute
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function getReload(string $BackRoute): string
    {
        return
            Consumer::useService()->getAccountSettingValue('IsPrintCertificateReload') === 'True'
             ? new Redirect($BackRoute, Redirect::TIMEOUT_SUCCESS)
             : '';
    }
}