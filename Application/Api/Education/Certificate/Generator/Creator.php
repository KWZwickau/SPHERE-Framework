<?php

namespace SPHERE\Application\Api\Education\Certificate\Generator;

use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Document;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Education\Certificate\Generator\Repository\MultiCertificate;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Layout\Repository\Paragraph;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Window\Display;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\RedirectScript;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\PdfMerge;

/**
 * Class Creator
 *
 * @package SPHERE\Application\Api\Education\Certificate\Generator
 */
class Creator extends Extension
{
    /**
     * @param Certificate $Certificate
     * @param TblPerson $tblPerson
     * @param array $Data
     *
     * @return FilePointer
     */
    private function buildDummyFile(Certificate $Certificate, TblPerson $tblPerson, array $Data = array()): FilePointer
    {
        $tblYear = $Data['Division']['Data']['Year'] ?? '';
        $personName = '';
        if (isset($Data['Person']['Data']['Name']['First']) && isset($Data['Person']['Data']['Name']['Last'])) {
            $personName = $Data['Person']['Data']['Name']['Last'] . ', ' . $Data['Person']['Data']['Name']['First'];
        }
        $Prefix = md5($tblYear . $personName . ($Data['Person']['Student']['Id'] ?? ''));

        // Create Tmp
        $File = Storage::createFilePointer('pdf', $Prefix);
        $pageList[$tblPerson->getId()] = $Certificate->buildPages($tblPerson);
        $bridge = $Certificate->createCertificate($Data, $pageList);
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($File->getFileLocation());
        $Document->setContent($bridge);
        $Document->saveFile(new FileParameter($File->getFileLocation()));

        return $File;
    }

    private static function buildMultiDummyFile($Data = array(), $pageList = array(), $certificateList = array()): FilePointer
    {
        ini_set('memory_limit', '1G');

        $MultiCertificate = new MultiCertificate();

        // Create Tmp
        $File = Storage::createFilePointer('pdf');
        /** @var DomPdf $Document */
        $Document = Document::getPdfDocument($File->getFileLocation());
        $Content = $MultiCertificate->createCertificate($Data, $pageList, $certificateList);
        $Document->setContent($Content);
        $Document->saveFile(new FileParameter($File->getFileLocation()));

        return $File;
    }

    /**
     * @param FilePointer $File
     * @param string $FileName
     *
     * @return string
     */
    private static function buildDownloadFile(FilePointer $File, string $FileName = ''): string
    {
        return FileSystem::getStream(
            $File->getRealPath(),
            $FileName ?: "Zeugnis-Test-" . date("Y-m-d H:i:s") . ".pdf"
        )->__toString();
    }

    /**
     * @param null $PrepareId
     * @param null $PersonId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public function previewPdf($PrepareId = null, $PersonId = null, $Name = 'Zeugnis Muster', $Redirect = true)
    {
        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/Preview', array(
                'PrepareId' => $PrepareId,
                'PersonId' => $PersonId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblYear = $tblPrepare->getYear())
            && ($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
            && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
        ) {
            $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();
            if (class_exists($CertificateClass)) {
                $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                /** @var Certificate $Certificate */
                $Certificate = new $CertificateClass($tblStudentEducation ?: null, $tblPrepare);

                // get Content
                $Content = Prepare::useService()->createCertificateContent($tblPerson, $tblPrepareStudent);
                $personId = $tblPerson->getId();
                if (isset($Content['P' . $personId]['Grade'])) {
                    $Certificate->setGrade($Content['P' . $personId]['Grade']);
                }
                if (isset($Content['P' . $personId]['AdditionalGrade'])) {
                    $Certificate->setAdditionalGrade($Content['P' . $personId]['AdditionalGrade']);
                }

                $File = $this->buildDummyFile($Certificate, $tblPerson, $Content);

                $FileName = $Name . " " . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d H:i:s") . ".pdf";

