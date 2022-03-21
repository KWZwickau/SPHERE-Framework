<?php
namespace SPHERE\Application\Api\Reporting\Standard\Person;

use DateTime;
use MOC\V\Core\FileSystem\FileSystem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;

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
     *
     * @return bool|string
     */
    public function downloadClassList($DivisionId = null, $GroupId = null)
    {
        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $tblGroup = false;
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $name = 'Klassenliste ' . $tblDivision->getDisplayName();
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
            $name = 'Stammgruppenliste ' . $tblGroup->getName();
        } else {
            return false;
        }

        if ($tblPersonList
            && ($DataList = ReportingPerson::useService()->createClassList($tblPersonList))
        ) {
            $fileLocation = ReportingPerson::useService()->createClassListExcel($DataList, $tblPersonList,
                $tblDivision ?: null, $tblGroup ?: null);

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
     *
     * @return bool|string
     */
    public function downloadMedicalRecordClassList($DivisionId = null, $GroupId = null)
    {
        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $name = 'Krankenakte_Klassenliste ' . $tblDivision->getDisplayName();
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
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
     *
     * @return bool|string
     */
    public function downloadAgreementClassList($DivisionId = null, $GroupId = null)
    {
        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $name = 'Einverständniserklärung_Klassenliste ' . $tblDivision->getDisplayName();
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
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
     * @param null $Date
     * @param null $DateTo
     * @param null $Type
     * @param string $DivisionName
     * @param string $GroupName
     * @param int $IsCertificateRelevant
     *
     * @return bool|string
     */
    public function downloadAbsenceList($Date = null, $DateTo = null, $Type = null, $DivisionName = '', $GroupName = '',
        int $IsCertificateRelevant = 0)
    {
        // das Datum darf keine Uhrzeit enthalten
        $dateTime = new DateTime((new DateTime($Date))->format('d.m.Y'));
        if ($DateTo == null || $DateTo == '') {
            $dateTimeTo = null;
        } else {
            $dateTimeTo = new DateTime((new DateTime($DateTo))->format('d.m.Y'));
        }
        if (($fileLocation = ReportingPerson::useService()->createAbsenceListExcel($dateTime, $dateTimeTo, $Type,
            $DivisionName, $GroupName, $IsCertificateRelevant))
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
    public function downloadClassRegisterAbsence($DivisionId = null, $GroupId = null)
    {
        if (($tblDivision = Division::useService()->getDivisionById($DivisionId))) {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
            $name = 'Fehlzeiten der Klasse ' . $tblDivision->getDisplayName();
        } elseif (($tblGroup = Group::useService()->getGroupById($GroupId))) {
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
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
}
