<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Data;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetable;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacement;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableWeek;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Setup;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Education\ClassRegister\Timetable
 */
class Service extends AbstractService
{

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
     * @return TblTimetable|null
     * @throws \Exception
     */
    public function getTimetableById($Id)
    {

        return (new Data($this->getBinding()))->getTimetableById($Id);
    }

    /**
     * @return TblTimetable[]|null
     */
    public function getTimetableAll()
    {

        return (new Data($this->getBinding()))->getTimetableAll();
    }

    /**
     * @param string $Name
     * @param DateTime $DateFrom
     * @param DateTime $DateTo
     *
     * @return TblTimetable|null
     */
    public function getTimetableByNameAndTime(string $Name, DateTime $DateFrom, DateTime $DateTo)
    {
        return (new Data($this->getBinding()))->getTimetableByNameAndTime($Name, $DateFrom, $DateTo);
    }

    /**
     * @param DateTime $Date
     *
     * @return TblTimetable[]|false
     */
    public function getTimetableListByDateTime(DateTime $Date)
    {
        return (new Data($this->getBinding()))->getTimetableListByDateTime($Date);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return mixed
     */
    public function getTimetableNodeListByTimetable(TblTimetable $tblTimetable)
    {

        return (new Data($this->getBinding()))->getTimetableNodeListByTimetable($tblTimetable);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return mixed
     */
    public function getTimetableWeekListByTimetable(TblTimetable $tblTimetable)
    {

        return (new Data($this->getBinding()))->getTimetableWeekListByTimetable($tblTimetable);
    }

    /**
     * @param DateTime $Date
     * @param $tblPerson
     * @param $tblCourse
     * @param $Hour
     * @return TblTimetableReplacement[]|null
     */
    public function getTimetableReplacementByTime(DateTime $Date, $tblPerson = null, $tblCourse = null, $Hour = null)
    {

        return (new Data($this->getBinding()))->getTimetableReplacementByTime($Date, $tblPerson, $tblCourse, $Hour);
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     *
     * @return TblTimetableReplacement[]|bool
     */
    public function getTimetableReplacementByDate(DateTime $fromDate, DateTime $toDate)
    {

        return (new Data($this->getBinding()))->getTimetableReplacementByDate($fromDate, $toDate);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param DateTime $DateFrom
     * @param DateTime $DateTo
     * @return TblTimetable|null
     */
    public function createTimetable(string $Name, string $Description, DateTime $DateFrom, DateTime $DateTo): ?TblTimetable
    {

        return (new Data($this->getBinding()))->createTimetable($Name, $Description, $DateFrom, $DateTo);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param array $ImportList
     * required ArrayKeys
     * [Hour]
     * [Day]
     * [Week]
     * [Room]
     * [SubjectGroup]
     * [Level]
     * [tblCourse]
     * [tblSubject]
     * [tblPerson]
     *
     * @return bool
     */
    public function createTimetableNodeBulk(TblTimetable $tblTimetable, $ImportList)
    {

        return (new Data($this->getBinding()))->createTimetableNodeBulk($tblTimetable, $ImportList);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param array $ImportList
     * required ArrayKeys
     * [number]
     * [week]
     * [date]
     *
     * @return bool
     */
    public function createTimetableWeekBulk(TblTimetable $tblTimetable, $ImportList)
    {

        return (new Data($this->getBinding()))->createTimetableWeekBulk($tblTimetable, $ImportList);
    }

    /**
     * @param array $ImportList
     * required ArrayKeys
     * [hour]
     * [room]
     * [subjectGroup]
     * [Date]
     * [IsCanceled]
     * [tblSubject]
     * [tblCourse]
     * [tblPerson]
     *
     * @return bool
     */
    public function createTimetableReplacementBulk($ImportList)
    {

        return (new Data($this->getBinding()))->createTimetableReplacementBulk($ImportList);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @return bool
     */
    public function removeTimetable(TblTimetable $tblTimetable)
    {

        if(($tblTimetableNodeList = $this->getTimetableNodeListByTimetable($tblTimetable))){
            (new Data($this->getBinding()))->removeTimetableNodeList($tblTimetableNodeList);
        }
        if(($tblTimetableWeekList = $this->getTimetableWeekListByTimetable($tblTimetable))){
            (new Data($this->getBinding()))->removeTimetableWeekList($tblTimetableWeekList);
        }
        return (new Data($this->getBinding()))->removeTimetable($tblTimetable);
    }

    /**
     * @return bool
     */
    public function destroyTimetableAllBulk(): bool
    {

        return (new Data($this->getBinding()))->destroyTimetableAllBulk();
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param string $week
     * @param DateTime $dateTime
     *
     * @return false|TblTimetableWeek
     */
    public function getTimetableWeekByTimeTableAndWeekAndDate(TblTimetable $tblTimetable, string $week, DateTime $dateTime)
    {
        return (new Data($this->getBinding()))->getTimetableWeekByTimeTableAndWeekAndDate($tblTimetable, $week, $dateTime);
    }

    /**
     * @param DateTime $dateTime
     *
     * @return DateTime
     */
    public function getStartDateOfWeek(DateTime $dateTime): DateTime
    {
        $year = $dateTime->format('Y');
        $currentWeek = (int)$dateTime->format('W');
        $week = str_pad($currentWeek, 2, '0', STR_PAD_LEFT);
        return new DateTime(date('d.m.Y', strtotime("$year-W{$week}")));
    }

    /**
     * @param TblDivision $tblDivision
     * @param DateTime $dateTime
     * @param Int $lesson
     *
     * @return false|TblTimetableNode
     */
    public function getTimeTableNodeBy(TblDivision $tblDivision, DateTime $dateTime, Int $lesson)
    {
        $day = (int) $dateTime->format('w');
        $tblPerson = Account::useService()->getPersonByLogin();

        // Startdatum der Woche ermitteln, wird für Stundenplan mit Wochen abhängig benötigt
        $startDateOfWeek = $this->getStartDateOfWeek($dateTime);

        if (($tblTimeTableList = $this->getTimetableListByDateTime($dateTime))) {
            // Suche mit aktueller Person
            if (($result = $this->searchTimeTableNode($tblTimeTableList, $tblDivision, $day, $lesson, $startDateOfWeek, $tblPerson))) {
                return $result;
            }

            // Suche ohne aktuelle Person als Fallback
            return $this->searchTimeTableNode($tblTimeTableList, $tblDivision, $day, $lesson, $startDateOfWeek, null);
        }

        return false;
    }

    /**
     * @param array $tblTimeTableList
     * @param TblDivision $tblDivision
     * @param $day
     * @param $lesson
     * @param DateTime $startDateOfWeek
     * @param TblPerson|null $tblPerson
     *
     * @return false|TblTimetableNode
     */
    private function searchTimeTableNode(array $tblTimeTableList, TblDivision $tblDivision, $day, $lesson, DateTime $startDateOfWeek, ?TblPerson $tblPerson)
    {
        foreach ($tblTimeTableList as $tblTimetable) {
            if (($tblTimeTableNodeList = (new Data($this->getBinding()))->getTimetableNodeListBy($tblTimetable, $tblDivision, $day, $lesson, $tblPerson))) {
                $resultList = array();
                foreach ($tblTimeTableNodeList as $tblTimeTableNode) {
                    // Woche prüfen
                    if ($tblTimeTableNode->getWeek()) {
                        if ($this->getTimetableWeekByTimeTableAndWeekAndDate($tblTimetable, $tblTimeTableNode->getWeek(), $startDateOfWeek)) {
                            $resultList[] = $tblTimeTableNode;
                        }
                    } else {
                        $resultList[] = $tblTimeTableNode;
                    }
                }

                // nur bei einem gültigem Treffer das Fach und den Raum vorsetzen
                if (count($resultList) == 1) {
                    return reset($resultList);
                }
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return string
     */
    public function getTimetablePanelForTeacher(TblPerson $tblPerson)
    {
        $dateTime = new DateTime('today');
        $day = (int) $dateTime->format('w');
        $startDateOfWeek = $this->getStartDateOfWeek($dateTime);
        $tblPerson = Account::useService()->getPersonByLogin();

        $resultList = array();
        if (($tblTimeTableList = $this->getTimetableListByDateTime($dateTime))) {
            foreach ($tblTimeTableList as $tblTimetable) {
                if (($tblTimeTableNodeList = (new Data($this->getBinding()))->getTimetableNodeListByDayAndPerson($tblTimetable, $day, $tblPerson))) {
                    foreach ($tblTimeTableNodeList as $tblTimeTableNode) {
                        // Woche prüfen
                        if ($tblTimeTableNode->getWeek()) {
                            if ($this->getTimetableWeekByTimeTableAndWeekAndDate($tblTimetable, $tblTimeTableNode->getWeek(), $startDateOfWeek)) {
                                $resultList[] = $tblTimeTableNode;
                            }
                        } else {
                            $resultList[] = $tblTimeTableNode;
                        }
                    }
                }

                // nur aktuellen Stundenplan-Import verwenden
                if ($resultList) {
                    break;
                }
            }
        }

        if ($resultList) {
            $dataList = array();
            foreach ($resultList as $item) {
                if (($tblDivision = $item->getServiceTblCourse()) && ($tblSubject = $item->getServiceTblSubject())) {
                    // prüfen ob der Lehrer einen Lehrauftrag hat
                    $option = '';
                    if (($tblDivisionSubjectList = Division::useService()->getDivisionSubjectBySubjectAndDivision($tblSubject, $tblDivision))) {
                        foreach ($tblDivisionSubjectList as $tblDivisionSubject) {
                            if (Division::useService()->existsSubjectTeacher($tblPerson, $tblDivisionSubject)) {
                                $option = new Standard(
                                    '',
                                    '/Education/ClassRegister/Digital/LessonContent',
                                    new Extern(),
                                    array(
                                        'DivisionId' => $tblDivision->getId(),

                                    ),
                                    'Zum Klassenbuch wechseln'
                                );

                                break;
                            }
                        }
                    }

                    $dataList[] = array(
                        'Lesson' => $item->getHour(),
                        'Division' => $tblDivision->getDisplayName(),
                        'Subject' => $tblSubject->getDisplayName(),
                        'Room' => $item->getRoom(),
                        'Option' => $option
                    );
                }
            }

            $dayName = array(
                '0' => 'Sonntag',
                '1' => 'Montag',
                '2' => 'Dienstag',
                '3' => 'Mittwoch',
                '4' => 'Donnerstag',
                '5' => 'Freitag',
                '6' => 'Samstag',
            );
            $dayAtWeek = $dateTime->format('w');

            return new Panel(
                'Stundenplan am ' . $dayName[$dayAtWeek] . ', den ' . $dateTime->format('d.m.Y'),
                new TableData($dataList, null, array(
                    'Lesson' => 'UE',
                    'Division' => 'Klasse',
                    'Subject' => 'Fach',
                    'Room' => 'Raum',
                    'Option' => ''
                ), null),
                Panel::PANEL_TYPE_PRIMARY
            );

        }

        return '';
    }

    /**
     * @return bool
     */
    public function destroyTimetableReplacementBulk($RemoveList): bool
    {

        return (new Data($this->getBinding()))->destroyTimetableReplacementBulk($RemoveList);
    }
}
