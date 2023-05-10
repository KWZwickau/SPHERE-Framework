<?php
namespace SPHERE\Application\Api\Reporting\Standard\Person;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Certificate\Reporting\Reporting;
use SPHERE\Application\Education\Certificate\Reporting\View;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;
use SPHERE\System\Extension\Extension;

/**
 * Class Person
 *
 * @package SPHERE\Application\Api\Reporting\Standard\Person
 */
class Person
{
    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $DivisionSubjectId
     *
     * @return false|string
     */
    public function downloadClassList($DivisionId = null, $GroupId = null, $DivisionSubjectId = null)
    {
        $tblDivision = false;
        $tblGroup = false;
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
            $name = 'Kursliste '
                . (($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup()) ? $tblSubjectGroup->getName() : '');
        } elseif (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $name = 'Klassenliste ' . $tblDivision->getDisplayName();
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $tblPersonList = $tblGroup->getStudentOnlyList();
            $name = 'Stammgruppenliste ' . $tblGroup->getName();
        } else {
            return false;
        }

        if ($tblPersonList
            && ($DataList = ReportingPerson::useService()->createClassList($tblPersonList))
        ) {
            $fileLocation = ReportingPerson::useService()->createClassListExcel($DataList, $tblPersonList,
                $tblDivision ?: null, $tblGroup ?: null, $tblDivisionSubject ?: null);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                $name . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadExtendedClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createExtendedClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createExtendedClassListExcel($PersonList, $tblPersonList, $tblDivision);
                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Erweiterte_Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadBirthdayClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createBirthdayClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createBirthdayClassListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Birthday_Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadMedicalInsuranceClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createMedicalInsuranceClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createMedicalInsuranceClassListExcel($PersonList, $tblPersonList);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Krankenkasse_Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $GroupId
     *
     * @return bool|string
     */
    public function downloadGroupList($GroupId = null)
    {

        $tblGroup = Group::useService()->getGroupById($GroupId);
        if ($tblGroup) {
            $PersonList = ReportingPerson::useService()->createGroupList($tblGroup);
            if ($PersonList) {
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createGroupListExcel($PersonList, $tblPersonList, $GroupId);

                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Gruppenliste ".$tblGroup->getName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadInterestedPersonList()
    {

        $hasGuardian = false;
        $hasAuthorizedPerson = false;
        $PersonList = ReportingPerson::useService()->createInterestedPersonList($hasGuardian, $hasAuthorizedPerson);
        if ($PersonList) {
            $firstName = array();
            foreach ($PersonList as $key => $row) {
                $name[$key] = strtoupper($row['LastName']);
                $firstName[$key] = strtoupper($row['FirstName']);
            }
            array_multisort($name, SORT_ASC, $firstName, SORT_ASC, $PersonList);

            $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT));
            if ($tblPersonList) {
                $fileLocation = ReportingPerson::useService()->createInterestedPersonListExcel($PersonList, $tblPersonList, $hasGuardian, $hasAuthorizedPerson);

                return FileSystem::getDownload($fileLocation->getRealPath(),
                    "Interessentenliste ".date("Y-m-d H:i:s").".xlsx")->__toString();
            }
        }

        return false;
    }

    /**
     * @param null $DivisionId
     *
     * @return bool|string
     */
    public function downloadElectiveClassList($DivisionId = null)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        if ($tblDivision) {
            $PersonList = ReportingPerson::useService()->createElectiveClassList($tblDivision);
            if ($PersonList) {
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
                if ($tblPersonList) {
                    $fileLocation = ReportingPerson::useService()->createElectiveClassListExcel($PersonList,
                        $tblPersonList
                        , $tblDivision->getId());
                    return FileSystem::getDownload($fileLocation->getRealPath(),
                        "Wahlfächer_Klassenliste ".$tblDivision->getDisplayName()
                        ." ".date("Y-m-d H:i:s").".xlsx")->__toString();
                }
            }
        }

        return false;
    }

    /**
     * @param null $Person
     * @param null $Year
     * @param null $Division
     * @param null $Option
     * @param null $PersonGroup
     *
     * @return string
     */
    public function downloadMetaDataComparison($Person = null, $Year = null, $Division = null, $Option = null, $PersonGroup = null)
    {

        $fileLocation = ReportingPerson::useService()->createMetaDataComparisonExcel($Person, $Year, $Division, $Option, $PersonGroup);
        return FileSystem::getDownload($fileLocation->getRealPath(),"Stammdatenabfrage"." ".date("Y-m-d H:i:s").".xlsx")->__toString();
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $DivisionSubjectId
     *
     * @return false|string
     */
    public function downloadMedicalRecordClassList($DivisionId = null, $GroupId = null, $DivisionSubjectId = null)
    {
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
            $name = 'Krankenakte_Kursliste '
                . (($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup()) ? $tblSubjectGroup->getName() : '');
        } elseif (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $name = 'Krankenakte_Klassenliste ' . $tblDivision->getDisplayName();
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $tblPersonList = $tblGroup->getStudentOnlyList();
            $name = 'Krankenakte_Stammgruppenliste ' . $tblGroup->getName();
        } else {
            return false;
        }

        if ($tblPersonList
            && ($DataList = ReportingPerson::useService()->createMedicalRecordClassList($tblPersonList))
        ) {
            $fileLocation = ReportingPerson::useService()->createMedicalRecordClassListExcel($DataList, $tblPersonList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                $name . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $DivisionSubjectId
     *
     * @return false|string
     */
    public function downloadAgreementClassList($DivisionId = null, $GroupId = null, $DivisionSubjectId = null)
    {
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
            $name = 'Einverständniserklärung_Kursliste '
                . (($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup()) ? $tblSubjectGroup->getName() : '');
        } elseif (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $name = 'Einverständniserklärung_Klassenliste ' . $tblDivision->getDisplayName();
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $tblPersonList = $tblGroup->getStudentOnlyList();
            $name = 'Einverständniserklärung_Stammgruppenliste ' . $tblGroup->getName();
        } else {
            return false;
        }

        if ($tblPersonList
            && ($DataList = ReportingPerson::useService()->createAgreementClassList($tblPersonList))
        ) {
            $fileLocation = ReportingPerson::useService()->createAgreementClassListExcel($DataList, $tblPersonList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                $name . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
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

        $tblYear = $tblGroup = $tblType = false;
        if(!empty($Data['Year'])){
            $tblYear = Term::useService()->getYearById($Data['Year']);
        }
        if(!empty($Data['Group'])){
            $tblGroup = \SPHERE\Application\People\Search\Group\Group::useService()->getGroupById($Data['Group']);
        }
        if(!empty($Data['Type'])){
            $tblType = Type::useService()->getTypeById($Data['Type']);
        }
        $Level = !empty($Data['Level']) ? $Data['Level'] : '';
        $Division = !empty($Data['Division']) ? $Data['Division'] : '';
        if($tblYear){
            $tblPersonList = Individual::useService()->getStudentPersonListByFilter($tblYear, $tblGroup, $tblType,
                $Level, $Division);
            if($tblPersonList && ($DataList = ReportingPerson::useService()->createAgreementList($tblPersonList))){
                $fileLocation = ReportingPerson::useService()->createAgreementClassListExcel($DataList, $tblPersonList);
                return FileSystem::getDownload($fileLocation->getRealPath(),
                    'Einverständniserklärung_Schüler ' . date("Y-m-d H:i:s").".xlsx")->__toString();
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
        if($tblPersonList && ($DataList = ReportingPerson::useService()->createPersonAgreementList($tblPersonList))){
            $fileLocation = ReportingPerson::useService()->createAgreementPersonListExcel($DataList, $tblPersonList);
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
     * @param string $GroupName
     * @param int $IsCertificateRelevant
     * @param bool $IsAbsenceOnlineOnly
     *
     * @return bool|string
     */
    public function downloadAbsenceList($Date = null, $DateTo = null, $Type = null, $DivisionName = '', $GroupName = '',
        int $IsCertificateRelevant = 0, bool $IsAbsenceOnlineOnly = false)
    {
        // das Datum darf keine Uhrzeit enthalten
        $dateTime = new DateTime((new DateTime($Date))->format('d.m.Y'));
        if ($DateTo == null || $DateTo == '') {
            $dateTimeTo = null;
        } else {
            $dateTimeTo = new DateTime((new DateTime($DateTo))->format('d.m.Y'));
        }
        if (($fileLocation = ReportingPerson::useService()->createAbsenceListExcel($dateTime, $dateTimeTo, $Type,
            $DivisionName, $GroupName, $IsCertificateRelevant, $IsAbsenceOnlineOnly))
        ) {
            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Fehlzeiten " . $dateTime->format("Y-m-d") . ".xlsx")->__toString();
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

            if (($fileLocation = ReportingPerson::useService()->createAbsenceBetweenListExcel($StartDate, $EndDate))) {
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

        $PersonList = ReportingPerson::useService()->createClubList();
        if ($PersonList) {
            $fileLocation = ReportingPerson::useService()->createClubListExcel($PersonList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Fördervereinsmitgliedschaft ".date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @return string|bool
     */
    public function downloadStudentArchive(?string $YearId = null)
    {
        if (($tblYear = Term::useService()->getYearById($YearId))
            && ($personList = Division::useService()->getLeaveStudents($tblYear))
        ) {
            $dataList = ReportingPerson::useService()->createStudentArchiveList($personList);

            $fileLocation = ReportingPerson::useService()->createStudentArchiveExcel($dataList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Ehemalige Schüler " . $tblYear->getName() . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     *
     * @return bool|string
     */
    public function downloadClassRegisterAbsence($DivisionId = null, $GroupId = null, $DivisionSubjectId = null)
    {
        $tblDivision = false;
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
            $name = 'Fehlzeiten des Kurses '
                . (($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup()) ? $tblSubjectGroup->getName() : '');
        } elseif (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $name = 'Fehlzeiten der Klasse ' . $tblDivision->getDisplayName();
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $tblPersonList = $tblGroup->getStudentOnlyList();
            $name = 'Fehlzeiten der Stammgruppe ' . $tblGroup->getName();
        } else {
            return false;
        }

        if ($tblPersonList
            && ($DataList = ReportingPerson::useService()->createAbsenceContentList($tblPersonList, $tblDivision ?: null))
        ) {
            $fileLocation = ReportingPerson::useService()->createAbsenceContentExcel($DataList);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                $name . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     *
     * @return bool|string
     */
    public function downloadClassRegisterAbsenceMonthly($DivisionId = null, $GroupId = null, $DivisionSubjectId = null)
    {
        $tblDivision = false;
        if (($tblDivisionSubject = Division::useService()->getDivisionSubjectById($DivisionSubjectId))) {
            $tblPersonList = Division::useService()->getStudentByDivisionSubject($tblDivisionSubject);
            $name = 'Fehlzeiten des Kurses '
                . (($tblSubjectGroup = $tblDivisionSubject->getTblSubjectGroup()) ? $tblSubjectGroup->getName() : '');
        } elseif (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $name = 'Fehlzeiten der Klasse ' . $tblDivision->getDisplayName();
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $tblPersonList = $tblGroup->getStudentOnlyList();
            $name = 'Fehlzeiten der Stammgruppe ' . $tblGroup->getName();
        } else {
            return false;
        }

        if ($tblPersonList
            && $tblDivision
            && ($tblYear = $tblDivision->getServiceTblYear())
        ) {
            list($dataList, $countList) = Absence::useService()->getAbsenceForExcelDownload($tblDivision);
            $fileLocation = ReportingPerson::useService()->createAbsenceContentExcelMonthly($tblPersonList, $dataList, $countList, $tblYear);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                $name . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
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
        if(($tblDivision = Division::useService()->getDivisionById($DivisionId))
            && ($content = Reporting::useService()->getCourseGradesContent($tblDivision))
            && ($fileLocation = Reporting::useService()->createCourseGradesContentExcel($content))
        ){
            return FileSystem::getDownload($fileLocation->getRealPath(), 'Kursnoten '
                . $tblDivision->getTypeName() . ' Klasse ' . $tblDivision->getDisplayName() . ' ' . date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return 'Keine Daten vorhanden!';
    }

    /**
     * @return string|bool
     */
    public function downloadDivisionTeacherList()
    {
        list($TableContent, $headers) = ReportingPerson::useService()->createDivisionTeacherList();
        if ($TableContent) {
            $fileLocation = ReportingPerson::useService()->createDivisionTeacherExcelList($TableContent, $headers);

            return FileSystem::getDownload($fileLocation->getRealPath(),
                "Klassenlehrer".date("Y-m-d H:i:s").".xlsx")->__toString();
        }

        return false;
    }
}
