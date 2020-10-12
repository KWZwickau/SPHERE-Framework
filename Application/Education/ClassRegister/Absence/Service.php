<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.07.2016
 * Time: 09:05
 */

namespace SPHERE\Application\Education\ClassRegister\Absence;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Data;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsenceLesson;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\ViewAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence
 */
class Service extends AbstractService
{
    /**
     * @return false|ViewAbsence[]
     */
    public function viewAbsence()
    {

        return ( new Data($this->getBinding()) )->viewAbsence();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
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
     * @param TblPerson $tblPerson
     * @param TblDivision|null $tblDivision
     * @param bool $isForced
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByPerson(TblPerson $tblPerson, TblDivision $tblDivision = null, $isForced = false)
    {

        return (new Data($this->getBinding()))->getAbsenceAllByPerson($tblPerson, $tblDivision, $isForced);
    }

    /**
     * @param $Id
     *
     * @return false|TblAbsence
     */
    public function getAbsenceById($Id)
    {

        return (new Data($this->getBinding()))->getAbsenceById($Id);
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyAbsence(TblAbsence $tblAbsence, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyAbsence($tblAbsence, $IsSoftRemove);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param DateTime|null $tillDate
     * @param int $countLessons
     *
     * @return int
     */
    function getUnexcusedDaysByPerson(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        DateTime $tillDate = null,
        &$countLessons = 0
    ) {
        $list = array();
        // Fehlzeiten aus alle Klassen des Schuljahrs
        if (($tblDivisionList = Division::useService()->getOtherDivisionsByStudent($tblDivision, $tblPerson, true))) {
            foreach ($tblDivisionList as $tblDivisionItem) {
                if (($absenceList = $this->getAbsenceAllByPerson($tblPerson, $tblDivisionItem))) {
                    $list = array_merge($list, $absenceList);
                }
            }
        }

        $days = 0;
        /** @var TblAbsence $item */
        foreach ($list as $item) {
            if ($item->getStatus() == TblAbsence::VALUE_STATUS_UNEXCUSED) {
                $days += intval($item->getDays($tillDate, $countLessons));
            }
        }

        return $days;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param DateTime|null $tillDate
     *
     * @param int $countLessons
     * @return int
     */
    public function getExcusedDaysByPerson(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        DateTime $tillDate = null,
        &$countLessons = 0
    ) {
        $list = array();
        // Fehlzeiten aus alle Klassen des Schuljahrs
        if (($tblDivisionList = Division::useService()->getOtherDivisionsByStudent($tblDivision, $tblPerson, true))) {
            foreach ($tblDivisionList as $tblDivisionItem) {
                if (($absenceList = $this->getAbsenceAllByPerson($tblPerson, $tblDivisionItem))) {
                    $list = array_merge($list, $absenceList);
                }
            }
        }

        $days = 0;
        /** @var TblAbsence $item */
        foreach ($list as $item) {
            if ($item->getStatus() == TblAbsence::VALUE_STATUS_EXCUSED) {
                $days += intval($item->getDays($tillDate, $countLessons));
            }
        }

        return $days;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function destroyAbsenceAllByPerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblAbsenceList = $this->getAbsenceAllByPerson($tblPerson))){
            foreach($tblAbsenceList as $tblAbsence){
                $this->destroyAbsence($tblAbsence, $IsSoftRemove);
            }
        }
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return bool
     */
    public function restoreAbsence(TblAbsence $tblAbsence)
    {

        return (new Data($this->getBinding()))->restoreAbsence($tblAbsence);
    }

    /**
     * @return false|TblAbsence[]
     */
    public function getAbsenceAll()
    {

        return (new Data($this->getBinding()))->getAbsenceAll();
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return false|TblAbsence[]
     */
    public function getAbsenceAllByDivision(TblDivision $tblDivision)
    {

        return (new Data($this->getBinding()))->getAbsenceAllByDivision($tblDivision);
    }

    /**
     * @param DateTime $dateTime
     * @param TblType|null $tblType
     * @param array $divisionList
     * @param array $groupList
     * @param bool $hasAbsenceTypeOptions
     *
     * @return array
     */
    public function getAbsenceAllByDay(
        DateTime $dateTime,
        TblType $tblType = null,
        $divisionList = array(),
        $groupList = array(),
        &$hasAbsenceTypeOptions = false
    ) {
        $resultList = array();
        $tblAbsenceList = array();
        $isGroup = false;
        $groupPersonList = array();
        if (!empty($divisionList)
            && ($tblDivisionAll = Division::useService()->getDivisionAll())
        ) {
            foreach ($divisionList as $tblDivision) {
                if (($tblAbsenceDivisionList = $this->getAbsenceAllByDivision($tblDivision))) {
                    $tblAbsenceList = array_merge($tblAbsenceList, $tblAbsenceDivisionList);
                }
            }
        } elseif (!empty($groupList)) {
            $isGroup = true;
            foreach ($groupList as $tblGroup) {
                if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                    foreach ($tblPersonList as $tblPerson) {
                        $groupPersonList[$tblPerson->getId()] = $tblGroup->getName();
                        if (($tblAbsencePersonList = $this->getAbsenceAllByPerson($tblPerson))) {
                            $tblAbsenceList = array_merge($tblAbsenceList, $tblAbsencePersonList);
                        }
                    }
                }
            }
        } else {
            $tblAbsenceList = $this->getAbsenceAll();
        }

        if ($tblAbsenceList) {
            foreach ($tblAbsenceList as $tblAbsence) {
                $isAdd = false;
                $fromDate = new DateTime($tblAbsence->getFromDate());
                if ($fromDate->format('d.m.Y') == $dateTime->format('d.m.Y')) {
                    $isAdd = true;
                } elseif ($tblAbsence->getToDate()) {
                    $toDate = new DateTime($tblAbsence->getToDate());
                    if ($fromDate <= $dateTime && $toDate >= $dateTime) {
                        $isAdd = true;
                    }
                }

                if ($isAdd
                    && ($tblPerson = $tblAbsence->getServiceTblPerson())
                    && ($tblDivision = $tblAbsence->getServiceTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($tblTypeItem = $tblLevel->getServiceTblType())
                ) {
                    if (!$tblType || ($tblType->getId() == $tblTypeItem->getId())) {
                        $resultList[] = array(
                            'Type' => $tblTypeItem->getName(),
                            'TypeExcel' => $tblTypeItem->getShortName(),
                            'Division' => $tblDivision->getDisplayName(),
                            'Group' => $isGroup && isset($groupPersonList[$tblPerson->getId()]) ? $groupPersonList[$tblPerson->getId()] : '',
                            'Person' => $tblPerson->getLastFirstName(),
                            'DateSpan' => $tblAbsence->getDateSpan(),
                            'Status' => $tblAbsence->getStatusDisplayName(),
                            'StatusExcel' => $tblAbsence->getStatusDisplayShortName(),
                            'Remark' => $tblAbsence->getRemark(),
                            'AbsenceType' => $tblAbsence->getTypeDisplayName(),
                            'AbsenceTypeExcel' => $tblAbsence->getTypeDisplayShortName(),
                            'Lessons' => $tblAbsence->getLessonStringByAbsence()
                        );
                    }

                    if (!$hasAbsenceTypeOptions && $this->hasAbsenceTypeOptionsBySchoolType($tblTypeItem)) {
                        $hasAbsenceTypeOptions = true;
                    }
                }
            }
        }

        // Liste sortieren
        if (!empty($resultList)) {
            $type = $division = $group = $person = array();
            foreach ($resultList as $key => $row) {
                $type[$key] = strtoupper($row['Type']);
                $division[$key] = strtoupper($row['Division']);
                $group[$key] = strtoupper($row['Group']);
                $person[$key] = strtoupper($row['Person']);
            }

            if ($isGroup) {
                array_multisort($type, SORT_ASC, $group, SORT_NATURAL, $person, SORT_ASC, $resultList);
            } else {
                array_multisort($type, SORT_ASC, $division, SORT_NATURAL, $person, SORT_ASC, $resultList);
            }
        }

        return $resultList;
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     *
     * @return TblAbsence[]|bool
     */
    public function getAbsenceAllBetween(DateTime $fromDate, DateTime $toDate)
    {
        return (new Data($this->getBinding()))->getAbsenceAllBetween($fromDate, $toDate);
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @param TblDivision $tblDivision
     *
     * @return TblAbsence[]|bool
     */
    public function getAbsenceAllBetweenByDivision(DateTime $fromDate, DateTime $toDate, TblDivision $tblDivision)
    {
        return (new Data($this->getBinding()))->getAbsenceAllBetweenByDivision($fromDate, $toDate, $tblDivision);
    }

    /**
     * @param $Data
     * @param string $Search
     * @param TblAbsence|null $tblAbsence
     * @param null $PersonId
     * @param null $DivisionId
     * @param bool $hasSearch
     *
     * @return bool|Form
     */
    public function checkFormAbsence(
        $Data,
        $Search = '',
        TblAbsence $tblAbsence = null,
        $PersonId = null,
        $DivisionId = null,
        $hasSearch = false
    ) {

        $error = false;
        $messageSearch = null;
        $messageLesson = null;

        $tblPerson = false;
        $tblDivision = false;

        if ($PersonId && $DivisionId) {
            $tblPerson = Person::useService()->getPersonById($PersonId);
            $tblDivision = Division::useService()->getDivisionById($DivisionId);
        } elseif ($tblAbsence) {
            $tblPerson = $tblAbsence->getServiceTblPerson();
            $tblDivision = $tblAbsence->getServiceTblDivision();
        } else {
            if(!isset($Data['PersonId']) || !($tblPerson = Person::useService()->getPersonById($Data['PersonId']))) {
                $messageSearch = new Danger('Bitte wählen Sie einen Schüler aus.', new Exclamation());
                $error = true;
            }

            if ($tblPerson) {
                if (!($tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson))) {
                    $messageSearch = new Danger('Bitte wählen Sie einen Schüler aus, welcher sich aktuell in einer Klasse befindet.'
                        , new Exclamation()
                    );
                }
            }
        }

        // Prüfung ob Unterrichtseinheiten ausgewählt wurden
        if (!isset($Data['IsFullDay']) && !isset($Data['UE'])) {
            $messageLesson = new Danger('Bitte wählen Sie mindestens eine Unterrichtseinheit aus.', new Exclamation());
            $error = true;
        }

        $form = Absence::useFrontend()->formAbsence(
            $tblAbsence ? $tblAbsence->getId() : null,
            $hasSearch,
            $Search,
            $Data,
            $tblPerson ? $tblPerson->getId() : null,
            $tblDivision ? $tblDivision->getId() : null,
            $messageSearch,
            $messageLesson
        );

        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $form->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }

        $fromDate = null;
        $toDate = null;
        if (isset($Data['FromDate']) && !empty($Data['FromDate'])) {
            $fromDate = new DateTime($Data['FromDate']);
        }
        if (isset($Data['ToDate']) && !empty($Data['ToDate'])) {
            $toDate = new DateTime($Data['ToDate']);
        }

        if ($fromDate && $toDate) {
            if ($toDate->format('Y-m-d') < $fromDate->format('Y-m-d')){
                $form->setError('Data[ToDate]', 'Das "Datum bis" darf nicht kleiner sein Datum als das "Datum von"');
                $error = true;
            }
        }

        // Prüfung ob in diesem Zeitraum bereits eine Fehlzeit existiert
        if (!$error && !$tblAbsence && $tblPerson && $fromDate) {
            if (($list = (new Data($this->getBinding()))->getAbsenceAllBetweenByPerson($fromDate, $tblPerson, $toDate == $fromDate ? null : $toDate))) {
                $form->setError('Data[FromDate]', 'Es existiert bereits eine Fehlzeit im Bereich dieses Zeitraums');
//                if ($toDate) {
//                    $form->setError('Data[ToDate]', 'Es existiert bereits eine Fehlzeit im Bereich dieses Zeitraums');
//                }
                $error = true;
            }
        }

        if (!$error && $tblDivision && ($tblYear = $tblDivision->getServiceTblYear())) {
            list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDate && $endDate) {
                if ($fromDate < $startDate || $fromDate > $endDate) {
                    $form->setError(
                        'Data[FromDate]',
                        'Eingabe außerhalb des Schuljahres (' . $startDate->format('d.m.Y').' - ' . $endDate->format('d.m.Y') . ')'
                    );
                    $error = true;
                }

                if (isset($Data['ToDate']) && !empty($Data['ToDate'])) {
                    if ($toDate > $endDate) {
                        $form->setError(
                            'Data[FromDate]',
                            'Eingabe außerhalb des Schuljahres (' . $startDate->format('d.m.Y').' - ' . $endDate->format('d.m.Y') . ')'
                        );
                        $error = true;
                    }
                }
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool
     */
    public function hasAbsenceTypeOptions(TblDivision $tblDivision)
    {
        if (($tblLevel = $tblDivision->getTblLevel())
            && ($tblSchoolType = $tblLevel->getServiceTblType())
        ) {
            return $this->hasAbsenceTypeOptionsBySchoolType($tblSchoolType);
        }

        return false;
    }

    /**
     * @param TblType $tblSchoolType
     *
     * @return bool
     */
    public function hasAbsenceTypeOptionsBySchoolType(TblType $tblSchoolType)
    {
        if ($tblSchoolType->getName() == 'Berufliches Gymnasium'
            || $tblSchoolType->getName() == 'Berufsfachschule'
            || $tblSchoolType->getName() == 'Berufsschule'
            || $tblSchoolType->getName() == 'Fachoberschule'
            || $tblSchoolType->getName() == 'Fachschule'
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $Data
     * @param TblPerson|null $tblPerson
     * @param TblDivision|null $tblDivision
     *
     * @return bool
     */
    public function createAbsence($Data, TblPerson $tblPerson = null, TblDivision $tblDivision = null)
    {
        if ($tblPerson == null) {
            $tblPerson = Person::useService()->getPersonById($Data['PersonId']);
        }

        if ($tblDivision == null) {
            $tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson);
        }

        if ($tblPerson && $tblDivision) {
            if (($tblAbsence = (new Data($this->getBinding()))->createAbsence(
                $tblPerson,
                $tblDivision,
                $Data['FromDate'],
                $Data['ToDate'],
                $Data['Status'],
                $Data['Remark'],
                isset($Data['Type']) ? $Data['Type'] : TblAbsence::VALUE_TYPE_NULL
            ))) {
                if (isset($Data['UE'])) {
                    foreach ($Data['UE'] as $lesson => $value) {
                        (new Data($this->getBinding()))->addAbsenceLesson($tblAbsence, $lesson);
                    }
                }

                return  true;
            }
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     * @param $Data
     *
     * @return bool
     */
    public function updateAbsenceService(TblAbsence $tblAbsence, $Data)
    {
        if ((new Data($this->getBinding()))->updateAbsence(
            $tblAbsence,
            $Data['FromDate'],
            $Data['ToDate'],
            $Data['Status'],
            $Data['Remark'],
            isset($Data['Type']) ? $Data['Type'] : TblAbsence::VALUE_TYPE_NULL
        )) {
            for ($i = 1; $i < 11; $i++) {
                if (isset($Data['UE'][$i])) {
                    (new Data($this->getBinding()))->addAbsenceLesson($tblAbsence, $i);
                } else {
                    (new Data($this->getBinding()))->removeAbsenceLesson($tblAbsence, $i);
                }
            }

            return  true;
        }

        return false;
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return false|int[]
     */
    public function getLessonAllByAbsence(TblAbsence $tblAbsence)
    {
        $result = array();
        if (($list = (new Data($this->getBinding()))->getAbsenceLessonAllByAbsence($tblAbsence))) {
            foreach ($list as $tblAbsenceLesson) {
                $result[] = $tblAbsenceLesson->getLesson();
            }
        }

        return  empty($result) ? false : $result;
    }

    /**
     * @param TblAbsence $tblAbsence
     *
     * @return false|TblAbsenceLesson[]
     */
    public function getAbsenceLessonAllByAbsence(TblAbsence $tblAbsence)
    {
        return (new Data($this->getBinding()))->getAbsenceLessonAllByAbsence($tblAbsence);
    }
}