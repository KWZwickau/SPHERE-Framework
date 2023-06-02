<?php
namespace SPHERE\Application\Api\Reporting\Standard\Person;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Absence\Absence;
use SPHERE\Application\Education\Certificate\Reporting\Reporting;
use SPHERE\Application\Education\Certificate\Reporting\View;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;
use SPHERE\System\Extension\Extension;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Standard\Person
 */
class Person extends Extension
{
    /**
     * @param TblDivisionCourse     $tblDivisionCourse
     * @param TblDivisionCourseType $tblDivisionCourseType
     *
     * @return string
     */
    private function getDivisionCourseTypeNameList(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseType $tblDivisionCourseType)
    {

        switch ($tblDivisionCourseType->getIdentifier()) {
            case TblDivisionCourseType::TYPE_DIVISION:          // Klasse
            case TblDivisionCourseType::TYPE_CORE_GROUP:        // Stammgruppe
            case TblDivisionCourseType::TYPE_TEACHING_GROUP:    // Unterrichtsgruppe
            case TblDivisionCourseType::TYPE_TEACHER_GROUP:     // Lerngruppe
                $name = $tblDivisionCourseType->getName() . 'nliste_';
                break;
            case TblDivisionCourseType::TYPE_BASIC_COURSE:      // SekII-Grundkurs
            case TblDivisionCourseType::TYPE_ADVANCED_COURSE:   // SekII-Leistungskurs
                $name = $tblDivisionCourseType->getName() . ' Liste_';
                break;
            default:
                $name = 'Gruppenliste_';
        }
        $name .= $tblDivisionCourse->getDisplayName();
        return $name;
    }

