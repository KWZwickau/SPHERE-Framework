<?php
namespace SPHERE\Application\Api\Document;

use DateTime;
use MOC\V\Component\Document\Component\Parameter\Repository\PaperOrientationParameter;
use MOC\V\Component\Document\Document as PdfDocument;
use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Api\Document\Custom\Gersdorf\Repository\MetaDataComparison;
use SPHERE\Application\Api\Document\Standard\Repository\AccidentReport\AccidentReport;
use SPHERE\Application\Api\Document\Standard\Repository\Account\AccountApp;
use SPHERE\Application\Api\Document\Standard\Repository\Account\AccountToken;
use SPHERE\Application\Api\Document\Standard\Repository\Billing\Billing;
use SPHERE\Application\Api\Document\Standard\Repository\Billing\DocumentWarning;
use SPHERE\Application\Api\Document\Standard\Repository\ClassRegister\ClassRegister;
use SPHERE\Application\Api\Document\Standard\Repository\ClassRegister\CourseContent;
use SPHERE\Application\Api\Document\Standard\Repository\EnrollmentDocument;
use SPHERE\Application\Api\Document\Standard\Repository\Gradebook\Gradebook;
use SPHERE\Application\Api\Document\Standard\Repository\GradebookOverview;
use SPHERE\Application\Api\Document\Standard\Repository\MultiPassword\MultiPassword;
use SPHERE\Application\Api\Document\Standard\Repository\PasswordChange\PasswordChange;
use SPHERE\Application\Api\Document\Standard\Repository\SignOutCertificate\SignOutCertificate;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\AbstractStudentCard;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\GrammarSchool;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\MultiStudentCard;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\PrimarySchool;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\SecondarySchool;
use SPHERE\Application\Api\Document\Standard\Repository\StudentCard\StudentCardNew;
use SPHERE\Application\Api\Document\Standard\Repository\StudentTransfer;
use SPHERE\Application\Api\Document\Standard\Repository\StaffAccidentReport\StaffAccidentReport;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Document\Service\Entity\TblDocument;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\Document\Storage\FilePointer;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account as GatekeeperAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Application\Setting\Consumer\Responsibility\Responsibility;
use SPHERE\Application\Setting\Consumer\Responsibility\Service\Entity\TblResponsibility;
use SPHERE\Application\Setting\User\Account\Account;
use SPHERE\Common\Window\Display;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use MOC\V\Component\Document\Component\Bridge\Repository\DomPdf;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use SPHERE\System\Extension\Repository\PdfMerge;

/**
 * Class Creator
 *
 * @package SPHERE\Application\Api\Document\Standard
 */
