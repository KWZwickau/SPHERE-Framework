<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Data;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetable;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacement;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableWeek;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Setup;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
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
     * @return false|TblTimetableNode[]|null
     */
    public function getTimetableNodeListByTimetable(TblTimetable $tblTimetable)
    {

        return (new Data($this->getBinding()))->getTimetableNodeListByTimetable($tblTimetable);
    }

    /**
     * @param TblTimetable $tblTimetable
     *
     * @return TblTimetableWeek[]|null
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
     * @param $Data
     * @param TblTimetable|null $tblTimetable
     *
     * @return false|Form
     */
    public function checkFormTimetable(
        $Data,
        TblTimetable $tblTimetable = null
    ) {
        $error = false;

        $form = Timetable::useFrontend()->formTimetable($tblTimetable ? $tblTimetable->getId() : null);

        if (isset($Data['Name']) && empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        }
        if (isset($Data['DateFrom']) && empty($Data['DateFrom'])) {
            $form->setError('Data[DateFrom]', 'Bitte geben Sie ein Gültig ab - Datum an');
            $error = true;
        }
        if (isset($Data['DateTo']) && empty($Data['DateTo'])) {
            $form->setError('Data[DateTo]', 'Bitte geben Sie ein Gültig bis - Datum an');
            $error = true;
        }

        return $error ? $form : false;
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
     * [tblSubstituteSubject]
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
     * @param TblTimetable $tblTimeTable
     * @param string       $Name
     * @param string       $Description
     * @param DateTime     $DateFrom
     * @param DateTime     $DateTo
     *
     * @return TblTimetable|null
     */
    public function updateTimetable(TblTimetable $tblTimeTable, string $Name, string $Description, DateTime $DateFrom, DateTime $DateTo): ?TblTimetable
    {

        return (new Data($this->getBinding()))->updateTimetable($tblTimeTable, $Name, $Description, $DateFrom, $DateTo);
    }

    /**
     * @param TblTimetable $tblTimetableNew
     * @param TblTimetable $tblTimetableOld
     */
    public function copyContentFromOldTimetable(TblTimetable $tblTimetableNew, TblTimetable $tblTimetableOld)
    {
        $createTimetableNodeList = array();
        if (($tblTimetableNodeList = $this->getTimetableNodeListByTimetable($tblTimetableOld))) {
            foreach ($tblTimetableNodeList as $tblTimetableNode) {
                if (($tblDivisionCourse = $tblTimetableNode->getServiceTblCourse()) && ($tblSubject = $tblTimetableNode->getServiceTblSubject())) {
                    $entity = new TblTimetableNode();
                    $entity->setTblTimetable($tblTimetableNew);
                    $entity->setHour($tblTimetableNode->getHour());
                    $entity->setDay($tblTimetableNode->getDay());
                    $entity->setWeek($tblTimetableNode->getWeek());
                    $entity->setRoom($tblTimetableNode->getRoom());
                    $entity->setSubjectGroup($tblTimetableNode->getSubjectGroup());
                    $entity->setLevel($tblTimetableNode->getLevel());
                    $entity->setServiceTblCourse($tblDivisionCourse);
                    $entity->setServiceTblSubject($tblSubject);
                    if (($tblPerson = $tblTimetableNode->getServiceTblPerson())) {
                        $entity->setServiceTblPerson($tblPerson);
                    }

                    $createTimetableNodeList[] = $entity;
                }
            }
        }
        $createTimetableWeekList = array();
        if (($tblTimetableWeekList = $this->getTimetableWeekListByTimetable($tblTimetableOld))) {
            foreach ($tblTimetableWeekList as $tblTimetableWeek) {
                $entity = new TblTimetableWeek();
                $entity->setTblTimetable($tblTimetableNew);
                $entity->setNumber($tblTimetableWeek->getNumber());
                $entity->setWeek($tblTimetableWeek->getWeek());
                $entity->setDate($tblTimetableWeek->getDate(true));

                $createTimetableWeekList[] = $entity;
            }
        }

        if (!empty($createTimetableNodeList)) {
            $this->createEntityListBulk($createTimetableNodeList);
        }
        if (!empty($createTimetableWeekList)) {
            $this->createEntityListBulk($createTimetableWeekList);
        }
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
//        $year = $dateTime->format('Y');
//        $currentWeek = (int)$dateTime->format('W');
//        $week = str_pad($currentWeek, 2, '0', STR_PAD_LEFT);
        return new DateTime(date('d.m.Y', strtotime('monday this week', strtotime($dateTime->format('d.m.Y')))));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $dateTime
     * @param ?Int $lesson
     *
     * @return false|TblTimetableNode[]
     */
    public function getTimeTableNodeListBy(TblDivisionCourse $tblDivisionCourse, DateTime $dateTime, ?Int $lesson)
    {
        $day = (int) $dateTime->format('w');

        // Startdatum der Woche ermitteln, wird für Stundenplan mit Wochen abhängig benötigt
        $startDateOfWeek = $this->getStartDateOfWeek($dateTime);

        if (($tblTimeTableList = $this->getTimetableListByDateTime($dateTime))) {
            // Suche mit aktueller Person, erstmal nicht mehr verwenden da jetzt alle Fächer aus dem Stundenplan zu der Stunde angezeigt werden
//            $tblPerson = Account::useService()->getPersonByLogin();
//            if (($tblTimetableNodeList = $this->searchTimeTableNode($tblTimeTableList, $tblDivisionCourse, $day, $lesson, $startDateOfWeek, $tblPerson ?: null))) {
//                return $tblTimetableNodeList;
//            }

            // Suche ohne aktuelle Person als Fallback
            return $this->searchTimeTableNode($tblTimeTableList, $tblDivisionCourse, $day, $lesson, $startDateOfWeek, null);
        }

        return false;
    }

    /**
     * @param array $tblTimeTableList
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $day
     * @param int|null $lesson
     * @param DateTime $startDateOfWeek
     * @param TblPerson|null $tblPerson
     *
     * @return false|TblTimetableNode[]
     */
    private function searchTimeTableNode(array $tblTimeTableList, TblDivisionCourse $tblDivisionCourse, $day, ?int $lesson, DateTime $startDateOfWeek, ?TblPerson $tblPerson)
    {
        foreach ($tblTimeTableList as $tblTimetable) {
            if (($tblTimeTableNodeList = (new Data($this->getBinding()))->getTimetableNodeListBy($tblTimetable, $tblDivisionCourse, $day, $lesson, $tblPerson))) {
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

                if (!empty($resultList)) {
                    return $resultList;
                }
            }
        }

        return false;
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param TblDivisionCourse $tblDivisionCourse
     * @param int $day
     * @param int|null $lesson
     *
     * @return false|TblTimetableNode[]
     */
    public function getTimetableNodeListByTimetableAndDivisionCourseAndDay(
        TblTimetable $tblTimetable, TblDivisionCourse $tblDivisionCourse, int $day, ?int $lesson = null
    ) {
        return (new Data($this->getBinding()))->getTimetableNodeListBy($tblTimetable, $tblDivisionCourse, $day, $lesson, null);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblTimetableNode[]
     */
    public function getTimetableNodeListByTimetableAndDivisionCourse(TblTimetable $tblTimetable, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getTimetableNodeListByTimetableAndDivisionCourse($tblTimetable, $tblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $dateTime
     * @param Int $lesson
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentListFromTimeTableNodeWithReplacementBy(TblDivisionCourse $tblDivisionCourse, DateTime $dateTime, Int $lesson)
    {
        $resultList = array();

        // Suche mit Person, erstmal nicht mehr verwenden da jetzt alle Fächer aus dem Stundenplan zu der Stunde angezeigt werden
//        $tblPerson = Account::useService()->getPersonByLogin();
//        if (!($replacementList = $this->getTimetableReplacementByTime($dateTime, $tblPerson ?: null, $tblDivisionCourse, $lesson))) {
            // Suche ohne aktuelle Person als Fallback
        $replacementList = $this->getTimetableReplacementByTime($dateTime, null, $tblDivisionCourse, $lesson);
//        }

        if ($replacementList) {
            $subjectList = array();
            // Vertretungsplan gefunden
            foreach ($replacementList as $tblTimetableReplacement) {
                $tblLessonContent = new TblLessonContent();

                if ($tblTimetableReplacement->getIsCanceled() && !$tblTimetableReplacement->getServiceTblSubject()) {
                    $tblLessonContent->setServiceTblSubject($tblTimetableReplacement->getServiceTblSubstituteSubject() ?: null);
                    $tblLessonContent->setServiceTblSubstituteSubject(null);
                } else {
                    $tblLessonContent->setServiceTblSubject($tblTimetableReplacement->getServiceTblSubject() ?: null);
                    $tblLessonContent->setServiceTblSubstituteSubject($tblTimetableReplacement->getServiceTblSubstituteSubject() ?: null);
                }

                $tblLessonContent->setRoom($tblTimetableReplacement->getRoom());
                $tblLessonContent->setIsCanceled($tblTimetableReplacement->getIsCanceled());

                $resultList[] = $tblLessonContent;

                if ($tblTimetableReplacement->getServiceTblSubject()) {
                    $subjectList[$tblTimetableReplacement->getServiceTblSubject()->getId()] = true;
                }
                if ($tblTimetableReplacement->getServiceTblSubstituteSubject()) {
                    $subjectList[$tblTimetableReplacement->getServiceTblSubstituteSubject()->getId()] = true;
                }
            }

            // es kann trotz einem Vertretungsfall oder Ausfall noch weitere Fächer im normalen Stundenplan geben
            if (($tblTimeTableNodeList = $this->getTimeTableNodeListBy($tblDivisionCourse, $dateTime, $lesson))
                && count($tblTimeTableNodeList) > count($replacementList)
            ) {
                foreach ($tblTimeTableNodeList as $tblTimetableNode) {
                    if ($tblTimetableNode->getServiceTblSubject() && !isset($subjectList[$tblTimetableNode->getServiceTblSubject()->getId()])) {
                        $tblLessonContent = new TblLessonContent();
                        $tblLessonContent->setServiceTblSubject($tblTimetableNode->getServiceTblSubject() ?: null);
                        $tblLessonContent->setRoom($tblTimetableNode->getRoom());
                        $resultList[] = $tblLessonContent;
                    }
                }
            }
        } else {
            // kein Vertretungsplan -> normaler Stundenplan
            if (($tblTimeTableNodeList = $this->getTimeTableNodeListBy($tblDivisionCourse, $dateTime, $lesson))) {
                foreach ($tblTimeTableNodeList as $tblTimetableNode) {
                    $tblLessonContent = new TblLessonContent();
                    $tblLessonContent->setServiceTblSubject($tblTimetableNode->getServiceTblSubject() ?: null);
                    $tblLessonContent->setRoom($tblTimetableNode->getRoom());
                    $resultList[] = $tblLessonContent;
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @return string
     */
    public function getTimetablePanelForTeacher()
    {
        $dateTime = new DateTime('today');
        $day = (int) $dateTime->format('w');
        $startDateOfWeek = $this->getStartDateOfWeek($dateTime);
        $tblPerson = Account::useService()->getPersonByLogin();

        $resultList = array();
        if ($tblPerson && ($tblTimeTableList = $this->getTimetableListByDateTime($dateTime))) {
            foreach ($tblTimeTableList as $tblTimetable) {
                if (($tblTimeTableNodeList = (new Data($this->getBinding()))->getTimetableNodeListByDayAndPerson($tblTimetable, $day, $tblPerson))) {
                    foreach ($tblTimeTableNodeList as $tblTimeTableNode) {
                        // aus dem Vertretungsplan wird ermittelt, ob die Stunde ausfällt
                        $isCanceled = false;
                        if (($tblDivisionCourseTemp = $tblTimeTableNode->getServiceTblCourse())
                            && ($tblTimetableReplacementList = $this->getTimetableReplacementByTime($dateTime, null, $tblDivisionCourseTemp, $tblTimeTableNode->getHour()))
                        ) {
                            foreach ($tblTimetableReplacementList as $tblTimetableReplacement) {
                                if ($tblTimeTableNode->getServiceTblSubject()
                                    && (($tblTimetableReplacement->getServiceTblSubject()
                                            && $tblTimeTableNode->getServiceTblSubject()->getId() == $tblTimetableReplacement->getServiceTblSubject()->getId())
                                    || $tblTimetableReplacement->getIsCanceled())
                                ) {
                                    $isCanceled = true;
                                }
                            }
                        }

                        // nur Einträge hinzufügen, welche nicht ausgefallen sind
                        if (!$isCanceled) {
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
                }

                // nur aktuellen Stundenplan-Import verwenden
                if ($resultList) {
                    break;
                }
            }
        }

        // Vertetungsplan -> alle Vertretungen für den Lehrer hinzufügen
        if ($tblPerson && ($tblTimetableReplacementList = $this->getTimetableReplacementByTime($dateTime, $tblPerson))) {
            foreach ($tblTimetableReplacementList as $tblTimetableReplacement) {
                $tblLessonContent = new TblLessonContent();
                $tblLessonContent->setServiceTblSubject($tblTimetableReplacement->getServiceTblSubject() ?: null);
                $tblLessonContent->setServiceTblSubstituteSubject($tblTimetableReplacement->getServiceTblSubstituteSubject() ?: null);
                $tblLessonContent->setRoom($tblTimetableReplacement->getRoom());
                $tblLessonContent->setIsCanceled($tblTimetableReplacement->getIsCanceled() || $tblTimetableReplacement->getServiceTblSubstituteSubject());

                if (($tblTimetableReplacement->getServiceTblSubstituteSubject())) {
                    // reine ausgefallene Fächer ohne Vertretung nicht anzeigen
                    if (!$tblTimetableReplacement->getServiceTblSubject() && $tblTimetableReplacement->getIsCanceled()) {
                        continue;
                    }

                    $item = new TblTimetableNode();
                    $item->setServiceTblCourse($tblTimetableReplacement->getServiceTblCourse() ?: null);
                    $item->setServiceTblSubject($tblTimetableReplacement->getServiceTblSubstituteSubject() ?: null);
                    $item->setRoom($tblTimetableReplacement->getRoom());
                    $item->setHour($tblTimetableReplacement->getHour());

                    $resultList[] = $item;
                }
            }
        }

        if ($resultList) {
            $dataList = array();
            $baseRoute = (Digital::useFrontend())::BASE_ROUTE;
            foreach ($resultList as $item) {
                /** @var TblDivisionCourse $tblDivisionCourse */
                if (($tblDivisionCourse = $item->getServiceTblCourse()) && ($tblSubject = $item->getServiceTblSubject())) {
                    if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                        $option = new Standard(
                            '',
                            $baseRoute . '/CourseContent',
                            new Extern(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'BasicRoute' => $baseRoute . '/Teacher'
                            ),
                            'Zum Kursheft wechseln'
                        );
                    } else {
                        $option = new Standard(
                            '',
                            $baseRoute . '/LessonContent',
                            new Extern(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'BasicRoute' => $baseRoute . '/Teacher'
                            ),
                            'Zum Klassenbuch wechseln'
                        );
                    }

                    $dataList[] = array(
                        'Lesson' => $item->getHour(),
                        'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
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
                    'DivisionCourse' => 'Kurs',
                    'Subject' => 'Fach',
                    'Room' => 'Raum',
                    'Option' => ''
                ),
                array(
                    'order' => array(
                        array('0', 'asc'),
                        array('1', 'asc'),
                    ),
                    'columnDefs' => array(
                        array('type' => 'natural', 'targets' => 1),
                        array('orderable' => false, 'width' => '1%', 'targets' => -1)
                    ),
                    'pageLength' => -1,
                    'paging' => false,
                    'info' => false,
                    'searching' => false,
                    'responsive' => false
                )),
                Panel::PANEL_TYPE_PRIMARY
            );

        }

        return '';
    }

    /**
     * @param $RemoveList
     *
     * @return bool
     */
    public function destroyTimetableReplacementBulk($RemoveList): bool
    {
        return (new Data($this->getBinding()))->destroyTimetableReplacementBulk($RemoveList);
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Day
     * @param $Data
     *
     * @return bool
     */
    public function updateTimetableDay(TblTimetable $tblTimetable, TblDivisionCourse $tblDivisionCourse, $Day, $Data): bool
    {
        if (($tblTimetableNodeList = $this->getTimetableNodeListByTimetableAndDivisionCourseAndDay($tblTimetable, $tblDivisionCourse, $Day))) {
            $deleteBulkList = array();
            foreach ($tblTimetableNodeList as $tblTimetableNode) {
                $deleteBulkList[] = $tblTimetableNode;
            }
            $this->deleteEntityListBulk($deleteBulkList);
        }

        if ($Data) {
            $createBulkList = array();
            foreach ($Data as $index => $list) {
                if (isset($list['serviceTblSubject']) && ($tblSubject = Subject::useService()->getSubjectById($list['serviceTblSubject']))) {
                    $entity = new TblTimetableNode();
                    $entity->setTblTimetable($tblTimetable);
                    $entity->setServiceTblCourse($tblDivisionCourse);
                    $entity->setDay($Day);
                    $entity->setHour(intval($index / 100));
                    $entity->setSubjectGroup('');
                    $entity->setLevel('');

                    $entity->setServiceTblSubject($tblSubject);
                    if (isset($list['serviceTblPerson']) && ($tblPerson = Person::useService()->getPersonById($list['serviceTblPerson']))) {
                        $entity->setServiceTblPerson($tblPerson);
                    }
                    $entity->setRoom($list['Room'] ?? '');
                    $entity->setWeek($list['Week'] ?? '');

                    $createBulkList[] = $entity;
                }
            }

            if (!empty($createBulkList)) {
                $this->createEntityListBulk($createBulkList);
            }
        }

        return true;
    }

    /**
     * @param TblTimetable $tblTimetable
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $Data
     *
     * @return bool
     */
    public function updateTimetableCourseSystem(TblTimetable $tblTimetable, TblDivisionCourse $tblDivisionCourse, $Data): bool
    {
        if (($tblTimetableNodeList = $this->getTimetableNodeListByTimetableAndDivisionCourse($tblTimetable, $tblDivisionCourse))) {
            $deleteBulkList = array();
            foreach ($tblTimetableNodeList as $tblTimetableNode) {
                $deleteBulkList[] = $tblTimetableNode;
            }
            $this->deleteEntityListBulk($deleteBulkList);
        }

        if ($Data && ($tblSubject = $tblDivisionCourse->getServiceTblSubject())) {
            $createBulkList = array();
            foreach ($Data as $list) {
                if (isset($list['Day']) && $list['Day'] > 0) {
                    $entity = new TblTimetableNode();
                    $entity->setTblTimetable($tblTimetable);
                    $entity->setServiceTblCourse($tblDivisionCourse);
                    $entity->setServiceTblSubject($tblSubject);
                    $entity->setSubjectGroup('');
                    $entity->setLevel('');

                    $entity->setDay($list['Day'] ?? 0);
                    $entity->setHour($list['Hour'] ?? 0);
                    if (isset($list['serviceTblPerson']) && ($tblPerson = Person::useService()->getPersonById($list['serviceTblPerson']))) {
                        $entity->setServiceTblPerson($tblPerson);
                    }
                    $entity->setRoom($list['Room'] ?? '');
                    $entity->setWeek($list['Week'] ?? '');

                    $createBulkList[] = $entity;
                }
            }

            if (!empty($createBulkList)) {
                $this->createEntityListBulk($createBulkList);
            }
        }

        return true;
    }

    /**
     * @param IFormInterface $form
     * @param TblTimetable $tblTimetable
     * @param $Data
     *
     * @return IFormInterface|string
     */
    public function updateTimetableWeek(
        IFormInterface $form,
        TblTimetable $tblTimetable,
        $Data
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Data) {
            return $form;
        }

        if (($tblTimetableWeekList = $this->getTimetableWeekListByTimetable($tblTimetable))) {
            $deleteBulkList = array();
            foreach ($tblTimetableWeekList as $tblTimetableWeek) {
                $deleteBulkList[] = $tblTimetableWeek;
            }
            $this->deleteEntityListBulk($deleteBulkList);
        }

        if ($Data) {
            $createBulkList = array();
            foreach ($Data as $date => $week) {
                if ($week) {
                    $entity = new TblTimetableWeek();
                    $entity->setTblTimetable($tblTimetable);
                    $entity->setDate(new DateTime($date));
                    $entity->setWeek(strtoupper($week));
                    $entity->setNumber('');

                    $createBulkList[] = $entity;
                }
            }

            if (!empty($createBulkList)) {
                $this->createEntityListBulk($createBulkList);
            }
        }

        return new Success('Die Wochen wurden erfolgreich gespeichert')
            . new Redirect('/Education/ClassRegister/Digital/Timetable', Redirect::TIMEOUT_SUCCESS);
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function createEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->createEntityListBulk($tblEntityList);
    }

    /**
     * @param array $tblEntityList
     *
     * @return bool
     */
    public function deleteEntityListBulk(array $tblEntityList): bool
    {
        return (new Data($this->getBinding()))->deleteEntityListBulk($tblEntityList);
    }
}