                return $this->buildDownloadFile($File, $FileName);
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param null $LeaveStudentId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return Display|Stage|string
     */
    public function previewLeavePdf($LeaveStudentId = null, $Name = 'Zeugnis Muster', $Redirect = true)
    {
        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/PreviewLeave', array(
                'LeaveStudentId' => $LeaveStudentId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        if (($tblLeaveStudent = Prepare::useService()->getLeaveStudentById($LeaveStudentId))
            && ($tblPerson = $tblLeaveStudent->getServiceTblPerson())
            && ($tblYear = $tblLeaveStudent->getServiceTblYear())
        ) {
            if (($tblCertificate = $tblLeaveStudent->getServiceTblCertificate())) {
                $CertificateClass = '\SPHERE\Application\Api\Education\Certificate\Generator\Repository\\' . $tblCertificate->getCertificate();

                if (class_exists($CertificateClass)) {
                    $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                    /** @var Certificate $Certificate */
                    $Certificate = new $CertificateClass($tblStudentEducation ?: null);

                    // get Content
                    $Content = Prepare::useService()->createCertificateContent($tblPerson, null, $tblLeaveStudent);
                    $personId = $tblPerson->getId();
                    if (isset($Content['P' . $personId]['Grade'])) {
                        $Certificate->setGrade($Content['P' . $personId]['Grade']);
                    }

                    $File = $this->buildDummyFile($Certificate, $tblPerson, $Content);

                    $FileName = $Name . " " . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d H:i:s") . ".pdf";

                    return $this->buildDownloadFile($File, $FileName);
                }
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param null $FileId
     *
     * @return Stage|string
     */
    public function downloadPdf($FileId = null)
    {
        if (($tblFile = Storage::useService()->getFileById($FileId))) {

            $File = Storage::createFilePointer('pdf');
            $File->setFileContent(stream_get_contents($tblFile->getTblBinary()->getBinaryBlob()));
            $File->saveFile();

            return FileSystem::getStream($File->getFileLocation(),
                $tblFile->getName()
                . " " . date("Y-m-d H:i:s") . ".pdf")->__toString();

        } else {

            return new Stage('Zeugnis', 'Nicht gefunden');
        }
    }

    /**
     * @param null $PrepareId
     * @param string $Name
     *
     * @return Stage|string
     */
    public function downloadHistoryZip($PrepareId = null, string $Name = 'Zeugnis')
    {
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblPrepareStudentList = Prepare::useService()->getPrepareStudentAllByPrepare($tblPrepare))
        ) {
            $FileList = array();
            foreach ($tblPrepareStudentList as $tblPrepareStudent) {
                if (($tblPerson = $tblPrepareStudent->getServiceTblPerson())) {
                    $tblFileList = Storage::useService()->getCertificateRevisionFileAllByPerson($tblPerson);
                    if ($tblFileList) {
                        foreach ($tblFileList as $tblFile) {
                            $name = explode(' - ', $tblFile->getName());
                            if (count($name) >= 4 && $name[3] == $tblPrepare->getId()) {
                                $personLastName = str_replace('ä', 'ae', $tblPerson->getLastName());
                                $personLastName = str_replace('ü', 'ue', $personLastName);
                                $personLastName = str_replace('ö', 'oe', $personLastName);
                                $personLastName = str_replace('ß', 'ss', $personLastName);
                                $File = Storage::createFilePointer('pdf', $Name . '-' . $personLastName
                                    . '-' . date('Y-m-d') . '--');
                                $File->setFileContent(stream_get_contents($tblFile->getTblBinary()->getBinaryBlob()));
                                $File->saveFile();

                                $FileList[] = $File;
                            }
                        }
                    }
                }
            }

            if (!empty($FileList)) {
//                $ZipFile = new FilePointer('zip');
//                $ZipFile->saveFile();
                $MergeFile = Storage::createFilePointer('pdf');
                $PdfMerger = new PdfMerge();

//                $ZipArchive = $this->getPacker($ZipFile->getRealPath());
                /** @var FilePointer $File */
                foreach ($FileList as $File) {
//                    $ZipArchive->compactFile(
//                        new \MOC\V\Component\Packer\Component\Parameter\Repository\FileParameter(
//                            $File->getRealPath()
//                        )
//                        , false);
                    $PdfMerger->addPdf($File);
                }

                // mergen aller hinzugefügten PDF-Datein
                $PdfMerger->mergePdf($MergeFile);

                // aufräumen der Temp-Files
                /** @var FilePointer $File */
                foreach($FileList as $File){
                    $File->setDestruct();
                }

//                return FileSystem::getDownload(
//                    $MergeFile->getRealPath(),
//                    $Name . '-' . $tblDivision->getDisplayName() . '-' . date("Y-m-d H:i:s") . ".pdf"
//                )->__toString();
                return FileSystem::getStream(
                    $MergeFile->getRealPath(),
                    $Name . '-' . $tblDivisionCourse->getName() . '-' . date("Y-m-d H:i:s") . ".pdf"
                )->__toString();
            } else {
                return new Stage($Name, 'Keine weiteren Zeugnisse zum Druck bereit.')
                    . new Redirect('/Education/Certificate/PrintCertificate');
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param null $PrepareId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return string
     */
    public static function previewMultiPdf($PrepareId = null, $Name = 'Zeugnis', $Redirect = true)
    {
        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/PreviewMultiPdf', array(
                'PrepareId' => $PrepareId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        $pageList = array();
        $certificateList = array();
        $Data = array();
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblYear = $tblPrepare->getYear())
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $description = $tblDivisionCourse->getName();
            foreach ($tblPersonList as $tblPerson) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                ) {
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
                    }
                }
            }

            if (!empty($pageList)) {
                $File = self::buildMultiDummyFile($Data, $pageList, $certificateList);
                $FileName = $Name . ' ' . ($description ?: '-') . ' ' . date("Y-m-d") . ".pdf";

                return self::buildDownloadFile($File, $FileName);
            }
        }