class Creator extends Extension
{
    const PAPERORIENTATION_PORTRAIT = 'PORTRAIT';
    const PAPERORIENTATION_LANDSCAPE = 'LANDSCAPE';
    /**
     * @param null   $PersonId
     * @param string $DocumentClass
     * @param string $paperOrientation
     * @param array  $Data
     *
     * @return Stage|string
     */
    public static function createPdf($PersonId, $DocumentClass, $paperOrientation = Creator::PAPERORIENTATION_PORTRAIT, $Data = array())
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && class_exists($DocumentClass)
        ) {
            /** @var AbstractDocument $Document */
            if(!empty($Data)){
                $Document = new $DocumentClass($Data);
            } else {
                $Document = new $DocumentClass();
            }

            $Data['Person']['Id'] = $tblPerson->getId();
            if (strpos($DocumentClass, 'StudentCard') !== false ) {
                $Data = Generator::useService()->setStudentCardContent($Data, $tblPerson, $Document);
            }

            $File = self::buildDummyFile($Document, $Data, array(), $paperOrientation);

            $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        } elseif (class_exists($DocumentClass)) {
            // create PDF without Data and PersonId
            /** @var AbstractDocument $Document */
            if(!empty($Data)){
                $Document = new $DocumentClass($Data);
            } else {
                $Document = new $DocumentClass();
            }
            $File = self::buildDummyFile($Document, array(), array(), $paperOrientation);
            $FileName = $Document->getName().' '.date("Y-m-d").".pdf";

            return self::buildDownloadFile($File, $FileName);
        }
        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param $PersonId
     * @param $YearId
     * @param string $paperOrientation
     *
     * @return Stage|string
     */
    public static function createGradebookOverviewPdf($PersonId, $YearId, string $paperOrientation = Creator::PAPERORIENTATION_LANDSCAPE)
    {
        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblYear = Term::useService()->getYearById($YearId))
        ) {
            $Document = new GradebookOverview\GradebookOverview();
            $pageList[] = $Document->buildPage($tblPerson, $tblYear);

            $File = self::buildDummyFile($Document, array(), $pageList, $paperOrientation);

            $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param $DivisionCourseId
     * @param string $paperOrientation
     * @param $Redirect
     *
     * @return Display|Stage|string
     */
    public static function createMultiGradebookOverviewPdf($DivisionCourseId, $paperOrientation = Creator::PAPERORIENTATION_LANDSCAPE, $Redirect = true)
    {
        // Warteseite
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/MultiGradebookOverview/Create',
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'paperOrientation' => $paperOrientation,
                    'Redirect' => 0
                )
            );
        }

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
                $pageList = array();
                $documentName = '';
                foreach($tblPersonList as $tblPerson){
                    $Document = new GradebookOverview\GradebookOverview();
                    $documentName = $Document->getName();

                    $pageList[] = $Document->buildPage($tblPerson, $tblYear);
                }

                if(!empty($pageList)){
                    $template = new GradebookOverview\GradebookOverview();
                    $File = self::buildDummyFile($template, array(), $pageList, $paperOrientation);
                }

                $FileName = $documentName . $tblDivisionCourse->getDisplayName() . ' ' . date("Y-m-d") . ".pdf";

                if(isset($File)){
                    return self::buildDownloadFile($File, $FileName);
                }
            }
        }

        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param AbstractDocument|AbstractStudentCard $DocumentClass
     * @param array $Data
     * @param array $pageList
     * @param string $paperOrientation
     * @param bool $isDestruction
     * @param string $part
     *
     * @return FilePointer
     */
    private static function buildDummyFile($DocumentClass, $Data = array(), $pageList = array(),
        $paperOrientation = Creator::PAPERORIENTATION_PORTRAIT, $isDestruction = true, $part = '0')
    {

        ini_set('memory_limit', '2G');
        set_time_limit(300);

        // Create Tmp
        $File = Storage::createFilePointer('pdf', 'SPHERE-Temporary', $isDestruction);

        // build before const is set (picture)
        /** @var IBridgeInterface $Content */
        $Content = $DocumentClass->createDocument($Data, $pageList, $part);
        /** @var DomPdf $Document */
        $Document = PdfDocument::getPdfDocument($File->getFileLocation());
        $Document->setPaperOrientationParameter(new PaperOrientationParameter($paperOrientation));
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
    private static function buildDownloadFile(FilePointer $File, $FileName = '')
    {

        return FileSystem::getStream(
            $File->getRealPath(),
            $FileName ? $FileName : "Dokument " . date("Y-m-d") . ".pdf"
        )->__toString();
    }

    /**
     * @param int  $PersonId
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createStudentCardPdf($PersonId, $Redirect)
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/StudentCard/Create',
                array(
                    'PersonId' => $PersonId,
                    'Redirect' => 0
                )
            );
        }

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblSchoolTypeList = Generator::useService()->getSchoolTypeListForStudentCard($tblPerson))
        ) {
            $Data['Person']['Id'] = $tblPerson->getId();
            $pageList = array();
            foreach ($tblSchoolTypeList as $tblType) {
                if ($tblType->getName() == 'Grundschule') {
                    $DocumentItem = new PrimarySchool();
                } else {
                    if ($tblType->getName() == 'Gymnasium') {
                        $DocumentItem = new GrammarSchool();
                    } else {
                        if ($tblType->getName() == 'Mittelschule / Oberschule') {
                            $DocumentItem = new SecondarySchool();
                        } else {
                            $DocumentItem = false;
                        }
                    }
                }

                if ($DocumentItem) {
                    $DocumentItem->setTblPerson($tblPerson);
                    $Data = Generator::useService()->setStudentCardContent($Data, $tblPerson, $DocumentItem, $tblType);
                    $pageList[] = $DocumentItem->buildPage();
                    $pageList[] = $DocumentItem->buildRemarkPage($tblType);
                }
            }

            if (!empty($pageList)) {
                $Document = new MultiStudentCard();
                $File = self::buildDummyFile($Document, $Data, $pageList, self::PAPERORIENTATION_PORTRAIT);
                $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

                return self::buildDownloadFile($File, $FileName);
            }
        }

        return "Keine Schülerkartei vorhanden!";
    }

    /**
     * @param int  $PersonId
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createStudentCardNewPdf($PersonId, $Redirect)
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/StudentCardNew/Create',
                array(
                    'PersonId' => $PersonId,
                    'Redirect' => 0
                )
            );
        }

        if (($tblPerson = Person::useService()->getPersonById($PersonId)))
        {
            $Data['Person']['Id'] = $tblPerson->getId();
            $DocumentItem = new StudentCardNew();
            $DocumentItem->setTblPerson($tblPerson);
            $pageList = array();

            $pageList[] = $DocumentItem->buildPage();

//            if (!empty($pageList)) {
                $Document = new MultiStudentCard();
                $File = self::buildDummyFile($Document, $Data, $pageList, self::PAPERORIENTATION_PORTRAIT);
                $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

                return self::buildDownloadFile($File, $FileName);
//            }
        }

        return "Keine Schülerkartei vorhanden!";
    }

    /**
     * @param $DivisionCourseId
     * @param $List
     * @param $Redirect
     *
     * @return Display|string
     */
    public static function createMultiStudentCardPdf($DivisionCourseId, $List, $Redirect)
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/StudentCard/CreateMulti',
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'List' => $List,
                    'Redirect' => 0
                )
            );
        }

        if (($tblAccount = GatekeeperAccount::useService()->getAccountBySession())
            && ($tblAccountDownloadLock = Consumer::useService()->getAccountDownloadLock($tblAccount, 'StudentCard'))
            && $tblAccountDownloadLock->getIsFrontendLocked()
        ) {
            return 'Sie können immer nur eine Schülerkartei herunterladen. Bitte warten Sie bis das Erstellen der letzten Schülerkartei abgeschlossen ist';
        }

        if ($tblAccount){
            Consumer::useService()->createAccountDownloadLock($tblAccount, new DateTime(), 'StudentCard', true, false);
        }

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            // Fieldpointer auf dem der Merge durchgeführt wird, (download)
            $MergeFile = Storage::createFilePointer('pdf');
            $PdfMerger = new PdfMerge();

            if(($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())){
                $FileList = array();
                $count = 0;
                $maxPersonCount = 15;
                if ($List !== null) {
                    $isList = true;
                    $minCount = 1 + $maxPersonCount * ($List - 1);
                    $maxCount = 0 + $maxPersonCount * ($List);
                } else{
                    $isList = false;
                    $minCount = 0;
                    $maxCount = 0;
                }

                foreach ($tblPersonList as $tblPerson) {
                    $count++;
                    $Data = array();

                    // nur entsprechenden Personenteil berücksichtigen
                    if ($isList
                        && (($count < $minCount) || ($count > $maxCount))
                    ) {
                        continue;
                    }

                    set_time_limit(300);
                    $Data['Person']['Id'] = $tblPerson->getId();
                    $pageList = array();
                    if (($tblSchoolTypeList = Generator::useService()->getSchoolTypeListForStudentCard($tblPerson))) {
                        foreach ($tblSchoolTypeList as $tblType) {
                            if ($tblType->getName() == 'Grundschule') {
                                $DocumentItem = new PrimarySchool();
                            } else {
                                if ($tblType->getName() == 'Gymnasium') {
                                    $DocumentItem = new GrammarSchool();
                                } else {
                                    if ($tblType->getName() == 'Mittelschule / Oberschule') {
                                        $DocumentItem = new SecondarySchool();
                                    } else {
                                        $DocumentItem = false;
                                    }
                                }
                            }

                            if ($DocumentItem) {
                                $DocumentItem->setTblPerson($tblPerson);
                                $DocumentItem->setTblYear($tblYear);

                                $Data = Generator::useService()->setStudentCardContent($Data, $tblPerson, $DocumentItem, $tblType);

                                $pageList[] = $DocumentItem->buildPage();
                                $pageList[] = $DocumentItem->buildRemarkPage($tblType);
                            }
                        }

                        if (!empty($pageList)) {
                            $Document = new MultiStudentCard();

                            // Tmp welches nicht sofort gelöscht werden soll (braucht man noch zum mergen)
                            $File = self::buildDummyFile($Document, $Data, $pageList, self::PAPERORIENTATION_PORTRAIT, false);
                            // hinzufügen für das mergen
                            $PdfMerger->addPdf($File);
                            // speichern der Files zum nachträglichem bereinigen
                            $FileList[] = $File;
                        }
                    }
                }

                // mergen aller hinzugefügten PDF-Datein
                $PdfMerger->mergePdf($MergeFile);
                if(!empty($FileList)){
                    // aufräumen der Temp-Files
                    /** @var FilePointer $File */
                    foreach($FileList as $File){
                        $File->setDestruct();
                    }
                }

                Consumer::useService()->createAccountDownloadLock($tblAccount, new DateTime(), 'StudentCard', false, true);

                if (!empty($FileList)) {
                    $FileName = 'Schülerkarteien '  .  $tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getName()
                        . ($isList ? ' ' . $List . '.Teil' : '')
                        . ' ' . date("Y-m-d") . ".pdf";

                    return self::buildDownloadFile($MergeFile, $FileName);
                }
            }
        }

        return "Keine Schülerkarteien vorhanden!";
    }

    /**
     * @param null|int $DivisionCourseId
     * @param null|int $List
     * @param bool $Redirect
     *
     * @return Display|string
     */
    public static function createMultiStudentCardNewPdf($DivisionCourseId, $List, $Redirect)
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/StudentCardNew/CreateMulti',
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'List' => $List,
                    'Redirect' => 0
                )
            );
        }

        if (($tblAccount = GatekeeperAccount::useService()->getAccountBySession())
            && ($tblAccountDownloadLock = Consumer::useService()->getAccountDownloadLock($tblAccount, 'StudentCard'))
            && $tblAccountDownloadLock->getIsFrontendLocked()
        ) {
            return 'Sie können immer nur eine Schülerkartei herunterladen. Bitte warten Sie bis das Erstellen der letzten Schülerkartei abgeschlossen ist';
        }

        if ($tblAccount){
            Consumer::useService()->createAccountDownloadLock($tblAccount, new DateTime(), 'StudentCard', true, false);
        }

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            // Fieldpointer auf dem der Merge durchgeführt wird, (download)
            $MergeFile = Storage::createFilePointer('pdf');
            $PdfMerger = new PdfMerge();

            if(($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())){
                $FileList = array();
                $count = 0;
                $maxPersonCount = 15;
                if ($List !== null) {
                    $isList = true;
                    $minCount = 1 + $maxPersonCount * ($List - 1);
                    $maxCount = 0 + $maxPersonCount * ($List);
                } else{
                    $isList = false;
                    $minCount = 0;
                    $maxCount = 0;
                }

                foreach ($tblPersonList as $tblPerson) {
                    $count++;
                    $Data = array();

                    // nur entsprechenden Personenteil berücksichtigen
                    if ($isList
                        && (($count < $minCount) || ($count > $maxCount))
                    ) {
                        continue;
                    }

                    set_time_limit(300);
                    $Data['Person']['Id'] = $tblPerson->getId();
                    $DocumentItem = new StudentCardNew();
                    $DocumentItem->setTblPerson($tblPerson);
                    $DocumentItem->setTblYear($tblYear);
                    $pageList = array();
                    $pageList[] = $DocumentItem->buildPage();

//            if (!empty($pageList)) {
                    $Document = new MultiStudentCard();
                    $File = self::buildDummyFile($Document, $Data, $pageList, self::PAPERORIENTATION_PORTRAIT);
                    $FileName = $Document->getName() . ' ' . $tblPerson->getLastFirstName() . ' ' . date("Y-m-d") . ".pdf";

                    $pageList = array();
                    $pageList[] = $DocumentItem->buildPage();
                    $Document = new MultiStudentCard();

                    // Tmp welches nicht sofort gelöscht werden soll (braucht man noch zum mergen)
                    $File = self::buildDummyFile($Document, $Data, $pageList, self::PAPERORIENTATION_PORTRAIT, false);
                    // hinzufügen für das mergen
                    $PdfMerger->addPdf($File);
                    // speichern der Files zum nachträglichem bereinigen
                    $FileList[] = $File;
                }

                // mergen aller hinzugefügten PDF-Datein
                $PdfMerger->mergePdf($MergeFile);
                if(!empty($FileList)){
                    // aufräumen der Temp-Files
                    /** @var FilePointer $File */
                    foreach($FileList as $File){
                        $File->setDestruct();
                    }
                }

                Consumer::useService()->createAccountDownloadLock($tblAccount, new DateTime(), 'StudentCard', false, true);

                if (!empty($FileList)) {
                    $FileName = 'Schülerkarteien ' .  $tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getName()
                        . ($isList ? ' ' . $List . '.Teil' : '')
                        . ' ' . date("Y-m-d") . ".pdf";

                    return self::buildDownloadFile($MergeFile, $FileName);
                }
            }
        }

        return "Keine Schülerkarteien vorhanden!";
    }

    /**
     * @param string $Type
     * @param string $Part
     * @param bool $Redirect
     *
     * @return Display|Stage|string
     */
    public static function createKamenzPdf($Type = '', $Part = '0', $Redirect = true)
    {

        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/KamenzReport/Create',
                array(
                    'Type' => $Type,
                    'Part' => $Part,
                    'Redirect' => 0
                )
            );
        }

        $Data = array();
        $Document = false;
        $paperOrientation = self::PAPERORIENTATION_PORTRAIT;
        if ($Type == 'Grundschule') {
            $Document = new Standard\Repository\KamenzReportGS();
            $Data = Generator::useService()->setKamenzReportGsContent($Data);
        } elseif ($Type == 'Oberschule') {
            $Document = new Standard\Repository\KamenzReport();
            $Data = Generator::useService()->setKamenzReportOsContent($Data);
        } elseif ($Type == 'Gymnasium') {
            $Document = new Standard\Repository\KamenzReportGym();
            $Data = Generator::useService()->setKamenzReportGymContent($Data);
        } elseif ($Type == 'Berufsfachschule') {
            $Document = new Standard\Repository\KamenzReportBFS();
            $paperOrientation = self::PAPERORIENTATION_LANDSCAPE;
            $Data = Generator::useService()->setKamenzReportBFSContent($Data);
        } elseif ($Type == 'Fachschule') {
            $Document = new Standard\Repository\KamenzReportFS();
            $paperOrientation = self::PAPERORIENTATION_LANDSCAPE;
            $Data = Generator::useService()->setKamenzReportFSContent($Data);
        }

        if ($Document) {
            $File = self::buildDummyFile($Document, $Data, array(), $paperOrientation, true, $Part);

            $FileName = $Document->getName() . ($Part != '0' ? ' Teil ' . $Part : '') . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param array  $Data
     * @param string $DocumentName
     * @param string $paperOrientation
     *
     * @return Stage|string
     */
    public static function createDataPdf($Data, $DocumentName, $paperOrientation = Creator::PAPERORIENTATION_PORTRAIT)
    {
        if (!empty($Data)
        ) {

            $Document = false;
            if ($DocumentName == 'EnrollmentDocument') {
                $Document = new EnrollmentDocument($Data);
            }
            if ($DocumentName == 'StudentTransfer') {
                $Document = new StudentTransfer\StudentTransfer($Data);
            }
            if ($DocumentName == 'SignOutCertificate') {
                $Document = new SignOutCertificate($Data);
            }
            if ($DocumentName == 'AccidentReport') {
                $Document = new AccidentReport($Data);
            }
            if ($DocumentName == 'StaffAccidentReport'){
                $Document = new StaffAccidentReport($Data);
            }
            if ($Document) {
                $File = self::buildDummyFile($Document, array(), array(), $paperOrientation);

                $FileName = $Document->getName().'_'.date("Y-m-d").".pdf";

                return self::buildDownloadFile($File, $FileName);
            }
        }

        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param null $DivisionCourseId
     * @param null $SubjectId
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createGradebookPdf($DivisionCourseId = null, $SubjectId = null, $Redirect = true)
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/Gradebook/Create',
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'SubjectId' => $SubjectId,
                    'Redirect' => 0
                )
            );
        }

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblSubject = Subject::useService()->getSubjectById($SubjectId))
        ) {
            $template = new Gradebook();
            $content = $template->createSingleDocument($tblDivisionCourse, $tblSubject);

            ini_set('memory_limit', '1G');

            // Create Tmp
            $File = Storage::createFilePointer('pdf');

            // build before const is set (picture)
            /** @var DomPdf $Document */
            $Document = PdfDocument::getPdfDocument($File->getFileLocation());
            $Document->setContent($content);
            $Document->saveFile(new FileParameter($File->getFileLocation()));

            $FileName = 'Notenbuch ' . $tblDivisionCourse->getName() . ' ' . $tblSubject->getAcronym() . ' ' . date("Y-m-d").".pdf";

            return FileSystem::getStream(
                $File->getRealPath(),
                $FileName
            )->__toString();
        }

        return new Stage('Notenbuch', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param $DivisionCourseId
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createMultiGradebookPdf($DivisionCourseId = null, $Redirect = true)
    {

        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/MultiGradebook/Create',
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'Redirect' => 0
                )
            );
        }

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $template = new Gradebook();

            ini_set('memory_limit', '2G');