    /**
     * @param int $DivisionCourseId
     *
     * @return false|string
     */
    public function downloadClassList(int  $DivisionCourseId)
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $tblType = $tblDivisionCourse->getType();
            $name = $this->getDivisionCourseTypeNameList($tblDivisionCourse, $tblType);
        } else {return false;}
        if (($tblPersonList = $tblDivisionCourse->getStudents())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($dataList = ReportingPerson::useService()->createClassList($tblPersonList, $tblYear))
        ) {
            $fileLocation = ReportingPerson::useService()->createClassListExcel($dataList, $tblPersonList, $tblDivisionCourse);
            return FileSystem::getDownload($fileLocation->getRealPath(), $name . '_' . date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param int $DivisionCourseId
     *
     * @return false|string
     */
    public function downloadExtendedClassList(int $DivisionCourseId)
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $tblType = $tblDivisionCourse->getType();
            $name = $this->getDivisionCourseTypeNameList($tblDivisionCourse, $tblType);
        } else {return false;}
        if (($dataList = ReportingPerson::useService()->createExtendedClassList($tblDivisionCourse))) {
            $fileLocation = ReportingPerson::useService()->createExtendedClassListExcel($dataList, $tblDivisionCourse);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Erweiterte_".$name.'_'.date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param int $DivisionCourseId
     *
     * @return false|string
     */
    public function downloadElectiveClassList(int $DivisionCourseId)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $tblType = $tblDivisionCourse->getType();
            $name = $this->getDivisionCourseTypeNameList($tblDivisionCourse, $tblType);
        } else {return false;}
        if (($dataList = ReportingPerson::useService()->createElectiveClassList($tblDivisionCourse))) {
            $fileLocation = ReportingPerson::useService()->createElectiveClassListExcel($dataList, $tblDivisionCourse);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Wahlfächer_".$name.'_'.date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param int $DivisionCourseId
     *
     * @return false|string
     */
    public function downloadBirthdayClassList(int $DivisionCourseId)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $tblType = $tblDivisionCourse->getType();
            $name = $this->getDivisionCourseTypeNameList($tblDivisionCourse, $tblType);
        } else {return false;}
        if(($tblPersonList = $tblDivisionCourse->getStudents())
        && ($dataList = ReportingPerson::useService()->createBirthdayClassList($tblDivisionCourse))){
            $fileLocation = ReportingPerson::useService()->createBirthdayClassListExcel($dataList, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Geburtstag_".$name.'_'.date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param int|null $DivisionCourseId
     *
     * @return false|string
     */
    public function downloadMedicalInsuranceClassList(?int $DivisionCourseId = null)
    {

        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            $tblType = $tblDivisionCourse->getType();
            $name = $this->getDivisionCourseTypeNameList($tblDivisionCourse, $tblType);
        } else {return false;}
        if(($tblPersonList = $tblDivisionCourse->getStudents())
            && ($dataList = ReportingPerson::useService()->createMedicalInsuranceClassList($tblDivisionCourse))){
            $fileLocation = ReportingPerson::useService()->createMedicalInsuranceClassListExcel($dataList, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Krankenkasse_".$name.'_'.date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @return false|string
     */
    public function downloadInterestedPersonList()
    {

        $hasGuardian = false;
        $hasAuthorizedPerson = false;
        if (!empty($dataList = ReportingPerson::useService()->createInterestedPersonList($hasGuardian, $hasAuthorizedPerson))) {
            // multisort
            foreach ($dataList as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $dataList);
            $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT));
            $fileLocation = ReportingPerson::useService()->createInterestedPersonListExcel($dataList, $tblPersonList, $hasGuardian, $hasAuthorizedPerson);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Interessentenliste ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param int $GroupId
     *
     * @return bool|string
     */
    public function downloadGroupList(int $GroupId)
    {

        if (($tblGroup = Group::useService()->getGroupById($GroupId))
        && ($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))
        && !empty($TableContent = ReportingPerson::useService()->createGroupList($tblGroup))) {
            $fileLocation = ReportingPerson::useService()->createGroupListExcel($TableContent, $tblPersonList, $tblGroup);
            return FileSystem::getDownload($fileLocation->getRealPath(), "Gruppenliste ".$tblGroup->getName() ." ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param array $Data
     *
     * @return string
     */
    public function downloadMetaDataComparison(array $Data = array())
    {

        $fileLocation = ReportingPerson::useService()->createMetaDataComparisonExcel($Data);
        return FileSystem::getDownload($fileLocation->getRealPath(),"Stammdatenabfrage ".date("Y-m-d").".xlsx")->__toString();
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return false|string
     */
    public function downloadMedicalRecordClassList($DivisionCourseId = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return 'Der Kurs wurde nicht gefunden';
        }

        switch ($tblDivisionCourse->getTypeIdentifier()) {
            case TblDivisionCourseType::TYPE_DIVISION: $preName = 'Krankenakte_Klassenliste '; break;
            case TblDivisionCourseType::TYPE_CORE_GROUP: $preName = 'Krankenakte_Stammgruppenliste '; break;
            default: $preName = 'Krankenakte_Kursliste ';
        }

        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
            && ($dataList = ReportingPerson::useService()->createMedicalRecordClassList($tblPersonList))
        ) {
            $fileLocation = ReportingPerson::useService()->createMedicalRecordClassListExcel($dataList, $tblPersonList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                $preName . $tblDivisionCourse->getName() . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return false|string
     */
    public function downloadAgreementClassList($DivisionCourseId = null)
    {
        if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return 'Der Kurs wurde nicht gefunden';
        }

        switch ($tblDivisionCourse->getTypeIdentifier()) {
            case TblDivisionCourseType::TYPE_DIVISION: $preName = 'Einverständniserklärung_Klassenliste '; break;
            case TblDivisionCourseType::TYPE_CORE_GROUP: $preName = 'Einverständniserklärung_Stammgruppenliste '; break;
            default: $preName = 'Einverständniserklärung_Kursliste ';
        }

        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
            && ($dataList = ReportingPerson::useService()->createAgreementClassList($tblPersonList))
        ) {
            $fileLocation = ReportingPerson::useService()->createAgreementClassListExcel($dataList, $tblPersonList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                $preName . $tblDivisionCourse->getName() . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param array $Data
     *
     * @return false|string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     * @throws \MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Core\FileSystem\Exception\FileSystemException
     */
    public function downloadAgreementStudentList($Data = array())
    {

        if(!empty(($tblPersonList = ReportingPerson::useService()->getStudentFilterResult($Data)))){
            if(!empty(($dataList = ReportingPerson::useService()->createAgreementList($tblPersonList)))){
                $fileLocation = ReportingPerson::useService()->createAgreementClassListExcel($dataList, $tblPersonList);
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    'Einverständniserklärung_Schüler ' . date("Y-m-d").".xlsx")->__toString();
            }
        }
        return false;
    }

    /**
     * @return string
     * @throws \MOC\V\Component\Document\Exception\DocumentTypeException
     * @throws \MOC\V\Core\FileSystem\Component\Exception\Repository\TypeFileException
     * @throws \MOC\V\Core\FileSystem\Exception\FileSystemException
     */
    public function downloadAgreementPersonList()
    {

        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        if($tblPersonList && ($dataList = ReportingPerson::useService()->createPersonAgreementList($tblPersonList))){
            $fileLocation = ReportingPerson::useService()->createAgreementPersonListExcel($dataList, $tblPersonList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                'Einverständniserklärung_Mitarbeiter ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param null $Date
     * @param null $DateTo
     * @param null $Type
     * @param string $DivisionName
     * @param int $IsCertificateRelevant
     * @param bool $IsAbsenceOnlineOnly
     *
     * @return bool|string
     */
    public function downloadAbsenceList($Date = null, $DateTo = null, $Type = null, string $DivisionName = '',
        int $IsCertificateRelevant = 0, bool $IsAbsenceOnlineOnly = false)
    {
        // das Datum darf keine Uhrzeit enthalten
        $dateTimeFrom = new DateTime((new DateTime($Date))->format('d.m.Y'));
        if ($DateTo == null || $DateTo == '') {
            $dateTimeTo = null;
        } else {
            $dateTimeTo = new DateTime((new DateTime($DateTo))->format('d.m.Y'));
        }

        if (($fileLocation = ReportingPerson::useService()->createAbsenceListExcel($dateTimeFrom, $dateTimeTo, $Type, $DivisionName, $IsCertificateRelevant, $IsAbsenceOnlineOnly))) {
            return FileSystem::getDownload($fileLocation->getRealPath(), "Fehlzeiten " . $dateTimeFrom->format("Y-m-d") . ".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param null $StartDate
     * @param null $EndDate
     *
     * @return bool|string
     */
    public function downloadAbsenceBetweenList($StartDate = null, $EndDate = null)
    {
        if ($StartDate && $EndDate) {
            $StartDate = new DateTime($StartDate);
            $EndDate = new DateTime($EndDate);
            if (($fileLocation = ReportingPerson::useService()->createAbsenceListExcel($StartDate, $EndDate))) {
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Fehlzeiten " . $StartDate->format("Y-m-d") . " - " . $EndDate->format("Y-m-d") . ".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadClubList()
    {

        if (($dataList = ReportingPerson::useService()->createClubList())) {
            $fileLocation = ReportingPerson::useService()->createClubListExcel($dataList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Fördervereinsmitgliedschaft ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadStudentArchive(?string $YearId = null)
    {
        if (($tblYear = Term::useService()->getYearById($YearId))
        && !empty(($personList = DivisionCourse::useService()->getLeaveStudents($tblYear)))
        ) {
            $dataList = ReportingPerson::useService()->createStudentArchiveList($personList);
            $fileLocation = ReportingPerson::useService()->createStudentArchiveExcel($dataList);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Ehemalige Schüler " . $tblYear->getName() . ' ' . date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return bool|string
     */
    public function downloadClassRegisterAbsence($DivisionCourseId = null)
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
            && ($dataList = ReportingPerson::useService()->createAbsenceContentList($tblPersonList, $tblYear))
        ) {
            $name = 'Fehlzeiten der ' . $tblDivisionCourse->getTypeName() . $tblDivisionCourse->getName();
            $fileLocation = ReportingPerson::useService()->createAbsenceContentExcel($dataList);

            return FileSystem::getDownload($fileLocation->getRealPath(), $name . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param null $DivisionCourseId
     *
     * @return bool|string
     */
    public function downloadClassRegisterAbsenceMonthly($DivisionCourseId = null)
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())
        ) {
            $name = 'Fehlzeiten der ' . $tblDivisionCourse->getTypeName() . $tblDivisionCourse->getName();

            list($dataList, $countList) = Absence::useService()->getMonthAbsencesForExcelDownload($tblDivisionCourse);
            $fileLocation = ReportingPerson::useService()->createAbsenceContentExcelMonthly($tblPersonList, $dataList, $countList, $tblYear);

            return FileSystem::getDownload($fileLocation->getRealPath(), $name . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param int $View
     *
     * @return string
     */
    public function downloadDiplomaSerialMail(int $View): string
    {
        $tblCourse = false;
        switch ($View) {
            case View::HS: $tblCourse = Course::useService()->getCourseByName('Hauptschule');
                $tblSchoolType = Type::useService()->getTypeByShortName('OS');
                break;
            case View::RS: $tblCourse = Course::useService()->getCourseByName('Realschule');
                $tblSchoolType = Type::useService()->getTypeByShortName('OS');
                break;
            case View::FOS: $tblSchoolType = Type::useService()->getTypeByShortName('FOS');
                break;
            default: $tblSchoolType = false;
        }

        $subjectList = array();
        if($tblSchoolType
            && ($content = Reporting::useService()->getDiplomaSerialMailContent($tblSchoolType, $tblCourse ?: null, $subjectList))
            && ($fileLocation = Reporting::useService()->createDiplomaSerialMailContentExcel($content, $subjectList))
        ){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                'Serien E-Mail für Prüfungsnoten ' . $tblSchoolType->getShortName()
                . ($tblCourse ? ' ' . $tblCourse->getName() : '') . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return 'Keine Daten vorhanden!';
    }

    /**
     * @param int $View
     *
     * @return string
     */
    public function downloadDiplomaStatistic(int $View): string
    {
        $tblCourse = false;
        switch ($View) {
            case View::HS: $tblCourse = Course::useService()->getCourseByName('Hauptschule');
                $tblSchoolType = Type::useService()->getTypeByShortName('OS');
                break;
            case View::RS: $tblCourse = Course::useService()->getCourseByName('Realschule');
                $tblSchoolType = Type::useService()->getTypeByShortName('OS');
                break;
            case View::FOS: $tblSchoolType = Type::useService()->getTypeByShortName('FOS');
                break;
            default: $tblSchoolType = false;
        }

        if($tblSchoolType
            && ($content = Reporting::useService()->getDiplomaStatisticContent($tblSchoolType, $tblCourse ?: null))
            && ($fileLocation = Reporting::useService()->createDiplomaStatisticContentExcel($content))
        ){
            return FileSystem::getDownload($fileLocation->getRealPath(),
                'Auswertung der Prüfungsnoten für die LaSuB ' . $tblSchoolType->getShortName()
                . ($tblCourse ? ' ' . $tblCourse->getName() : '') . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return 'Keine Daten vorhanden!';
    }

    /**
     * @param $DivisionId
     *
     * @return string
     */
    public function downloadCourseGrades($DivisionId): string
    {
        if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionId))
            && ($content = Reporting::useService()->getCourseGradesContent($tblDivisionCourse))
            && ($fileLocation = Reporting::useService()->createCourseGradesContentExcel($content))
        ) {
            return FileSystem::getDownload($fileLocation->getRealPath(), 'Kursnoten '
                . $tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getName() . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return 'Keine Daten vorhanden!';
    }

    /**
     * @return string|bool
     */
    public function downloadDivisionTeacherList()
    {

        list($TableContent, $headers) = ReportingPerson::useService()->createDivisionTeacherList();
        if (!empty($TableContent)) {
            $fileLocation = ReportingPerson::useService()->createDivisionTeacherExcelList($TableContent, $headers);
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Klassenlehrer ".date("Y-m-d").".xlsx")->__toString();
        }
        return false;
    }
}
