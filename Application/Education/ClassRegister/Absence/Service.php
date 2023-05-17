<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.07.2016
 * Time: 09:05
 */

namespace SPHERE\Application\Education\ClassRegister\Absence;

use DateTime;
use phpDocumentor\Reflection\Types\Array_;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Data;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\TblAbsenceLesson;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Entity\ViewAbsence;
use SPHERE\Application\Education\ClassRegister\Absence\Service\Setup;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\ParentStudentAccess\OnlineAbsence\OnlineAbsence;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\Table;
use SPHERE\Common\Frontend\Table\Structure\TableBody;
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * @deprecated
 *
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
        int &$countLessons = 0,
        bool $IsCertificateRelevant = true
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
            if ($item->getStatus() == TblAbsence::VALUE_STATUS_UNEXCUSED
                && $item->getIsCertificateRelevant() == $IsCertificateRelevant
            ) {
                $tblDivisionByAbsence = $item->getServiceTblDivision();
                $days += intval($item->getDays($tillDate, $countLessons,
                    ($tblCompany = $tblDivisionByAbsence->getServiceTblCompany()) ? $tblCompany : null
                ));
            }
        }

        return $days;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param DateTime|null $tillDate
     * @param int $countLessons
     * @param bool $IsCertificateRelevant
     *
     * @return int
     */
    public function getExcusedDaysByPerson(
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        DateTime $tillDate = null,
        int &$countLessons = 0,
        bool $IsCertificateRelevant = true
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
            if ($item->getStatus() == TblAbsence::VALUE_STATUS_EXCUSED
                && $item->getIsCertificateRelevant() == $IsCertificateRelevant
            ) {
                $tblDivisionByAbsence = $item->getServiceTblDivision();
                $days += intval($item->getDays($tillDate, $countLessons,
                    ($tblCompany = $tblDivisionByAbsence->getServiceTblCompany()) ? $tblCompany : null
                ));
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
     * @param DateTime $fromDate
     * @param DateTime|null $toDate
     * @param TblType|null $tblType
     * @param array $divisionList
     * @param array $groupList
     * @param bool $hasAbsenceTypeOptions
     * @param null|bool $IsCertificateRelevant
     * @param bool $IsOnlineAbsenceOnly
     *
     * @return array
     */
    public function getAbsenceAllByDay(
        DateTime $fromDate,
        DateTime $toDate = null,
        TblType $tblType = null,
        $divisionList = array(),
        $groupList = array(),
        &$hasAbsenceTypeOptions = false,
        $IsCertificateRelevant = true,
        bool $IsOnlineAbsenceOnly = false
    ) {
        $resultList = array();
        $tblAbsenceList = array();
        $isGroup = false;
        $groupPersonList = array();

        if ($toDate == null) {
            $toDate = $fromDate;
        }

        if (!empty($divisionList)
            && (Division::useService()->getDivisionAll())
        ) {
            foreach ($divisionList as $tblDivision) {
                if (($tblAbsenceDivisionList = $this->getAbsenceAllBetweenByDivision($fromDate, $toDate, $tblDivision))) {
                    $tblAbsenceList = array_merge($tblAbsenceList, $tblAbsenceDivisionList);
                }
            }
        } elseif (!empty($groupList)) {
            $isGroup = true;
            foreach ($groupList as $tblGroup) {
                if (($tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup))) {
                    foreach ($tblPersonList as $tblPerson) {
                        $groupPersonList[$tblPerson->getId()] = $tblGroup->getName();
                        if (($tblAbsencePersonList = (new Data($this->getBinding()))->getAbsenceAllBetweenByPerson(
                            $fromDate, $tblPerson, $toDate))
                        ) {
                            $tblAbsenceList = array_merge($tblAbsenceList, $tblAbsencePersonList);
                        }
                    }
                }
            }
        } else {
            $tblAbsenceList = $this->getAbsenceAllBetween($fromDate, $toDate);
        }

        if ($tblAbsenceList) {
            foreach ($tblAbsenceList as $tblAbsence) {
                if (($tblPerson = $tblAbsence->getServiceTblPerson())
                    && ($tblDivision = $tblAbsence->getServiceTblDivision())
                    && ($tblLevel = $tblDivision->getTblLevel())
                    && ($tblTypeItem = $tblLevel->getServiceTblType())
                ) {
                    // Zeugnisrelevant filtern
                    if ($IsCertificateRelevant !== null && $IsCertificateRelevant !== $tblAbsence->getIsCertificateRelevant()) {
                        continue;
                    }

                    // Nur Online Fehlzeiten filtern
                    if ($IsOnlineAbsenceOnly && !$tblAbsence->getIsOnlineAbsence()) {
                        continue;
                    }

                    if (!$tblType || ($tblType->getId() == $tblTypeItem->getId())) {
                        $resultList = $this->setAbsenceContent($tblTypeItem, $tblDivision, $isGroup, $groupPersonList,
                            $tblPerson, $tblAbsence, $resultList);
                    }

                    if (!$hasAbsenceTypeOptions && $tblTypeItem->isTechnical()) {
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
                $date[$key] = $row['DateSort'];
            }

            if ($isGroup) {
                array_multisort($type, SORT_ASC, $group, SORT_NATURAL, $person, SORT_ASC, $date, SORT_ASC, $resultList);
            } else {
                array_multisort($type, SORT_ASC, $division, SORT_NATURAL, $person, SORT_ASC, $date, SORT_ASC, $resultList);
            }
        }

        return $resultList;
    }

    /**
     * @param TblType $tblType
     * @param TblDivision $tblDivision
     * @param $isGroup
     * @param array $groupPersonList
     * @param TblPerson $tblPerson
     * @param TblAbsence $tblAbsence
     * @param array $resultList
     * @return array
     */
    public function setAbsenceContent(
        TblType $tblType,
        TblDivision $tblDivision,
        $isGroup,
        array $groupPersonList,
        TblPerson $tblPerson,
        TblAbsence $tblAbsence,
        array $resultList
    ) {

        $isOnlineAbsence = $tblAbsence->getIsOnlineAbsence();

        $resultList[] = array(
            'AbsenceId' => $tblAbsence->getId(),
            'Type' => $tblType->getName(),
            'TypeExcel' => $tblType->getShortName(),
            'Division' => $tblDivision->getDisplayName(),
            'Group' => $isGroup && isset($groupPersonList[$tblPerson->getId()]) ? $groupPersonList[$tblPerson->getId()] : '',
            'Person' => $tblPerson->getLastFirstNameWithCallNameUnderline(),
            'PersonExcel' => $tblPerson->getLastFirstName(),
            'DateSpan' => $tblAbsence->getDateSpan(),
            'DateSort' => $tblAbsence->getFromDate('Y.m.d'),
            'DateFrom' => ($isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getFromDate() . '</span>' : $tblAbsence->getFromDate()),
            'DateTo' => ($isOnlineAbsence ? '<span style="color:darkorange">' . $tblAbsence->getToDate() . '</span>' : $tblAbsence->getToDate()),
            'PersonCreator' => $tblAbsence->getDisplayPersonCreator(false),
            'Status' => $tblAbsence->getStatusDisplayName(),
            'StatusExcel' => $tblAbsence->getStatusDisplayShortName(),
            'Remark' => $tblAbsence->getRemark(),
            'AbsenceType' => $tblAbsence->getTypeDisplayName(),
            'AbsenceTypeExcel' => $tblAbsence->getTypeDisplayShortName(),
            'Lessons' => $tblAbsence->getLessonStringByAbsence(),
            'IsCertificateRelevant' => $tblAbsence->getIsCertificateRelevant() ? 'ja' : 'nein'
        );

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
     * @param null $Type
     * @param null $TypeId
     *
     * @return bool|Form
     */
    public function checkFormAbsence(
        $Data,
        $Search = '',
        TblAbsence $tblAbsence = null,
        $PersonId = null,
        $DivisionId = null,
        $hasSearch = false,
        $Type = null,
        $TypeId = null
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
        } elseif ($Type) {
            // Prüfung kann erst nach dem Erstellen des Forms erfolgen
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
            $messageLesson,
            null,
            $Type,
            $TypeId
        );

        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $form->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }

        if ($Type) {
            if(!isset($Data['PersonId']) || !($tblPerson = Person::useService()->getPersonById($Data['PersonId']))) {
                $form->setError('Data[PersonId]', 'Bitte wählen Sie einen Schüler aus.');
                $error = true;
            }

            if ($tblPerson) {
                $tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson);
            }
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
        if (!$error && $tblPerson && $fromDate) {
            if (($resultList = (new Data($this->getBinding()))->getAbsenceAllBetweenByPerson($fromDate, $tblPerson, $toDate == $fromDate ? null : $toDate))) {
                foreach ($resultList as $item) {
                    // beim Bearbeiten der Fehlzeit, die zu bearbeitende Fehlzeit ignorieren
                    if ($tblAbsence && $tblAbsence->getId() == $item->getId()) {
                        continue;
                    }

                    $form->setError('Data[FromDate]', 'Es existiert bereits eine Fehlzeit im Bereich dieses Zeitraums');
//                if ($toDate) {
//                    $form->setError('Data[ToDate]', 'Es existiert bereits eine Fehlzeit im Bereich dieses Zeitraums');
//                }
                    $error = true;
                    break;
                }

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
     * @param $Data
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param int $Source
     *
     * @return false|Form
     */
    public function checkFormOnlineAbsence(
        $Data,
        TblPerson $tblPerson,
        TblDivision $tblDivision,
        int $Source
    ) {
        $error = false;
        $messageLesson = null;

        // Prüfung ob Unterrichtseinheiten ausgewählt wurden
        if (!isset($Data['IsFullDay']) && !isset($Data['UE'])) {
            $messageLesson = new Danger('Bitte wählen Sie mindestens eine Unterrichtseinheit aus.', new Exclamation());
            $error = true;
        }

        $form = OnlineAbsence::useFrontend()->formOnlineAbsence(
            $Data,
            $tblPerson->getId(),
            $tblDivision->getId(),
            $Source,
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

        if (!$error && $fromDate) {
            // prüfen, ob das fromDate größer gleich heute ist
            if ($fromDate < (new DateTime('today'))) {
                $form->setError('Data[FromDate]', 'Bitte wählen Sie heute oder ein zukünftiges Datum aus');
                $error = true;
            }

            // Prüfung ob in diesem Zeitraum bereits eine Fehlzeit existiert
            if ((new Data($this->getBinding()))->getAbsenceAllBetweenByPerson($fromDate, $tblPerson, $toDate == $fromDate ? null : $toDate)) {
                $form->setError('Data[FromDate]', 'Es existiert bereits eine Fehlzeit im Bereich dieses Zeitraums');
                $error = true;
            }
        }

        if (!$error && ($tblYear = $tblDivision->getServiceTblYear())) {
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
            return $tblSchoolType->isTechnical();
        }

        return false;
    }

    /**
     * @param $Data
     * @param TblPerson|null $tblPerson
     * @param TblDivision|null $tblDivision
     *
     * @return bool
     */
    public function createAbsence($Data, TblPerson &$tblPerson = null, TblDivision &$tblDivision = null)
    {
        if ($tblPerson == null) {
            $tblPerson = Person::useService()->getPersonById($Data['PersonId']);
        }

        if ($tblDivision == null) {
            $tblDivision = Student::useService()->getCurrentMainDivisionByPerson($tblPerson);
        }

        $tblPersonStaff = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPersonStaff = $tblPersonAllByAccount[0];
            }
        }

        if ($tblPerson && $tblDivision) {
            if (($tblAbsence = (new Data($this->getBinding()))->createAbsence(
                $tblPerson,
                $tblDivision,
                $Data['FromDate'],
                $Data['ToDate'],
                $Data['Status'],
                $Data['Remark'],
                $Data['Type'] ?? TblAbsence::VALUE_TYPE_NULL,
                isset($Data['IsCertificateRelevant']),
                // Ersteller
                $tblPersonStaff ?: null,
                // letzter Bearbeiter
                $tblPersonStaff ?: null
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
     * @param $Data
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param int $Source
     *
     * @return bool
     */
    public function createOnlineAbsence($Data, TblPerson $tblPerson, TblDivision $tblDivision, int $Source): bool
    {
        $tblPersonCreator = Account::useService()->getPersonByLogin();
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'Absence', 'DefaultStatusForNewOnlineAbsence'))) {
            $status = $tblSetting->getValue();
        } else {
            $status = TblAbsence::VALUE_STATUS_UNEXCUSED;
        }

        if (($tblAbsence = (new Data($this->getBinding()))->createAbsence(
            $tblPerson,
            $tblDivision,
            $Data['FromDate'],
            $Data['ToDate'],
            $status,
            $Data['Remark'],
            $Data['Type'] ?? TblAbsence::VALUE_TYPE_NULL,
            true,
            $tblPersonCreator ?: null,
            null,
            $Source
        ))) {
            if (isset($Data['UE'])) {
                foreach ($Data['UE'] as $lesson => $value) {
                    (new Data($this->getBinding()))->addAbsenceLesson($tblAbsence, $lesson);
                }
            }

            return  true;
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
        $tblPersonStaff = false;
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblPersonAllByAccount = Account::useService()->getPersonAllByAccount($tblAccount);
            if ($tblPersonAllByAccount) {
                $tblPersonStaff = $tblPersonAllByAccount[0];
            }
        }

        if ((new Data($this->getBinding()))->updateAbsence(
            $tblAbsence,
            $Data['FromDate'],
            $Data['ToDate'],
            $Data['Status'],
            $Data['Remark'],
            isset($Data['Type']) ? $Data['Type'] : TblAbsence::VALUE_TYPE_NULL,
            $tblPersonStaff ? $tblPersonStaff : null,
            isset($Data['IsCertificateRelevant'])
        )) {
            for ($i = 0; $i < 13; $i++) {
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

    /**
     * @param TblPerson $tblPerson
     * @param TblDivision $tblDivision
     * @param $Status
     *
     * @return bool
     */
    public function hasPersonAbsenceLessons(TblPerson $tblPerson, TblDivision $tblDivision, $Status)
    {
        return (new Data($this->getBinding()))->hasPersonAbsenceLessons($tblPerson, $tblDivision, $Status);
    }

    public function getAbsenceForExcelDownload(TblDivision $tblDivision): array
    {
        $dataList = array();
        $countList = array();
        if (($tblYear = $tblDivision->getServiceTblYear())
           && ($tblSchoolType = $tblDivision->getType())
        ) {
            $tblCompany = $tblDivision->getServiceTblCompany();
            list($startDate, $endDate) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDate && $endDate
                && ($tblAbsenceList = Absence::useService()->getAbsenceAllBetweenByDivision($startDate, $endDate, $tblDivision))
            ) {
                list($dataList, $countList) = $this->setDataForExcel($tblAbsenceList, $tblSchoolType, $tblYear, $tblCompany ?: null);
            }
        }

        return array($dataList, $countList);
    }

    private function setDataForExcel(array $tblAbsenceList, $tblSchoolType, TblYear $tblYear, ?TblCompany $tblCompany): array
    {
        $dataList = array();
        $countList = array();
        $hasSaturdayLessons = Digital::useService()->getHasSaturdayLessonsBySchoolType($tblSchoolType);

        /** @var TblAbsence $tblAbsence */
        foreach($tblAbsenceList as $tblAbsence) {
            if (($tblPerson = $tblAbsence->getServiceTblPerson())) {
                $fromDate = new DateTime($tblAbsence->getFromDate());
                $countLessons = $tblAbsence->getCountLessons();
                if ($tblAbsence->getToDate()) {
                    $toDate = new DateTime($tblAbsence->getToDate());
                    if ($toDate > $fromDate) {
                        $date = $fromDate;
                        while ($date <= $toDate) {
                            $this->setData($dataList, $countList, $date, $tblPerson, $tblAbsence->getStatusDisplayShortName(), $countLessons, $tblYear, $hasSaturdayLessons, $tblCompany);
                            $date = $date->modify('+1 day');
                        }
                    } elseif ($toDate == $fromDate) {
                        $this->setData($dataList, $countList, $fromDate, $tblPerson, $tblAbsence->getStatusDisplayShortName(), $countLessons, $tblYear, $hasSaturdayLessons, $tblCompany);
                    }
                } else {
                    $this->setData($dataList, $countList, $fromDate, $tblPerson, $tblAbsence->getStatusDisplayShortName(), $countLessons, $tblYear, $hasSaturdayLessons, $tblCompany);
                }
            }
        }

        return array($dataList, $countList);
    }

    private function setData(array &$dataList, array &$countList, DateTime $dateTime, TblPerson $tblPerson, string $status, int $countLessons,
        TblYear $tblYear, bool $hasSaturdayLessons, ?TblCompany $tblCompany)
    {
        $DayAtWeek = $dateTime->format('w');
        $month = intval($dateTime->format('m'));

        if ($hasSaturdayLessons) {
            $isWeekend = $DayAtWeek == 0;
        } else {
            $isWeekend = $DayAtWeek == 0 || $DayAtWeek == 6;
        }
        $isHoliday = Term::useService()->getHolidayByDay($tblYear, $dateTime, $tblCompany);
        if (!$isWeekend && !$isHoliday) {
            $dataList[$month][$tblPerson->getId()][$dateTime->format('d')] = $countLessons > 0 ? $countLessons : $status;
            if (isset($countList[$month][$tblPerson->getId()][$countLessons > 0 ? 'Lessons' : 'Days'][$status])) {
                $countList[$month][$tblPerson->getId()][$countLessons > 0 ? 'Lessons' : 'Days'][$status] += $countLessons > 0 ? $countLessons : 1;
            } else {
                $countList[$month][$tblPerson->getId()][$countLessons > 0 ? 'Lessons' : 'Days'][$status] = $countLessons > 0 ? $countLessons : 1;
            }
        }
    }
}