//            $PdfMerger = new PdfMerge();
//            $FileList = array();
            $allPages = array();

            if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse))) {
                $tblSubjectList = (new Extension())->getSorter($tblSubjectList)->sortObjectBy('Name');
                /** @var TblSubject $tblSubject */
                foreach ($tblSubjectList as $tblSubject) {
                    $pageList = $template->buildPageList($tblDivisionCourse, $tblSubject);
                    $allPages = array_merge($allPages, $pageList);
//                    // Create Tmp
//                    $File = Storage::createFilePointer('pdf', 'SPHERE-Temporary-short', false);
//                    $clone[] = clone $File;
//                    // build before const is set (picture)
//                    /** @var DomPdf $Document */
//                    $Document = PdfDocument::getPdfDocument($File->getFileLocation());
//                    $Document->setContent($Content);
//                    $Document->saveFile(new FileParameter($File->getFileLocation()));
//                    // hinzufügen für das mergen
//                    $PdfMerger->addPDF($File);
//                    // speichern der Files zum nachträglichem bereinigen
//                    $FileList[] = $File;
                }
            }
            $File = self::buildDummyFile($template, array(), $allPages);
            $FileName = 'Notenbücher ' . $tblDivisionCourse->getName()  . ' ' . date("Y-m-d").".pdf";
            return self::buildDownloadFile($File, $FileName);
