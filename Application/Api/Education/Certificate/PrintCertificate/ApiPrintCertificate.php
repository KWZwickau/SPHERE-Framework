<?php

namespace SPHERE\Application\Api\Education\Certificate\PrintCertificate;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Api\Education\Certificate\Generator\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generator\Creator;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\PrintCertificate\PrintCertificate;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
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
     *
     * @return Pipeline
     */
    public static function pipelineLoadCertificate($prepareStudentId, array $tblPrepareStudentList, string $filePointerList): Pipeline
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
//            'tblPrepareStudentList' => $tblPrepareStudentList,
//            'filePointerList' => $filePointerList,
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
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $prepareStudentId
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function waitContent($prepareStudentId): string
    {
        $content = 'Inhalt lädt...';
        if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentById($prepareStudentId))
            && ($tblPerson = $tblPrepareStudent->getServiceTblPerson())
        ) {
            $content = "Zeugnis für {$tblPerson->getLastFirstName()} wird erstellt. Bitte warten...";
        }

        return new Info($content . new ProgressBar(0, 100, 0, 12));
    }

    /**
     * @param $prepareStudentId
     * @param array $tblPrepareStudentList
     * @param string $filePointerList
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadCertificate($prepareStudentId, array $tblPrepareStudentList, string $filePointerList): string
    {
        $filePointerList = json_decode($filePointerList, true);

        $pdfName = 'Musterzeugnisse ';
        $message = '';
        $tblPrepare = null;
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
                // Todo allgemeiner das es auch für abgangszeugnisse und den richtigen Druck funktioniert
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

        unset($tblPrepareStudentList[$prepareStudentId]);
        // todo auf 0 setzen
        if (count($tblPrepareStudentList) > 23) {
            $prepareStudentNextId = array_key_first($tblPrepareStudentList);

            return $message
                . self::receiverBlock(self::pipelineLoadCertificate($prepareStudentNextId, $tblPrepareStudentList, json_encode($filePointerList)),
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


        if ($tblPrepare
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
        ) {
            $pdfName .= $tblDivisionCourse->getName();
        }

        return $message . new Redirect('/Api/Education/Certificate/Generator/FileLocation/DownloadPdf', Redirect::TIMEOUT_SUCCESS, [
            'FileLocation' => $MergeFile->getRealPath(),
            'Name' => $pdfName
        ]);
    }
}