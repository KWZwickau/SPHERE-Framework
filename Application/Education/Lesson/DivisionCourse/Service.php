<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse;

use DateInterval;
use DateTime;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Lesson\Course\Course;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Data;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseLink;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMember;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblStudentEducation;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Setup;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\School\School;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

class Service extends AbstractService
{
    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8): string
    {
        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourse
     */
    public function getDivisionCourseById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseById($Id);
    }

    /**
     * @param string|null $TypeIdentifier
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseAll(?string $TypeIdentifier = '')
    {
        return (new Data($this->getBinding()))->getDivisionCourseAll($TypeIdentifier);
    }

    /**
     * @param TblYear|null $tblYear
     * @param string|null $TypeIdentifier
     *
     * @return false|TblDivisionCourse[]
     */
    public function getDivisionCourseListBy(TblYear $tblYear = null, ?string $TypeIdentifier = '')
    {
        return (new Data($this->getBinding()))->getDivisionCourseListBy($tblYear, $TypeIdentifier);
    }

    /**
     * @param string $name
     * @param array|null $tblYearList
     *
     * @return TblDivisionCourse[]|false
     */
    public function getDivisionCourseListByLikeName(string $name, ?array $tblYearList = null)
    {
        return (new Data($this->getBinding()))->getDivisionCourseListByLikeName($name, $tblYearList);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return TblDivisionCourseLink
     */
    public function addSubDivisionCourseToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblSubDivisionCourse)
    {
        return (new Data($this->getBinding()))->addSubDivisionCourseToDivisionCourse($tblDivisionCourse, $tblSubDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourse $tblSubDivisionCourse
     *
     * @return bool
     */
    public function removeSubDivisionCourseFromDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourse $tblSubDivisionCourse): bool
    {
        return (new Data($this->getBinding()))->removeSubDivisionCourseFromDivisionCourse($tblDivisionCourse, $tblSubDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblDivisionCourse[]|false
     */
    public function getSubDivisionCourseListByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getSubDivisionCourseListByDivisionCourse($tblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param array $resultList
     *
     * @return bool
     */
    public function getSubDivisionCourseRecursiveListByDivisionCourse(TblDivisionCourse $tblDivisionCourse, array &$resultList): bool
    {
        if (($tblDivisionCourseList = $this->getSubDivisionCourseListByDivisionCourse($tblDivisionCourse))) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (isset($resultList[$tblDivisionCourse->getId()])) {
                    return false;
                }
                $resultList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                $this->getSubDivisionCourseRecursiveListByDivisionCourse($tblDivisionCourse, $resultList);
            }
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseLink
     */
    public function getDivisionCourseLinkById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseLinkById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseTypeById($Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblDivisionCourseType
     */
    public function getDivisionCourseTypeByIdentifier(string $Identifier)
    {
        return (new Data($this->getBinding()))->getDivisionCourseTypeByIdentifier($Identifier);
    }

    /**
     * @return false|TblDivisionCourseType[]
     */
    public function getDivisionCourseTypeAll()
    {
        return (new Data($this->getBinding()))->getDivisionCourseTypeAll();
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getDivisionCourseMemberTypeById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseMemberTypeById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return false|TblDivisionCourseMemberType
     */
    public function getDivisionCourseMemberTypeByIdentifier($Identifier)
    {
        return (new Data($this->getBinding()))->getDivisionCourseMemberTypeByIdentifier($Identifier);
    }

    /**
     * @param $Filter
     * @param $Data
     * @param TblDivisionCourse|null $tblDivisionCourse
     *
     * @return false|Form
     */
    public function checkFormDivisionCourse($Filter, $Data, TblDivisionCourse $tblDivisionCourse = null)
    {
        $error = false;
        $form = DivisionCourse::useFrontend()->formDivisionCourse($tblDivisionCourse ? $tblDivisionCourse->getId() : null, $Filter);

        $tblYear = false;
        $tblType = false;
        if (!$tblDivisionCourse) {
            if (!isset($Data['Year']) || !($tblYear = Term::useService()->getYearById($Data['Year']))) {
                $form->setError('Data[Year]', 'Bitte wählen Sie ein Schuljahr aus');
                $error = true;
            }
            if (!isset($Data['Type']) || !($tblType = DivisionCourse::useService()->getDivisionCourseTypeById($Data['Type']))) {
                $form->setError('Data[Type]', 'Bitte wählen Sie einen Typ aus');
                $error = true;
            }
        }

        if (!isset($Data['Name']) || empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Name ein');
            $error = true;
        }
        if (isset($Data['Name']) && $Data['Name'] != '') {
            // Name Zeicheneingrenzung für Klassen und Stammgruppen, falls diese an angeschlossene Systeme übertragen werden müssen
            if ($tblType && ($tblType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)) {
                if (!preg_match('!^[\w\-,\/ ]+$!', $Data['Name'])) {
                    $form->setError('Data[Name]', 'Erlaubte Zeichen [a-zA-Z0-9, -_/]');
                    $error = true;
                }
            }
            // Prüfung ob name schon mal verwendet wird
            if ($tblYear && ($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear))) {
                foreach ($tblDivisionCourseList as $tblDivisionCourseItem) {
                    if ($tblDivisionCourse && $tblDivisionCourse->getId() == $tblDivisionCourseItem->getId()) {
                        continue;
                    }

                    if ($Data['Name'] == $tblDivisionCourseItem->getName()) {
                        $form->setError('Data[Name]', 'Ein Kurs mit diesem Name existiert bereits im Schuljahr');
                        $error = true;
                    }
                }
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param array $Data
     *
     * @return false|TblDivisionCourse
     */
    public function createDivisionCourse(array $Data)
    {
        if (($tblYear = Term::useService()->getYearById($Data['Year']))
            && ($tblType = DivisionCourse::useService()->getDivisionCourseTypeById($Data['Type']))
        ) {
            return (new Data($this->getBinding()))->createDivisionCourse($tblType, $tblYear, $Data['Name'], $Data['Description'],
                isset($Data['IsShownInPersonData']), isset($Data['IsReporting']));
        } else {
            return false;
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param array $Data
     *
     * @return bool
     */
    public function updateDivisionCourse(TblDivisionCourse $tblDivisionCourse, array $Data): bool
    {
        return (new Data($this->getBinding()))->updateDivisionCourse($tblDivisionCourse, $Data['Name'], $Data['Description'],
            isset($Data['IsShownInPersonData']), isset($Data['IsReporting']), isset($Data['IsUcs']));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function destroyDivisionCourse(TblDivisionCourse $tblDivisionCourse): bool
    {
        // Verknüpfungen mit anderen Kursen löschen
        if (($tblSubDivisionCourseList = $this->getSubDivisionCourseListByDivisionCourse($tblDivisionCourse))) {
            foreach ($tblSubDivisionCourseList as $tblSubDivisionCourse) {
                $this->removeSubDivisionCourseFromDivisionCourse($tblDivisionCourse, $tblSubDivisionCourse);
            }
        }

        // alle Mitglieder löschen
        (new Data($this->getBinding()))->removeDivisionCourseMemberAllFromDivisionCourse($tblDivisionCourse);

        return (new Data($this->getBinding()))->destroyDivisionCourse($tblDivisionCourse);
    }

    /**
     * @param $Id
     *
     * @return false|TblDivisionCourseMember
     */
    public function getDivisionCourseMemberById($Id)
    {
        return (new Data($this->getBinding()))->getDivisionCourseMemberById($Id);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     * @param TblPerson $tblPerson
     *
     * @return false|TblDivisionCourseMember
     */
    public function getDivisionCourseMemberByPerson(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType, TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblMemberType, $tblPerson);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param string $memberTypeIdentifier
     * @param bool $withInActive
     * @param bool $isResultPersonList
     *
     * @return TblDivisionCourseMember[]|TblPerson[]|false
     */
    public function getDivisionCourseMemberListBy(TblDivisionCourse $tblDivisionCourse, string $memberTypeIdentifier, bool $withInActive = false, bool $isResultPersonList = true)
    {
        $memberList = false;
        $tblMemberType = $this->getDivisionCourseMemberTypeByIdentifier($memberTypeIdentifier);
        if ($memberTypeIdentifier == TblDivisionCourseMemberType::TYPE_STUDENT && ($tblDivisionCourseType = $tblDivisionCourse->getType())
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                $memberList = (new Data($this->getBinding()))->getDivisionCourseMemberStudentByDivision($tblDivisionCourse);
            } elseif ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP) {
                $memberList = (new Data($this->getBinding()))->getDivisionCourseMemberStudentByCoreGroup($tblDivisionCourse);
            }
        } elseif ($tblMemberType) {
            $memberList = (new Data($this->getBinding()))->getDivisionCourseMemberListBy($tblDivisionCourse, $tblMemberType);
        }

        if (!empty ($memberList)) {
            // inaktive aussortieren
            if (!$withInActive) {
                $list = array();
                $now = new DateTime('now');
                foreach ($memberList as $tblDivisionCourseMember) {
                    if ($tblDivisionCourseMember->getLeaveDateTime() !== null && $now > $tblDivisionCourseMember->getLeaveDateTime()) {
                        // inaktiv
                    } else {
                        $list[] = $tblDivisionCourseMember;
                    }
                }

                $memberList = $list;
            }

            // ist Kursliste sortiert
            $isSorted = false;
            foreach ($memberList as $tblDivisionCourseMember) {
                if ($tblDivisionCourseMember->getSortOrder() !== null) {
                    $isSorted = true;
                    break;
                }
            }

            if ($isSorted) {
                $memberList = $this->getSorter($memberList)->sortObjectBy('SortOrder');
            } else {
                $memberList = $this->getSorter($memberList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
            }

            if ($isResultPersonList) {
                $personList = array();
                foreach ($memberList as $tblDivisionCourseMember) {
                    if ($tblDivisionCourseMember->getServiceTblPerson()) {
                        $personList[] = $tblDivisionCourseMember->getServiceTblPerson();
                    }
                }

                return empty($personList) ? false : $personList;
            } else {
                return empty($memberList) ? false : $memberList;
            }
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     * @param TblPerson $tblPerson
     * @param string $description
     *
     * @return TblDivisionCourseMember
     */
    public function addDivisionCourseMemberToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType,
        TblPerson $tblPerson, string $description): TblDivisionCourseMember
    {
        $maxSortOrder = $this->sortDivisionCourseMember($tblDivisionCourse, $tblMemberType);

        return (new Data($this->getBinding()))->addDivisionCourseMemberToDivisionCourse($tblDivisionCourse, $tblMemberType, $tblPerson, $description, $maxSortOrder);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function addStudentToDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson): bool
    {
        $maxSortOrder = $this->sortDivisionCourseMember($tblDivisionCourse, $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT));

        // Schüler in Klassen und Stammgruppen werden anders gespeichert (TblStudentEducation)
        if (($tblDivisionCourseType = $tblDivisionCourse->getType())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                    $tblStudentEducation->setTblDivision($tblDivisionCourse);
                    $tblStudentEducation->setDivisionSortOrder($maxSortOrder);
                } else {
                    $tblStudentEducation->setTblCoreGroup($tblDivisionCourse);
                    $tblStudentEducation->setCoreGroupSortOrder($maxSortOrder);
                }

                return (new Data($this->getBinding()))->updateStudentEducation($tblStudentEducation);
            } else {
                // Interessent
                $tblStudentEducation = new TblStudentEducation();
                $tblStudentEducation->setServiceTblPerson($tblPerson);
                $tblStudentEducation->setServiceTblYear($tblYear);
                if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                    $tblStudentEducation->setTblDivision($tblDivisionCourse);
                } else {
                    $tblStudentEducation->setTblCoreGroup($tblDivisionCourse);
                }

                if ((new Data($this->getBinding()))->createStudentEducation($tblStudentEducation)) {
                    return true;
                }
            }
        } else {
           if ((new Data($this->getBinding()))->addDivisionCourseMemberToDivisionCourse(
               $tblDivisionCourse, $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT), $tblPerson, '', $maxSortOrder
           )) {
               return true;
           }
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblDivisionCourseMemberType
     *
     * @return int|null
     */
    private function sortDivisionCourseMember(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblDivisionCourseMemberType): ?int
    {
        $maxSortOrder = $this->getDivisionCourseMemberMaxSortOrder($tblDivisionCourse, $tblDivisionCourseMemberType);
        // Kurs ist noch nicht sortiert
        if ($maxSortOrder === null) {
            // ist der Kurs im aktuellen Schuljahr und Schuljahr noch nicht älter als 1 Monat → Schüler sortieren und neuen Schüler hinten anfügen
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                $today = new DateTime('today');
                /** @var DateTime $startDate */
                list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDate && $endDate
                    && $today > $startDate
                    && $today < $endDate
                    && ($firstMonthDate = clone $startDate)
                    && $today > ($firstMonthDate->add(new DateInterval('P1M')))
                ) {
                    if (($tblMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy(
                        $tblDivisionCourse, $tblDivisionCourseMemberType->getIdentifier(), true, false
                    ))) {
                        $tblMemberList = (new Extension())->getSorter($tblMemberList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
                        $count = 1;
                        /** @var TblDivisionCourseMember $tblMember */
                        foreach ($tblMemberList as $tblMember) {
                            $tblMember->setSortOrder($count++);
                        }
                        DivisionCourse::useService()->updateDivisionCourseMemberBulkSortOrder(
                            $tblMemberList, $tblDivisionCourseMemberType->getIdentifier(), $tblDivisionCourse->getType() ?: null
                        );
                        $maxSortOrder = $count;
                    }
                }
            }
        } else {
            $maxSortOrder++;
        }

        return $maxSortOrder;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblDivisionCourseMemberType $tblMemberType
     *
     * @return int|null
     */
    private function getDivisionCourseMemberMaxSortOrder(TblDivisionCourse $tblDivisionCourse, TblDivisionCourseMemberType $tblMemberType): ?int
    {
        if ($tblMemberType->getIdentifier() == TblDivisionCourseMemberType::TYPE_STUDENT
            && ($tblDivisionCourseType = $tblDivisionCourse->getType())
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                return (new Data($this->getBinding()))->getStudentEducationDivisionMaxSortOrder($tblDivisionCourse);
            } else {
                return (new Data($this->getBinding()))->getStudentEducationCoreGroupMaxSortOrder($tblDivisionCourse);
            }
        } else {
            return (new Data($this->getBinding()))->getDivisionCourseMemberMaxSortOrder($tblDivisionCourse, $tblMemberType);
        }
    }

    /**
     * @param TblDivisionCourseMember $tblDivisionCourseMember
     *
     * @return bool
     */
    public function removeDivisionCourseMemberFromDivisionCourse(TblDivisionCourseMember $tblDivisionCourseMember): bool
    {
        return (new Data($this->getBinding()))->removeDivisionCourseMemberFromDivisionCourse($tblDivisionCourseMember);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function removeStudentFromDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson): bool
    {
        // Schüler in Klassen und Stammgruppen werden anders gespeichert (TblStudentEducation)
        if (($tblDivisionCourseType = $tblDivisionCourse->getType())
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                if (($tblStudentEducationList = (new Data($this->getBinding()))->getStudentEducationListByDivision($tblDivisionCourse, $tblPerson))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        $tblStudentEducation->setTblDivision(null);
                        $tblStudentEducation->setDivisionSortOrder(null);

                        (new Data($this->getBinding()))->updateStudentEducation($tblStudentEducation);
                    }

                    return true;
                }
            } elseif ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP) {
                if (($tblStudentEducationList = (new Data($this->getBinding()))->getStudentEducationListByCoreGroup($tblDivisionCourse, $tblPerson))) {
                    foreach ($tblStudentEducationList as $tblStudentEducation) {
                        $tblStudentEducation->setTblCoreGroup(null);
                        $tblStudentEducation->setCoreGroupSortOrder(null);

                        (new Data($this->getBinding()))->updateStudentEducation($tblStudentEducation);
                    }

                    return true;
                }
            }
        } else {
            if (($tblDivisionCourseMember = (new Data($this->getBinding()))->getDivisionCourseMemberByPerson(
                $tblDivisionCourse, $this->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_STUDENT), $tblPerson
            ))) {
                return (new Data($this->getBinding()))->removeDivisionCourseMemberFromDivisionCourse($tblDivisionCourseMember);
            }
        }

        return false;
    }

    /**
     * @param array $tblDivisionCourseMemberList
     * @param string $MemberTypeIdentifier
     * @param TblDivisionCourseType|null $tblDivisionCourseType
     *
     * @return bool
     */
    public function updateDivisionCourseMemberBulkSortOrder(array $tblDivisionCourseMemberList, string $MemberTypeIdentifier, ?TblDivisionCourseType $tblDivisionCourseType): bool
    {
        // Schüler in Klassen und Stammgruppen werden anders gespeichert (TblStudentEducation)
        if ($MemberTypeIdentifier == TblDivisionCourseMemberType::TYPE_STUDENT && $tblDivisionCourseType
            && ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION || $tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP)
        ) {
            $updateStudentEducationList = array();
            /** @var TblDivisionCourseMember $tblDivisionCourseMember */
            foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                if ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_DIVISION){
                    if (($tblPerson = $tblDivisionCourseMember->getServiceTblPerson())
                        && ($tblDivisionCourse = $tblDivisionCourseMember->getTblDivisionCourse())
                        && ($tblStudentEducation = (new Data($this->getBinding()))->getStudentEducationByDivision(
                            $tblDivisionCourse, $tblPerson, $tblDivisionCourseMember->getLeaveDateTime() ?: null
                        ))
                    ) {
                        $tblStudentEducation->setDivisionSortOrder($tblDivisionCourseMember->getSortOrder());
                        $updateStudentEducationList[] = $tblStudentEducation;
                    }
                } elseif ($tblDivisionCourseType->getIdentifier() == TblDivisionCourseType::TYPE_CORE_GROUP) {
                    if (($tblPerson = $tblDivisionCourseMember->getServiceTblPerson())
                        && ($tblDivisionCourse = $tblDivisionCourseMember->getTblDivisionCourse())
                        && ($tblStudentEducation = (new Data($this->getBinding()))->getStudentEducationByCoreGroup(
                            $tblDivisionCourse, $tblPerson, $tblDivisionCourseMember->getLeaveDateTime() ?: null
                        ))
                    ) {
                        $tblStudentEducation->setCoreGroupSortOrder($tblDivisionCourseMember->getSortOrder());
                        $updateStudentEducationList[] = $tblStudentEducation;
                    }
                }
            }

            return (new Data($this->getBinding()))->updateStudentEducationBulk($updateStudentEducationList);
        } else {
            return (new Data($this->getBinding()))->updateDivisionCourseMemberBulk($tblDivisionCourseMemberList);
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return string
     */
    public function getDivisionCourseHeader(TblDivisionCourse $tblDivisionCourse): string
    {
        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Kurs', $tblDivisionCourse->getName() . ' ' . new Small(new Muted($tblDivisionCourse->getTypeName())), Panel::PANEL_TYPE_INFO)
                    , 6),
                new LayoutColumn(
                    new Panel('Schuljahr', $tblDivisionCourse->getYearName(), Panel::PANEL_TYPE_INFO)
                    , 6)
            ))
        )));
    }

    /**
     * zählt die aktiven Schüler
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountStudentByDivisionCourse(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tblType = $tblDivisionCourse->getType())) {
            switch ($tblType->getIdentifier()) {
                case TblDivisionCourseType::TYPE_DIVISION: return (new Data($this->getBinding()))->getCountStudentByDivision($tblDivisionCourse);
                case TblDivisionCourseType::TYPE_CORE_GROUP: return (new Data($this->getBinding()))->getCountStudentByCoreGroup($tblDivisionCourse);
                default: return (new Data($this->getBinding()))->getCountStudentByDivisionCourse($tblDivisionCourse);
            }
        }

        return 0;
    }

    /**
     * zählt die inaktiven Schüler
     *
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return int
     */
    public function getCountInActiveStudentByDivisionCourse(TblDivisionCourse $tblDivisionCourse): int
    {
        if (($tblType = $tblDivisionCourse->getType())) {
            switch ($tblType->getIdentifier()) {
                case TblDivisionCourseType::TYPE_DIVISION: return (new Data($this->getBinding()))->getCountInActiveStudentByDivision($tblDivisionCourse);
                case TblDivisionCourseType::TYPE_CORE_GROUP: return (new Data($this->getBinding()))->getCountInActiveStudentByCoreGroup($tblDivisionCourse);
                default: return (new Data($this->getBinding()))->getCountInActiveStudentByDivisionCourse($tblDivisionCourse);
            }
        }

        return 0;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function getStudentInfoByDivisionCourse(TblDivisionCourse $tblDivisionCourse): array
    {
        $countActive = 0;
        $countInActive = 0;
        $genderList = array();
        $genders = '';

        if (($tblStudentMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse,
            TblDivisionCourseMemberType::TYPE_STUDENT, true, false))
        ) {
            foreach ($tblStudentMemberList as $tblStudentMember) {
                if (($tblPerson = $tblStudentMember->getServiceTblPerson())) {
                    if ($tblStudentMember->isInActive()) {
                        $countInActive++;
                    } else {
                        $countActive++;
                    }

                    if(($tblGender = $tblPerson->getGender())){
                        if (isset($genderList[$tblGender->getShortName()])) {
                            $genderList[$tblGender->getShortName()]++;
                        } else {
                            $genderList[$tblGender->getShortName()] = 1;
                        }
                    }
                }
            }

            foreach($genderList as $key => $count) {
                $genders .= ($genders ? '<br/>' : '') . $key . ': ' . $count;
            }
        }

        $toolTip = $countInActive . ($countInActive == 1 ? ' deaktivierter Schüler' : ' deaktivierte Schüler');
        $students = $countActive . ($countInActive > 0 ? ' + ' . new ToolTip('(' . $countInActive . new Info() . ')', $toolTip) : '');

        return array($students, $genders);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return false|TblStudentEducation
     */
    public function getStudentEducationByPersonAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
    }

    /**
     * @return array|bool|TblCompany[]
     */
    public function getSchoolListForStudentEducation()
    {
        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        $tblCompanyAllOwn = array();

        // Normaler Inhalt
        $tblSchoolList = School::useService()->getSchoolAll();
        if ($tblSchoolList) {
            foreach ($tblSchoolList as $tblSchool) {
                if ($tblSchool->getServiceTblCompany()) {
                    $tblCompanyAllOwn[] = $tblSchool->getServiceTblCompany();
                }
            }
        }

        if (empty($tblCompanyAllOwn)) {
            $resultList = $tblCompanyAllSchool;
        } else {
            $resultList = $tblCompanyAllOwn;
        }

        return $resultList;
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     *
     * @return false|Form
     */
    public function checkFormChangeDivisionCourse($Data, TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson)
    {
        $error = false;
        $form = DivisionCourse::useFrontend()->formChangeDivisionCourse($tblDivisionCourse, $tblPerson, $tblDivisionCourse->getServiceTblYear());

        $tblSchoolType = false;
        if (!isset($Data['SchoolType']) || !($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))) {
            $form->setError('Data[SchoolType]', 'Bitte wählen Sie eine Schulart aus');
            $error = true;
        }
        if (!isset($Data['Level']) || empty($Data['Level']) || !intval($Data['Level'])) {
            $form->setError('Data[Level]', 'Bitte geben Sie eine Klassenstufe an');
            $error = true;
        } elseif ($tblSchoolType) {
            $level = $Data['Level'];
            if ($level < 1) {
                $form->setError('Data[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                $error = true;
            } else {
                switch ($tblSchoolType->getShortName()) {
                    case 'GS':
                        if ($level > 4) {
                            $form->setError('Data[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                            $error = true;
                        }
                        break;
                    case 'OS':
                        if ($level < 5 || $level > 10) {
                            $form->setError('Data[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                            $error = true;
                        }
                        break;
                    case 'Gy':
                        if ($level < 5 || $level > 12) {
                            $form->setError('Data[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                            $error = true;
                        }
                        break;
                    default:
                        if ($level > 13) {
                            $form->setError('Data[Level]', 'Bitte geben Sie eine gültige Klassenstufe an');
                            $error = true;
                        }
                }
            }
        }
        if (!isset($Data['Company']) || !(Company::useService()->getCompanyById($Data['Company']))) {
            $form->setError('Data[Company]', 'Bitte wählen Sie eine Schule aus');
            $error = true;
        }

        if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
            if (($tblDivisionCourseNew = DivisionCourse::useService()->getDivisionCourseById($Data['Division']))
                && $tblDivisionCourseNew->getId() == $tblDivisionCourse->getId())
            {
                $form->setError('Data[Division]', 'Bitte wählen Sie eine neue Klasse aus');
                $error = true;
            }
        } else {
            if (($tblDivisionCourseNew = DivisionCourse::useService()->getDivisionCourseById($Data['CoreGroup']))
                && $tblDivisionCourseNew->getId() == $tblDivisionCourse->getId()
            ) {
                $form->setError('Data[CoreGroup]', 'Bitte wählen Sie eine neue Stammgruppe aus');
                $error = true;
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblPerson $tblPerson
     * @param $Data
     *
     * @return bool
     */
    public function changeDivisionCourse(TblDivisionCourse $tblDivisionCourse, TblPerson $tblPerson, $Data): bool
    {
        if (($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblStudentEducationOld = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
            && ($tblSchoolType = Type::useService()->getTypeById($Data['SchoolType']))
            && ($tblCompany = Company::useService()->getCompanyById($Data['Company']))
        ) {
            $leaveDate = new DateTime('now');
            if ($tblDivisionCourse->getTypeIdentifier() == TblDivisionCourseType::TYPE_DIVISION) {
                $tblDivisionCourseCoreGroupOld = $tblStudentEducationOld->getTblCoreGroup();
                $tblDivisionCourseCoreGroupNew = DivisionCourse::useService()->getDivisionCourseById($Data['CoreGroup']);
                if ($tblDivisionCourseCoreGroupNew && $tblDivisionCourseCoreGroupOld
                    && $tblDivisionCourseCoreGroupNew->getId() == $tblDivisionCourseCoreGroupOld->getId()
                ) {
                    $coreGroupSortOrder = $tblStudentEducationOld->getCoreGroupSortOrder();
                    // Stammgruppe bleibt → am alten TblStudentEducation Eintrag löschen
                    (new Data($this->getBinding()))->updateStudentEducationByProperties(
                        $tblStudentEducationOld,
                        $tblStudentEducationOld->getTblDivision() ?: null,
                        $tblStudentEducationOld->getDivisionSortOrder(),
                        null,
                        null,
                        $leaveDate
                    );
                } else {
                    $coreGroupSortOrder = null;
                    (new Data($this->getBinding()))->updateStudentEducationByProperties(
                        $tblStudentEducationOld,
                        $tblStudentEducationOld->getTblDivision() ?: null,
                        $tblStudentEducationOld->getDivisionSortOrder(),
                        $tblStudentEducationOld->getTblCoreGroup() ?: null,
                        $tblStudentEducationOld->getCoreGroupSortOrder(),
                        $leaveDate
                    );
                }

                $tblStudentEducationNew = new TblStudentEducation();
                $tblStudentEducationNew->setTblDivision(DivisionCourse::useService()->getDivisionCourseById($Data['Division']) ?: null);
                $tblStudentEducationNew->setTblCoreGroup($tblDivisionCourseCoreGroupNew ?: null);
                $tblStudentEducationNew->setCoreGroupSortOrder($coreGroupSortOrder);
            } else {
                $tblDivisionCourseDivisionOld = $tblStudentEducationOld->getTblDivision();
                $tblDivisionCourseDivisionNew = DivisionCourse::useService()->getDivisionCourseById($Data['Division']);
                if ($tblDivisionCourseDivisionNew && $tblDivisionCourseDivisionOld
                    && $tblDivisionCourseDivisionNew->getId() == $tblDivisionCourseDivisionOld->getId()
                ) {
                    $divisionSortOrderNew = $tblStudentEducationOld->getDivisionSortOrder();
                    // Klasse bleibt → am alten TblStudentEducation Eintrag löschen
                    $tblStudentEducationOld->setTblDivision(null);
                    $tblStudentEducationOld->setDivisionSortOrder(null);
                    (new Data($this->getBinding()))->updateStudentEducationByProperties(
                        $tblStudentEducationOld,
                        null,
                        null,
                        $tblStudentEducationOld->getTblCoreGroup() ?: null,
                        $tblStudentEducationOld->getCoreGroupSortOrder(),
                        $leaveDate
                    );
                } else {
                    $divisionSortOrderNew = null;
                    (new Data($this->getBinding()))->updateStudentEducationByProperties(
                        $tblStudentEducationOld,
                        $tblStudentEducationOld->getTblDivision() ?: null,
                        $tblStudentEducationOld->getDivisionSortOrder(),
                        $tblStudentEducationOld->getTblCoreGroup() ?: null,
                        $tblStudentEducationOld->getCoreGroupSortOrder(),
                        $leaveDate
                    );
                }

                $tblStudentEducationNew = new TblStudentEducation();
                $tblStudentEducationNew->setTblCoreGroup(DivisionCourse::useService()->getDivisionCourseById($Data['CoreGroup']) ?: null);
                $tblStudentEducationNew->setTblDivision($tblDivisionCourseDivisionNew ?: null);
                $tblStudentEducationNew->setDivisionSortOrder($divisionSortOrderNew);
            }

            $tblStudentEducationNew->setServiceTblPerson($tblPerson);
            $tblStudentEducationNew->setServiceTblYear($tblYear);
            $tblStudentEducationNew->setLevel($Data['Level']);
            $tblStudentEducationNew->setServiceTblSchoolType($tblSchoolType);
            $tblStudentEducationNew->setServiceTblCompany($tblCompany);
            $tblStudentEducationNew->setServiceTblCourse(Course::useService()->getCourseById($Data['Course']) ?: null);

            if ((new Data($this->getBinding()))->createStudentEducation($tblStudentEducationNew)) {
                return true;
            }
        }

        return false;
    }
}