//            $MergeFile = Storage::createFilePointer('pdf');
//            // mergen aller hinzugefügten PDF-Datein
//            $PdfMerger->mergePdf($MergeFile);

//            if(!empty($FileList)){
//                // aufräumen der Temp-Files
//                /** @var FilePointer $File */
//                foreach($FileList as $File){
//                    $File->setDestruct();
//                }
//            }

//            return FileSystem::getStream(
//                $MergeFile->getRealPath(),
//                $FileName
//            )->__toString();
        }

        return new Stage('Notenbuch', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param array  $Data
     * @param string $paperOrientation
     *
     * @return Stage|string
     */
    public static function createChangePasswordPdf($Data, $paperOrientation = Creator::PAPERORIENTATION_PORTRAIT)
    {
        if (!empty($Data)
        ) {

            if(isset($Data['UserAccountId']) && ($tblUserAccount = Account::useService()->getUserAccountById($Data['UserAccountId']))){
                if($tblUserAccount->getType() == 'CUSTODY'){
                    $IdentifierString = 'Sorgeberechtigte';
                } else {
                    $IdentifierString = 'Schüler';
                }
            } else {
                $IdentifierString = 'KEIN_TYP';
            }

            $Document = new PasswordChange($Data);

            $File = self::buildDummyFile($Document, array(), array(), $paperOrientation);

            $Time = new DateTime();
            $Time = $Time->format('d_m_Y-h_i_s');
            $FileName = $Document->getName().'-'.$IdentifierString.'-'.$Time.".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return new Stage('Dokument', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param $Data
     * @param string $paperOrientation
     * @return Stage|string
     * @throws \MOC\V\Core\FileSystem\Exception\FileSystemException
     */
    public static function createMultiPasswordPdf($Data, $paperOrientation = Creator::PAPERORIENTATION_PORTRAIT)
    {

        $multiPassword = new MultiPassword($Data);
        $pageList = $multiPassword->getPageList();

        if (!empty($pageList)) {
            ini_set('memory_limit', '2G');
//            $PdfMerger = new PdfMerge();
//            $FileList = array();

            $File = self::buildDummyFile($multiPassword, array(), $pageList, $paperOrientation);

//            foreach ($pageList as $page) {
//                // Create Tmp
//                $File = Storage::createFilePointer('pdf', 'SPHERE-Temporary-short', false);
//                $clone[] = clone $File;
//                // build before const is set (picture)
//                /** @var DomPdf $Document */
//                $Document = PdfDocument::getPdfDocument($File->getFileLocation());
//                $Document->setPaperOrientationParameter(new PaperOrientationParameter($paperOrientation));
//                $pdfDocument = new \SPHERE\Application\Document\Generator\Repository\Document();
//                $pdfDocument->addPage($page);
//                $pdfFrame = new Frame();
//                $pdfFrame->addDocument($pdfDocument);
//                $Document->setContent($pdfFrame->getTemplate());
//                $Document->saveFile(new FileParameter($File->getFileLocation()));
//                // hinzufügen für das mergen
//                $PdfMerger->addPDF($File);
//                // speichern der Files zum nachträglichem bereinigen
//                $FileList[] = $File;
//            }
//
//
//            $MergeFile = Storage::createFilePointer('pdf');
//
//            // mergen aller hinzugefügten PDF-Datein
//            $PdfMerger->mergePdf($MergeFile);
//
//
//
//            if(!empty($FileList)){
//                // aufräumen der Temp-Files
//                /** @var FilePointer $File */
//                foreach($FileList as $File){
//                    $File->setDestruct();
//                }
//            }

            $FileName = $multiPassword->getName().".pdf";
            return self::buildDownloadFile($File, $FileName);
//            return FileSystem::getStream(
//                $MergeFile->getRealPath(),
//                $FileName
//            )->__toString();
        }

        return new Stage('Account Export', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param array $Data
     * @param bool $Redirect
     *
     * @return Display|Stage|string
     */
    public static function createBillingDocumentPdf($Data = array(), $Redirect = true)
    {

        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/BillingDocument/Create',
                array(
                    'Data'   => $Data,
                    'Redirect' => 0
                )
            );
        }

        if(($tblItem = Item::useService()->getItemById($Data['Item']))
            && ($tblDocument = \SPHERE\Application\Billing\Inventory\Document\Document::useService()->getDocumentById($Data['Document']))
        ) {
            if (isset($Data['PersonId']) && ($tblPerson = Person::useService()->getPersonById($Data['PersonId']))) {
                $BasketTypeId = $Data['BasketType'];
                $PriceList = Balance::useService()->getPriceListByItemAndPerson(
                    $tblItem,
                    $Data['Year'],
                    $Data['From'],
                    $Data['To'],
                    $tblPerson,
                    $BasketTypeId
                );
                // Summe berechnen
                $PriceList = Balance::useService()->getSummaryByItemPrice($PriceList);
            } else {
                $BasketTypeId = $Data['BasketType'];
                $PriceList = Balance::useService()->getPriceListByItemAndYear(
                    $tblItem,
                    $Data['Year'],
                    $BasketTypeId,
                    $Data['From'],
                    $Data['To'],
                    isset($Data['DivisionCourse']) ? $Data['DivisionCourse'] : '0',
                    isset($Data['Group']) ? $Data['Group'] : '0'
                );
            }

            if (!empty($PriceList)) {
                $Data['CompanyAddress'] = $Data['CompanyStreet'] . '<br/>' . $Data['CompanyCity']
                    . ($Data['CompanyDistrict'] ? '  OT ' . $Data['CompanyDistrict'] : '');
                $template = new Billing($tblItem, $Data);
                $pageList = array();

                ini_set('memory_limit', '2G');
//                $PdfMerger = new PdfMerge();
//                $FileList = array();
                $countPdfs = 0;
                if (isset($Data['List'])) {
                    $list = $Data['List'] - 1;
                    $isList = true;
                } else {
                    $list = 0;
                    $isList = false;
                }
                foreach($PriceList as $DebtorId => $CauserList) {
                    if (($tblPersonDebtor = Person::useService()->getPersonById($DebtorId))) {
                        foreach ($CauserList as $CauserId => $ItemList) {
                            foreach ($ItemList as $ItemId => $Value) {
                                if (($tblPersonCauser = Person::useService()->getPersonById($CauserId))){
                                    $countPdfs++;
                                    // nur die Pdfs der ausgewählten Liste herunterladen
                                    if ($isList){
                                        $maxPdfPages = Balance::useFrontend()->getMaxPdfPages();
                                        if ($countPdfs > $maxPdfPages * $list && $countPdfs <= $maxPdfPages * ($list + 1)){
                                            // werden hinzugefügt
                                        } else {
                                            continue;
                                        }
                                    }

                                    if (isset($Value['Sum'])){
                                        $TotalPrice = number_format($Value['Sum'], 2, ',', '.').' €';
                                    } else {
                                        $TotalPrice = '0,00 €';
                                    }
                                    $InvoiceNumber = '';
                                    if(isset($Value['InvoiceNumber'])){
                                        $InvoiceNumber = $Value['InvoiceNumber'];
                                    }

                                    $pageList[] = $template->buildPage($tblPersonDebtor, $tblPersonCauser, $TotalPrice, $InvoiceNumber);
                                }
                            }
                        }
                    }
                }

                $File = self::buildDummyFile($template, array(), $pageList);
                $FileName = 'Bescheinigung_' . $tblItem->getName() . ($isList ? '_Liste_' . ($list + 1) : '') . '_' . date("Y-m-d") . ".pdf";

                return FileSystem::getStream(
                    $File->getRealPath(),
                    $FileName
                )->__toString();
            }
        }

        return new Stage('Bescheinigung', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param array $Data
     * @param bool  $Redirect
     *
     * @return Display|Stage|string
     */
    public static function createBillingDocumentWarningPdf($Data = array(), $Redirect = true)
    {

        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/BillingDocumentWarning/Create',
                array(
                    'Data' => $Data,
                    'Redirect'  => 0
                )
            );
        }

        if(isset($Data['InvoiceItemDebtorId'])
            && ($tblInvoiceItemDebtor = Invoice::useService()->getInvoiceItemDebtorById($Data['InvoiceItemDebtorId']))
            && ($tblItem = $tblInvoiceItemDebtor->getServiceTblItem())
            && ($tblDocument = \SPHERE\Application\Billing\Inventory\Document\Document::useService()->getDocumentByName(TblDocument::IDENT_MAHNBELEG, true))
        ){

            $Data['CompanyName'] = '';
            $Data['CompanyExtendedName'] = '';
            $Data['CompanyDistrict'] = '';
            $Data['CompanyStreet'] = '';
            $Data['CompanyCity'] = '';
            $Data['Location'] = '';

            if (($tblResponsibilityAll = Responsibility::useService()->getResponsibilityAll())) {
                /** @var TblResponsibility $tblResponsibility */
                $tblResponsibility = reset($tblResponsibilityAll);
                if (($tblCompany = $tblResponsibility->getServiceTblCompany())) {
                    $Data['CompanyName'] = $tblCompany->getName();
                    $Data['CompanyExtendedName'] = $tblCompany->getExtendedName();
                    if (($tblAddress = $tblCompany->fetchMainAddress())
                        && ($tblCity = $tblAddress->getTblCity())
                    ) {
                        $Data['CompanyDistrict'] = $tblCity->getDistrict();
                        $Data['CompanyStreet'] = $tblAddress->getStreetName() . ' ' . $tblAddress->getStreetNumber();
                        $Data['CompanyCity'] = $tblCity->getCode() . ' ' . $tblCity->getName();
                        $Data['Location'] = $tblCity->getName();
                    }
                }
            }
            $tblPersonDebtor = $tblInvoiceItemDebtor->getServiceTblPersonDebtor();
            $tblInvoice = $tblInvoiceItemDebtor->getTblInvoice();
            $tblPersonCauser = $tblInvoice->getServiceTblPersonCauser();

            $InvoiceNumber = $tblInvoice->getInvoiceNumber();
            $Data['InvoiceNumber'] = $InvoiceNumber;
            $Data['BillTime'] = $tblInvoice->getBillTime('Y/m');
            $Data['BillName'] = $tblInvoice->getBasketName();
            $Data['Count'] = $tblInvoiceItemDebtor->getQuantity();
            $Data['Price'] = $tblInvoiceItemDebtor->getPriceString();
            $Data['SummaryPrice'] = $tblInvoiceItemDebtor->getSummaryPrice();
            $Data['TargetTime'] = $tblInvoice->getTargetTime();

            $Data['CompanyAddress'] = $Data['CompanyStreet'] . '<br/>' . $Data['CompanyCity']
                . ($Data['CompanyDistrict'] ? '  OT ' . $Data['CompanyDistrict'] : '');

            // Text aus Vorlage füllen
            $tblDocumentInformationList = \SPHERE\Application\Billing\Inventory\Document\Document::useService()->getDocumentInformationAllByDocument($tblDocument);
            foreach($tblDocumentInformationList as $tblDocumentInformation){
                $Data[$tblDocumentInformation->getField()] = $tblDocumentInformation->getValue();
            }

            $template = new DocumentWarning($tblItem, $Data);

            ini_set('memory_limit', '1G');
            $Content = $template->createSingleDocument($tblPersonDebtor, $tblPersonCauser);

            // Create Tmp
            $File = Storage::createFilePointer('pdf');

            // build before const is set (picture)
            /** @var DomPdf $Document */
            $Document = PdfDocument::getPdfDocument($File->getFileLocation());
            $Document->setContent($Content);
            $Document->saveFile(new FileParameter($File->getFileLocation()));

            $FileName = 'Mahnung_' .$InvoiceNumber. date("Y-m-d").".pdf";

            return FileSystem::getStream($File->getRealPath(), $FileName)->__toString();
        }

        return new Stage('Mahnung', 'Konnte nicht erstellt werden.');
    }

    /**
     * @param int  $AccountId
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createAccountPdf($AccountId = null, $Redirect = true)
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/Account/Create',
                array(
                    'AccountId' => $AccountId,
                    'Redirect' => 0
                )
            );
        }

        if (($tblAccount = GatekeeperAccount::useService()->getAccountById($AccountId))
            && ($tblIdentification = $tblAccount->getServiceTblIdentification())
        ) {
            if (($tblPersonAllByAccount = GatekeeperAccount::useService()->getPersonAllByAccount($tblAccount))) {
                $tblPerson = $tblPersonAllByAccount[0];
            } else {
                return "Das Benutzerkonto ist keiner Person zugeordnet.";
            }

            if ($tblIdentification->getName() == TblIdentification::NAME_AUTHENTICATOR_APP) {
                $Document = new AccountApp($tblAccount, $tblPerson);
            } else {
                $Document = new AccountToken($tblAccount, $tblPerson);
            }


            $File = self::buildDummyFile($Document, array(), array());

            $FileName = $Document->getName() . ' - ' . $tblPerson->getLastFirstName() . ' - ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return "Kein Benutzerkonto vorhanden!";
    }

    /**
     * @param string $Select Help|UserRole|Billing|Indiware|PrintA3Certificate
     */
    public static function createManualPdf(string $Select = '')
    {

        if($Select == 'Help'){
            $file = "Common/Style/Resource/Document/Manual/SSWHelp.pdf";
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=Hilfe_Schulsoftware_18.04.2023.pdf");
            header("Content-Length: ".filesize($file));
        } elseif($Select == 'UserRole') {
            $file = "Common/Style/Resource/Document/Manual/SSWUserRole.pdf";
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=Benutzerrechte_Schulsoftware.pdf");
            header("Content-Length: ".filesize($file));
        } elseif($Select == 'DigitalClassBook') {
            $file = "Common/Style/Resource/Document/Manual/SSWDigitalClassBook.pdf";
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=Digitales_Klassenbuch_09.11.2022.pdf");
            header("Content-Length: ".filesize($file));
        } elseif($Select == 'Billing') {
            $file = "Common/Style/Resource/Document/Manual/AnleitungFakturierung.pdf";
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=Anleitung_Fakturierung_21.02.2022.pdf");
            header("Content-Length: ".filesize($file));
        } elseif($Select == 'PrintA3Certificate') {
            $file = "Common/Style/Resource/Document/Manual/SSWPrintA3Certificate.pdf";
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=Zeugnisdruck_A3.pdf");
            header("Content-Length: ".filesize($file));
        } elseif($Select == 'ESDi') {
            $file = "Common/Style/Resource/Document/Manual/SSW_ESDiLeistungsbeschreibung.pdf";
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=ESDi_Leistungsbeschreibung_16.01.2023.pdf");
            header("Content-Length: ".filesize($file));
        } elseif($Select == 'Indiware') {
            $file = "Common/Style/Resource/Document/Manual/Indiware_Leitfaden.pdf";
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=Indiware_Leitfaden.pdf");
            header("Content-Length: ".filesize($file));
        } elseif($Select == 'Untis') {
            $file = "Common/Style/Resource/Document/Manual/Untis_Leitfaden.pdf";
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=Untis_Leitfaden.pdf");
            header("Content-Length: ".filesize($file));
        } elseif($Select == 'SSW_UCS_DLLP') {
            $file = "Common/Style/Resource/Document/Manual/SSW_UCS_DLLP.pdf";
            header("Content-Type: application/pdf");
            header("Content-Disposition: attachment; filename=Kurzleidfaden_UCS_DLLP_11.05.2023.pdf");
            header("Content-Length: ".filesize($file));
        }

        readfile($file);
    }

    /**
     * @param string $DivisionCourseId
     * @param bool $Redirect
     *
     * @return string
     */
    public static function createMultiEnrollmentDocumentPdf(string $DivisionCourseId, bool $Redirect): string
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/EnrollmentDocument/CreateMulti',
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'Redirect' => 0
                )
            );
        }

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
        ) {
            // Filepointer auf dem der Merge durchgeführt wird, (download)
            $MergeFile = Storage::createFilePointer('pdf');
            $PdfMerger = new PdfMerge();

            if(($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())){
                $FileList = array();
                foreach ($tblPersonList as $tblPerson) {
                    set_time_limit(300);

                    $Document = new EnrollmentDocument(\SPHERE\Application\Document\Standard\EnrollmentDocument\EnrollmentDocument::useService()
                        ->getEnrollmentDocumentData($tblPerson, $tblYear));
                    $File = self::buildDummyFile($Document, array(), array());

                    // hinzufügen für das mergen
                    $PdfMerger->addPdf($File);
                    // speichern der Files zum nachträglichem bereinigen
                    $FileList[] = $File;
                }

                // mergen aller hinzugefügten PDF-Datein
                $PdfMerger->mergePdf($MergeFile);
                if(!empty($FileList)){
                    // aufräumen der Temp-Files
                    /** @var FilePointer $File */
                    foreach($FileList as $File){
                        $File->setDestruct();
                    }
                }

                if (!empty($FileList)) {
                    $FileName = 'Schulbescheinigungen Kurs ' . $tblDivisionCourse->getName() . ' ' . date("Y-m-d") . ".pdf";

                    return self::buildDownloadFile($MergeFile, $FileName);
                }
            }
        }

        return "Keine Schulbescheinigungen vorhanden!";
    }

    /**
     * @param $DivisionCourseId
     * @param $Redirect
     *
     * @return string
     */
    public static function createClassRegisterPdf($DivisionCourseId, $Redirect): string
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/ClassRegister/Create',
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'Redirect' => 0
                )
            );
        }

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $Document = new ClassRegister($tblDivisionCourse);
            $pageList[] = $Document->getPageList();

            $File = self::buildDummyFile($Document, array(), $pageList);

            $FileName = $Document->getName() . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return "Kein Klassentagebuch vorhanden!";
    }

    /**
     * @param $DivisionCourseId
     * @param $Redirect
     *
     * @return string
     */
    public static function createCourseContentPdf($DivisionCourseId, $Redirect): string
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/CourseContent/Create',
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'Redirect' => 0
                )
            );
        }

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $Document = new CourseContent($tblDivisionCourse);
            $pageList[] = $Document->getPageList();

            $File = self::buildDummyFile($Document, array(), $pageList);

            $FileName = $Document->getName() . ' ' . date("Y-m-d") . ".pdf";

            return self::buildDownloadFile($File, $FileName);
        }

        return "Kein Kursheft vorhanden!";
    }

    /**
     * @param string $DivisionCourseId
     * @param bool $Redirect
     *
     * @return string
     */
    public static function createMultiDataComparisonPdf(string $DivisionCourseId, bool $Redirect = true): string
    {
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Custom/Gersdorf/MetaDataComparison/Division/CreateMulti',
                array(
                    'DivisionCourseId' => $DivisionCourseId,
                    'Redirect' => 0
                )
            );
        }

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            // Filepointer auf dem der Merge durchgeführt wird, (download)
            $MergeFile = Storage::createFilePointer('pdf');
            $PdfMerger = new PdfMerge();

            if(($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())){
                $FileList = array();
                foreach ($tblPersonList as $tblPerson) {
                    set_time_limit(300);
                    $Data['Person']['Id'] = $tblPerson->getId();
                    $Document = new MetaDataComparison($Data);
                    $File = self::buildDummyFile($Document, array(), array());

                    // hinzufügen für das mergen
                    $PdfMerger->addPdf($File);
                    // speichern der Files zum nachträglichem bereinigen
                    $FileList[] = $File;
                }

                // mergen aller hinzugefügten PDF-Datein
                $PdfMerger->mergePdf($MergeFile);
                if(!empty($FileList)){
                    // aufräumen der Temp-Files
                    /** @var FilePointer $File */
                    foreach($FileList as $File){
                        $File->setDestruct();
                    }
                }

                if (!empty($FileList)) {
                    $FileName = 'Stammdaten der Klasse ' . $tblDivisionCourse->getDisplayName() . ' ' . date("Y-m-d") . ".pdf";
                    return self::buildDownloadFile($MergeFile, $FileName);
                }
            }
        }

        return "Keine Stammdaten vorhanden!";
    }
}