        return "Keine Zeugnisse vorhanden!";
    }

    /**
     * @param null $DivisionId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return Display|string
     */
    public static function previewMultiLeavePdf($DivisionId = null, $Name = 'Abgangszeugnis', $Redirect = true)
    {
        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/PreviewMultiLeavePdf', array(
                'DivisionId' => $DivisionId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        $pageList = array();
        $certificateList = array();
        $Data = array();
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblYear))
        ) {
            foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                if (($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                    && ($tblCertificate = $tblLeaveStudent->getServiceTblCertificate())
                    && ($tblDivisionCourseLeave = $tblLeaveStudent->getTblDivisionCourse())
                    && ($tblDivisionCourseLeave->getId() == $tblDivisionCourse->getId())
                ) {
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
                    }
                }
            }

            if (!empty($pageList)) {
                $File = self::buildMultiDummyFile($Data, $pageList, $certificateList);
                $FileName = $Name . ' ' . $tblDivisionCourse->getDisplayName() . ' ' . date("Y-m-d") . ".pdf";

                return self::buildDownloadFile($File, $FileName);
            }
        }

        return "Keine Zeugnisse vorhanden!";
    }

    /**
     * @param null $PrepareId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return string
     */
    public static function downloadMultiPdf($PrepareId = null, $Name = 'Zeugnis', $Redirect = true)
    {
        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/DownLoadMultiPdf', array(
                'PrepareId' => $PrepareId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        $pageList = array();
        $certificateList = array();
        $Data = array();
        if (($tblPrepare = Prepare::useService()->getPrepareById($PrepareId))
            && ($tblYear = $tblPrepare->getYear())
            && ($tblDivisionCourse = $tblPrepare->getServiceTblDivision())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $description = $tblDivisionCourse->getName();
            if (($tblCertificateType = $tblPrepare->getCertificateType())
                && $tblCertificateType->isAutomaticallyApproved()
            ) {
                $isAutomaticallyApproved = true;
            } else {
                $isAutomaticallyApproved = false;
            }

            foreach ($tblPersonList as $tblPerson) {
                if (($tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson))
                    && ($tblCertificate = $tblPrepareStudent->getServiceTblCertificate())
                    && !$tblPrepareStudent->isPrinted()
                ) {
                    $isApproved = $tblPrepareStudent->isApproved();
                    // bei automatischer Freigabe --> freigeben + kopieren der Fehlzeiten (optional)
                    if (!$isApproved && $isAutomaticallyApproved) {
                        Prepare::useService()->updatePrepareStudentSetApproved($tblPrepareStudent);
                        $tblPrepareStudent = Prepare::useService()->getPrepareStudentBy($tblPrepare, $tblPerson, true);
                        $isApproved = true;
                    }

                    if ($isApproved) {
                        ini_set('memory_limit', '1G');
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

                            $personLastName = str_replace('ä', 'ae', $tblPerson->getLastName());
                            $personLastName = str_replace('ü', 'ue', $personLastName);
                            $personLastName = str_replace('ö', 'oe', $personLastName);
                            $personLastName = str_replace('ß', 'ss', $personLastName);
                            $File = Storage::createFilePointer('pdf', $Name . '-' . $personLastName
                                . '-' . date('Y-m-d') . '--');
                            /** @var DomPdf $Document */
                            $Document = Document::getPdfDocument($File->getFileLocation());
                            $Content = $Certificate->createCertificate($Data, array(0 => $page));
                            $Document->setContent($Content);
                            $Document->saveFile(new FileParameter($File->getFileLocation()));

                            if (Storage::useService()->saveCertificateRevision($tblPerson, $tblDivisionCourse, $Certificate, $File, $tblPrepare)) {
                                Prepare::useService()->updatePrepareStudentSetPrinted($tblPrepareStudent);
                            }
                        }
                    }
                }
            }

            if (!empty($pageList)) {
                $File = self::buildMultiDummyFile($Data, $pageList, $certificateList);
                $FileName = $Name . ' ' . $description . ' ' . date("Y-m-d") . ".pdf";

                return self::buildDownloadFile($File, $FileName);

            } else {

                return new Stage($Name, 'Keine weiteren Zeugnisse zum Druck bereit.')
                    . new Redirect('/Education/Certificate/PrintCertificate');
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param null $DivisionId
     * @param string $Name
     * @param bool $Redirect
     *
     * @return Display|Stage|string
     */
    public static function downloadMultiLeavePdf($DivisionId = null, $Name = 'Abgangszeugnis', $Redirect = true)
    {
        if ($Redirect) {
            return self::displayWaitingPage('/Api/Education/Certificate/Generator/DownLoadMultiLeavePdf', array(
                'DivisionId' => $DivisionId,
                'Name' => $Name,
                'Redirect' => 0
            ));
        }

        $pageList = array();
        $certificateList = array();
        $Data = array();
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblLeaveStudentList = Prepare::useService()->getLeaveStudentAllByYear($tblYear))
        ) {
            if (($tblCertificateTypeLeave = Generator::useService()->getCertificateTypeByIdentifier('LEAVE'))
                && $tblCertificateTypeLeave->isAutomaticallyApproved()
            ) {
                $isAutomaticallyApproved = true;
            } else {
                $isAutomaticallyApproved = false;
            }

            foreach ($tblLeaveStudentList as $tblLeaveStudent) {
                if (($tblPerson = $tblLeaveStudent->getServiceTblPerson())
                    && ($tblCertificate = $tblLeaveStudent->getServiceTblCertificate())
                    && ($tblDivisionCourseLeave = $tblLeaveStudent->getTblDivisionCourse())
                    && ($tblDivisionCourseLeave->getId() == $tblDivisionCourse->getId())
                    && !$tblLeaveStudent->isPrinted()
                ) {
                    $isApproved = $tblLeaveStudent->isApproved();
                    if ($isApproved || $isAutomaticallyApproved) {
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
                            $pageList[$tblPerson->getId()] = $page;
                            if (isset($certificateList[$tblCertificate->getCertificate()])) {
                                $certificateList[$tblCertificate->getCertificate()]++;
                            } else {
                                $certificateList[$tblCertificate->getCertificate()] = 1;
                            }

                            $personLastName = str_replace('ä', 'ae', $tblPerson->getLastName());
                            $personLastName = str_replace('ü', 'ue', $personLastName);
                            $personLastName = str_replace('ö', 'oe', $personLastName);
                            $personLastName = str_replace('ß', 'ss', $personLastName);
                            $File = Storage::createFilePointer('pdf', $Name . '-' . $personLastName
                                . '-' . date('Y-m-d') . '--');
                            /** @var DomPdf $Document */
                            $Document = Document::getPdfDocument($File->getFileLocation());
                            $Content = $Certificate->createCertificate($Data, array(0 => $page));
                            $Document->setContent($Content);
                            $Document->saveFile(new FileParameter($File->getFileLocation()));

                            // Revisionssicher speichern
                            if (Storage::useService()->saveCertificateRevision($tblPerson, $tblDivisionCourse, $Certificate, $File)) {
                                Prepare::useService()->updateLeaveStudent($tblLeaveStudent, true, true);
                            }
                        }
                    }
                }
            }

            if (!empty($pageList)) {
                $File = self::buildMultiDummyFile($Data, $pageList, $certificateList);
                $FileName = $Name . ' ' . $tblDivisionCourse->getDisplayName() . ' ' . date("Y-m-d") . ".pdf";

                return self::buildDownloadFile($File, $FileName);

            } else {

                return new Stage($Name, 'Keine weiteren Zeugnisse zum Druck bereit.')
                    . new Redirect('/Education/Certificate/PrintCertificate');
            }
        }

        return new Stage($Name, 'Nicht gefunden');
    }

    /**
     * @param string $Route
     * @param array $parameters
     *
     * @return Display
     */
    public static function displayWaitingPage(string $Route, array $parameters): Display
    {
        $Display = new Display();
        $Stage = new Stage('Dokument wird vorbereitet');
        $Stage->setContent(new Layout(new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        new Paragraph('Dieser Vorgang kann längere Zeit in Anspruch nehmen.'),
                        (new ProgressBar(0, 100, 0, 10))->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS),
                        new Paragraph('Bitte warten ..'),
                        "<button type=\"button\" class=\"btn btn-default\" onclick=\"window.open('', '_self', ''); window.close();\">Abbrechen</button>"
                    ), 4),
                )),
                new LayoutRow(
                    new LayoutColumn(
                        new RedirectScript($Route, 0, $parameters)
                    )
                ),
            )))
        );
        $Display->setContent($Stage);

        return $Display;
    }
}
