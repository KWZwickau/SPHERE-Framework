<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use DateInterval;
use DateTime;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblFullTimeContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContentLink;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonWeek;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Setup;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Data;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;

class Service extends ServiceTabs
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
     * @param TblYear $tblYear
     *
     * @return array
     */
    public function migrateYear(TblYear $tblYear): array
    {
        return (new Data($this->getBinding()))->migrateYear($tblYear);
    }

    /**
     * @param $Data
     * @param int $lesson
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblLessonContent
     */
    public function createLessonContent($Data, int $lesson, TblDivisionCourse $tblDivisionCourse): TblLessonContent
    {
        $tblPerson = Account::useService()->getPersonByLogin();
//        $tblPerson = Person::useService()->getPersonById($Data['serviceTblPerson'])

        return (new Data($this->getBinding()))->createLessonContent(
            $Data['Date'],
            $lesson,
            $Data['Content'],
            $Data['Homework'],
            $Data['Room'],
            $tblDivisionCourse,
            $tblPerson ?: null,
            ($tblSubject = Subject::useService()->getSubjectById($Data['serviceTblSubject'])) ? $tblSubject : null,
            ($tblSubstituteSubject = Subject::useService()->getSubjectById($Data['serviceTblSubstituteSubject'])) ? $tblSubstituteSubject : null,
            isset($Data['IsCanceled'])
        );
    }

    /**
     * @param TblLessonContent $tblLessonContent
     * @param $Data
     *
     * @return bool
     */
    public function updateLessonContent(TblLessonContent $tblLessonContent, $Data): bool
    {
        $tblPerson = Account::useService()->getPersonByLogin();
//        $tblPerson = Person::useService()->getPersonById($Data['serviceTblPerson'])

        // key -1 bei 0. UE
        $lesson = $Data['Lesson'];
        if ($lesson == -1) {
            $lesson = 0;
        }

        return (new Data($this->getBinding()))->updateLessonContent(
            $tblLessonContent,
            $Data['Date'],
            $lesson,
            $Data['Content'],
            $Data['Homework'],
            $Data['Room'],
            $tblPerson ?: null,
            ($tblSubject = Subject::useService()->getSubjectById($Data['serviceTblSubject'])) ? $tblSubject : null,
            ($tblSubstituteSubject = Subject::useService()->getSubjectById($Data['serviceTblSubstituteSubject'])) ? $tblSubstituteSubject : null,
            isset($Data['IsCanceled'])
        );
    }

    /**
     * @param TblLessonContent $tblLessonContent
     *
     * @return bool
     */
    public function destroyLessonContent(TblLessonContent $tblLessonContent): bool
    {
        if (($tblLessonContentLinkList = $tblLessonContent->getLinkedLessonContentAll())) {

            $tblLessonContentLinkList[] = $tblLessonContent;
            // Verknüpfungen löschen
            $this->destroyLessonContentLinkList($tblLessonContentLinkList);

            foreach ($tblLessonContentLinkList as $tblLessonContentItem) {
                (new Data($this->getBinding()))->destroyLessonContent($tblLessonContentItem);
            }
        } else {
            (new Data($this->getBinding()))->destroyLessonContent($tblLessonContent);
        }

        return true;
    }

    /**
     * @param $Id
     *
     * @return false|TblLessonContent
     */
    public function getLessonContentById($Id)
    {
        return (new Data($this->getBinding()))->getLessonContentById($Id);
    }

    /**
     * @param DateTime $date
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDate(DateTime $date, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getLessonContentAllByDate($date, $tblDivisionCourse);
    }

    /**
     * @param DateTime $date
     * @param int|null $lesson
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByDateAndLesson(DateTime $date, ?int $lesson, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getLessonContentAllByDateAndLesson($date, $lesson, $tblDivisionCourse);
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblLessonContent|null $tblLessonContent
     *
     * @return bool|Form
     */
    public function checkFormLessonContent($Data, TblDivisionCourse $tblDivisionCourse, TblLessonContent $tblLessonContent = null)
    {
        $error = false;
        $form = Digital::useFrontend()->formLessonContent($tblDivisionCourse, $tblLessonContent ? $tblLessonContent->getId() : null);

        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            // Prüfung ob das Datum innerhalb des Schuljahres liegt.
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDateSchoolYear && $endDateSchoolYear) {
                    $date = new DateTime($Data['Date']);
                    if ($date < $startDateSchoolYear || $date > $endDateSchoolYear) {
                        $form->setError('Data[Date]', 'Das ausgewählte Datum: ' . $Data['Date'] . ' befindet sich außerhalb des Schuljahres.');
                        $error = true;
                    }
                } else {
                    $form->setError('Data[Date]', 'Das Schuljahr besitzt keinen Zeitraum');
                    $error = true;
                }
            } else {
                $form->setError('Data[Date]', 'Kein Schuljahr gefunden');
                $error = true;
            }
        }
        if (isset($Data['Lesson']) && $Data['Lesson'] == 0) {
            $form->setError('Data[Lesson]', 'Bitte geben Sie eine Unterrichtseinheit an');
            $error = true;
        }

        // nicht mehr verwenden da es als zusätzliches Fach benutzt werden soll
//        // bei einem gesetzten Vertretungsfach muss auch ein Fach ausgewählt werden
//        if (Subject::useService()->getSubjectById($Data['serviceTblSubstituteSubject'])
//            && empty($Data['serviceTblSubject'])
//        ) {
//            $form->setError('Data[serviceTblSubject]', 'Bitte geben Sie ein Fach an');
//            $error = true;
//        }

        return $error ? $form : false;
    }

    /**
     * @param DateTime $fromDate
     * @param DateTime $toDate
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblLessonContent[]
     */
    public function getLessonContentAllByBetween(DateTime $fromDate, DateTime $toDate, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getLessonContentAllByBetween($fromDate, $toDate, $tblDivisionCourse);
    }

    /**
     * @param DateTime $toDate
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function getLessonContentCanceledSubjectList(DateTime $toDate, TblDivisionCourse $tblDivisionCourse): array
    {
        $subjectCancelList = array();
        $subjectAdditionalList = array();
        if (($tblLessonContentList = (new Data($this->getBinding()))->getLessonContentCanceledAllByToDate($toDate, $tblDivisionCourse))) {
            foreach ($tblLessonContentList as $tblLessonContent) {
                if (($tblSubject = $tblLessonContent->getServiceTblSubject())) {
                    if (isset($subjectCancelList[$tblSubject->getAcronym()])) {
                        $subjectCancelList[$tblSubject->getAcronym()]++;
                    } else {
                        $subjectCancelList[$tblSubject->getAcronym()] = 1;
                    }
                }
                if (($tblSubstituteSubjectSubject = $tblLessonContent->getServiceTblSubstituteSubject())) {
                    if (isset($subjectAdditionalList[$tblSubstituteSubjectSubject->getAcronym()])) {
                        $subjectAdditionalList[$tblSubstituteSubjectSubject->getAcronym()]++;
                    } else {
                        $subjectAdditionalList[$tblSubstituteSubjectSubject->getAcronym()] = 1;
                    }
                }
            }
        }

        return array($subjectCancelList, $subjectAdditionalList);
    }

    /**
     * @param DateTime $dateTime
     * @param TblDivisionCourse $tblDivisionCourse
     * @param bool $hasEdit
     *
     * @return Panel|string
     */
    public function getCanceledSubjectOverview(DateTime $dateTime, TblDivisionCourse $tblDivisionCourse, bool $hasEdit = true)
    {
        list($fromDate, $toDate, $canceledSubjectList, $additionalSubjectList, $subjectList) = $this->getCanceledSubjectList($dateTime, $tblDivisionCourse);

        list($subjectTotalCanceledList, $subjectTotalAdditionalList) = $this->getLessonContentCanceledSubjectList($toDate, $tblDivisionCourse);

        if ($subjectList) {
            $columns = array();
            $dataList = array();
            ksort($subjectList);
            $columns['Name'] = 'Fach';
            $dataList['Canceled']['Name'] = new ToolTip('Ausgefallene Stunden ' . new InfoIcon(), "Ausgefallene Stunden der KW{$dateTime->format('W')}");
            $dataList['Additional']['Name'] = new ToolTip('Zusätzlich erteilte Stunden ' . new InfoIcon(), "Zusätzlich erteilte Stunden der KW{$dateTime->format('W')}");
            $dataList['TotalCanceled']['Name'] = new ToolTip('Absoluter Ausfall ' . new InfoIcon(),
                "Aufsummierung der ausgefallenen Stunden bis einschließlich der KW{$dateTime->format('W')}");
            $dataList['TotalAdditional']['Name'] = new ToolTip('Abs. zus. erteilte Stunden ' . new InfoIcon(),
                "Aufsummierung der zusätzlich erteilten Stunden bis einschließlich der KW{$dateTime->format('W')}");
            foreach ($subjectList as $acronym => $subject) {
                $columns[$acronym] = $acronym;
                $dataList['Canceled'][$acronym] = $canceledSubjectList[$acronym] ?? 0;
                $dataList['Additional'][$acronym] = $additionalSubjectList[$acronym] ?? 0;
                $dataList['TotalCanceled'][$acronym] = $subjectTotalCanceledList[$acronym] ?? 0;
                $dataList['TotalAdditional'][$acronym] = $subjectTotalAdditionalList[$acronym] ?? 0;
            }

            $remark = '&nbsp;';
            $checking = new Container('&nbsp;');
            if (($tblLessonWeek = Digital::useService()->getLessonWeekByDate($tblDivisionCourse, $fromDate))) {
                $remark = str_replace("\n", '<br>', $tblLessonWeek->getRemark());
                if ($tblLessonWeek->getDateDivisionTeacher()) {
                    $checking .= new Container(new Success(new Check() . ' am ' . $tblLessonWeek->getDateDivisionTeacher() . ' von '
                            . (($divisionTeacher = $tblLessonWeek->getServiceTblPersonDivisionTeacher())
                                ? $divisionTeacher->getLastName() : '') . ' für die Vollständigkeit der Angaben (Klassenlehrer) geprüft'));
                }

                if ($tblLessonWeek->getDateHeadmaster()) {
                    $checking .= new Container(new Success(new Check() . ' am ' . $tblLessonWeek->getDateHeadmaster() . ' von '
                        . (($headmaster = $tblLessonWeek->getServiceTblPersonHeadmaster())
                            ? $headmaster->getLastName() : '') . ' zur Kenntnis genommen (Schulleitung)'));
                }
            }

            return new Panel(
                'Wochenübersicht',
                (new TableData($dataList, null, $columns, false))->setHash('Week')
                    . new Bold('Wochenbemerkung:')
                    . new Container($remark)
                    . ($hasEdit
                        ? new Container((new Primary(
                            new Edit() . ' Bearbeiten',
                            ApiDigital::getEndpoint()
                        ))->ajaxPipelineOnClick(ApiDigital::pipelineOpenEditLessonWeekRemarkModal($tblDivisionCourse, $fromDate->format('d.m.Y'))))
                        . new Container($checking)
                        : ''),
                Panel::PANEL_TYPE_INFO
            );
        }

        return '';
    }

    /**
     * @param DateTime $dateTime
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return array
     */
    public function getCanceledSubjectList(DateTime $dateTime, TblDivisionCourse $tblDivisionCourse): array
    {
        $fromDate = Timetable::useService()->getStartDateOfWeek($dateTime);
        $toDate = new DateTime($fromDate->format('d.m.Y'));
        $toDate = $toDate->add(new DateInterval('P4D'));

        $canceledSubjectList = array();
        $additionalSubjectList = array();
        if (($tblLessonContentList = $this->getLessonContentAllByBetween($fromDate, $toDate, $tblDivisionCourse))) {
            foreach ($tblLessonContentList as $tblLessonContent) {
                if ($tblLessonContent->getIsCanceled() && ($tblSubject = $tblLessonContent->getServiceTblSubject())) {
                    if (isset($canceledSubjectList[$tblSubject->getAcronym()])) {
                        $canceledSubjectList[$tblSubject->getAcronym()]++;
                    } else {
                        $canceledSubjectList[$tblSubject->getAcronym()] = 1;
                    }
                }
                if (($tblSubstituteSubject = $tblLessonContent->getServiceTblSubstituteSubject())) {
                    if (isset($additionalSubjectList[$tblSubstituteSubject->getAcronym()])) {
                        $additionalSubjectList[$tblSubstituteSubject->getAcronym()]++;
                    } else {
                        $additionalSubjectList[$tblSubstituteSubject->getAcronym()] = 1;
                    }
                }
            }
        }

        $subjectList = array();
        // Falls es bereits Einträge im Klassenbuch gibt, werden diese Fächer in der Wochenübersicht angezeigt
        if (($tempList = $this->getSubjectListFromLessonContent($tblDivisionCourse))) {
            $subjectList = $tempList;
        // ansonsten die Fächer der Klasse
        } else {
            $this->setSubjectListByDivision($tblDivisionCourse, $subjectList);
        }

        return array($fromDate, $toDate, $canceledSubjectList, $additionalSubjectList, $subjectList);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblSubject[]|false
     */
    public function getSubjectListFromLessonContent(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getSubjectListFromLessonContent($tblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param array $subjectList
     */
    private function setSubjectListByDivision(TblDivisionCourse $tblDivisionCourse, array &$subjectList)
    {
        if (($tblSubjectList = DivisionCourse::useService()->getSubjectListByDivisionCourse($tblDivisionCourse, false))) {
            foreach ($tblSubjectList as $tblSubject) {
                $subjectList[$tblSubject->getAcronym()] = $tblSubject;
            }
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $dateTime
     *
     * @return false|TblLessonWeek
     */
    public function getLessonWeekByDate(TblDivisionCourse $tblDivisionCourse, DateTime $dateTime)
    {
        return (new Data($this->getBinding()))->getLessonWeekAllByDate($tblDivisionCourse, $dateTime);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param $date
     * @param $Remark
     * @param $DateDivisionTeacher
     * @param TblPerson|null $serviceTblPersonDivisionTeacher
     * @param $DateHeadmaster
     * @param TblPerson|null $serviceTblPersonHeadmaster
     *
     * @return TblLessonWeek
     */
    public function createLessonWeek(TblDivisionCourse $tblDivisionCourse, $date, $Remark, $DateDivisionTeacher,
        ?TblPerson $serviceTblPersonDivisionTeacher, $DateHeadmaster, ?TblPerson $serviceTblPersonHeadmaster
    ): TblLessonWeek {
        return (new Data($this->getBinding()))->createLessonWeek($tblDivisionCourse, $date, $Remark, $DateDivisionTeacher,
            $serviceTblPersonDivisionTeacher, $DateHeadmaster, $serviceTblPersonHeadmaster);
    }

    /**
     * @param TblLessonWeek $tblLessonWeek
     * @param $Remark
     * @param $DateDivisionTeacher
     * @param TblPerson|null $serviceTblPersonDivisionTeacher
     * @param $DateHeadmaster
     * @param TblPerson|null $serviceTblPersonHeadmaster
     *
     * @return bool
     */
    public function updateLessonWeek(
        TblLessonWeek $tblLessonWeek,
        $Remark,
        $DateDivisionTeacher,
        ?TblPerson $serviceTblPersonDivisionTeacher,
        $DateHeadmaster,
        ?TblPerson $serviceTblPersonHeadmaster
    ): bool {
        return (new Data($this->getBinding()))->updateLessonWeek($tblLessonWeek, $Remark, $DateDivisionTeacher, $serviceTblPersonDivisionTeacher,
            $DateHeadmaster, $serviceTblPersonHeadmaster);
    }

    /**
     * @param TblLessonWeek $tblLessonWeek
     * @param $Remark
     *
     * @return bool
     */
    public function updateLessonWeekRemark(
        TblLessonWeek $tblLessonWeek,
        $Remark
    ): bool {
        return (new Data($this->getBinding()))->updateLessonWeekRemark($tblLessonWeek, $Remark);
    }

    /**
     * @return string
     */
    public function getDigitalClassRegisterPanelForTeacher(): string
    {
        $resultList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())) {
            $baseRoute = (Digital::useFrontend())::BASE_ROUTE;

            $tblDivisionCourseList = array();
            $checkedDivisionCourseList = array();
            // Lehraufträge -> dann alle Schüler des Lehrauftrags -> alle Klassen, Stammgruppen und SekII-Kurse der Schüler
            if (($tblYearList = Term::useService()->getYearByNow())) {
                foreach ($tblYearList as $tblYear) {
                    if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson))) {
                        foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                            if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                                && !isset($tblDivisionCourseList[$tblDivisionCourse->getId()])
                                && !isset($checkedDivisionCourseList[$tblDivisionCourse->getId()])
                            ) {
                                // SekII-Kurse
                                if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                                    $tblDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                                    $checkedDivisionCourseList[$tblDivisionCourse->getId()] = $tblDivisionCourse;
                                } else {
                                    if (($tblDivisionCourseListFromStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse(
                                        $tblDivisionCourse
                                    ))) {
                                        foreach ($tblDivisionCourseListFromStudents as $tblDivisionCourseStudent) {
                                            if (isset($checkedDivisionCourseList[$tblDivisionCourseStudent->getId()])) {
                                                continue;
                                            }

                                            if (!isset($tblDivisionCourseList[$tblDivisionCourseStudent->getId()])) {
                                                $tblDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                                            }

                                            $checkedDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            /** @var TblDivisionCourse $tblDivisionCourse */
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                // falsch vergebener Lehrauftrag direkt an der Klasse statt am SekII-Kurs im Falle der SekII
                if ($tblDivisionCourse->getIsDivisionOrCoreGroup() && DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourse)) {
                    continue;
                // Klassentagebuch
                } elseif ($tblDivisionCourse->getIsDivisionOrCoreGroup()) {
                    $resultList[] = array(
                        'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                        'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                        'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                        'Option' => new Standard(
                            '',
                            $baseRoute . '/LessonContent',
                            new Extern(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'BasicRoute' => $baseRoute . '/Teacher'
                            ),
                            'Zum Klassenbuch wechseln'
                        )
                    );
                // Kursheft (SekII-Kurs)
                } elseif ($tblDivisionCourse->getType()->getIsCourseSystem()) {
                    $resultList[] = array(
                        'DivisionCourse' => $tblDivisionCourse->getDisplayName(),
                        'DivisionCourseType' => $tblDivisionCourse->getTypeName(),
                        'SchoolTypes' => $tblDivisionCourse->getSchoolTypeListFromStudents(true),
                        'Option' => new Standard(
                            '',
                            $baseRoute . '/CourseContent',
                            new Extern(),
                            array(
                                'DivisionCourseId' => $tblDivisionCourse->getId(),
                                'BasicRoute' => $baseRoute . '/Teacher'
                            ),
                            'Zum Kursheft wechseln'
                        )
                    );
                }
            }
        }

        if ($resultList) {
            return new Panel(
                'Digitales Klassenbuch (Fachlehrer)',
                new TableData(
                    $resultList,
                    null,
                    array(
                        'DivisionCourse' => 'Kurs',
                        'DivisionCourseType' => 'Kurs-Typ',
                        'SchoolTypes' => 'Schularten',
                        'Option' => ''
                    ),
                    array(
                        'order' => array(
                            array('0', 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 0),
                            array('orderable' => false, 'width' => '1%', 'targets' => -1),
                            array('searchable' => false, 'targets' => -1),
                        ),
                        'pageLength' => -1,
                        'paging' => false,
                        'info' => false,
                        'searching' => false,
                        'responsive' => false
                    )
                ),
                Panel::PANEL_TYPE_PRIMARY
            );
        }

        return '';
    }

    /**
     * @param TblLessonContent $tblLessonContent
     *
     * @return bool
     */
    public function getIsLessonContentEditAllowed(TblLessonContent $tblLessonContent): bool
    {
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'IsChangeLessonContentByOtherTeacherAllowed'))
            && $tblSetting->getValue()
        ) {
            return true;
        } else {
            $tblPerson = Account::useService()->getPersonByLogin();
            // Schulleitung darf immer
            if (Access::useService()->hasAuthorization('/Education/ClassRegister/Digital/Instruction/Setting')) {
                return true;
            // Klassenlehrer darf immer
            } elseif ($tblPerson
                && ($tblDivisionCourse = $tblLessonContent->getServiceTblDivisionCourse())
                && ($tblDivisionCourseMemberType = DivisionCourse::useService()->getDivisionCourseMemberTypeByIdentifier(TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER))
                && DivisionCourse::useService()->getDivisionCourseMemberByPerson($tblDivisionCourse, $tblDivisionCourseMemberType, $tblPerson)
            ) {
                return true;
            // Letzter Bearbeiter darf immer
            } else if (($tblPersonLessonContent = $tblLessonContent->getServiceTblPerson())
                && $tblPersonLessonContent->getId() == $tblPerson->getId()
            ) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $dateTime
     * @param int $lesson
     *
     * @return false|TblLessonContent
     */
    public function getTimetableFromLastLessonContent(TblDivisionCourse $tblDivisionCourse, DateTime $dateTime, int $lesson)
    {
        // kein importierter Stundenplan für den Tag vorhanden
        if (Timetable::useService()->getTimeTableNodeListBy($tblDivisionCourse, $dateTime, null)) {
            return false;
        }

        $lastDateTime = (new DateTime($dateTime->format('d.m.Y')))->sub(new DateInterval('P7D'));

        if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
            list($startDateSchoolYear,) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
            if ($startDateSchoolYear) {
                while ($lastDateTime > $startDateSchoolYear) {
                    // letzter Wochen Tag mit eingetragen Unterrichtseinheiten
                    if ($this->getLessonContentAllByDateAndLesson($lastDateTime, null, $tblDivisionCourse)) {
                        // Eintrag für die Stunde finden
                        if (($tblLessonContentList = $this->getLessonContentAllByDateAndLesson($lastDateTime, $lesson, $tblDivisionCourse))) {
                            // es darf nur ein Eintrag gefunden werden
                            if (count($tblLessonContentList) == 1) {
                                /** @var TblLessonContent $tblLessonContent */
                                $tblLessonContent = reset($tblLessonContentList);
                                // das Fach darf nicht ausgefallen sein
                                if (!$tblLessonContent->getIsCanceled()) {
                                    return $tblLessonContent;
                                }
                            }
                        }

                        return false;
                    }

                    $lastDateTime->sub(new DateInterval('P7D'));
                }
            }
        }

        return false;
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject $tblSubject
     *
     * @return string
     */
    public function getLessonContentLinkPanel(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject)
    {
        $tblDivisionCourseList = array();
        if (($tblPerson = Account::useService()->getPersonByLogin())
            && ($tblYear = $tblDivisionCourse->getServiceTblYear())
            && ($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPerson, null, $tblSubject))
        ) {
            foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                if (($tblDivisionCourseTeacher = $tblTeacherLectureship->getTblDivisionCourse())
                    && !isset($tblDivisionCourseList[$tblDivisionCourseTeacher->getId()])
                    && ($tblDivisionCourseListFromStudents = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourseTeacher))
                ) {
                    foreach ($tblDivisionCourseListFromStudents as $tblDivisionCourseStudent) {
                        if ($tblDivisionCourseStudent->getIsDivisionOrCoreGroup()
                            && !isset($tblDivisionCourseList[$tblDivisionCourseStudent->getId()])
                            && !DivisionCourse::useService()->getIsCourseSystemByStudentsInDivisionCourse($tblDivisionCourseStudent)
                        ) {
                            $tblDivisionCourseList[$tblDivisionCourseStudent->getId()] = $tblDivisionCourseStudent;
                        }
                    }
                }
            }

            $dataList = array();
            if (isset($tblDivisionCourseList[$tblDivisionCourse->getId()]) && count($tblDivisionCourseList) > 1) {
                unset($tblDivisionCourseList[$tblDivisionCourse->getId()]);
                $tblDivisionCourseList = $this->getSorter($tblDivisionCourseList)->sortObjectBy('DisplayName');
                /** @var TblDivisionCourse $item */
                foreach ($tblDivisionCourseList as $item) {
                    $dataList[] = new CheckBox('Data[Link][' . $item->getId() . ']', $item->getDisplayName(), 1);
                }
            }

            if ($dataList) {
                return new Panel(
                    'Thema/Hausaufgaben verknüpfen',
                    $dataList,
                    Panel::PANEL_TYPE_PRIMARY
                );
            }
        }

        return '';
    }

    /**
     * @param TblLessonContent $tblLessonContent
     * @param int $LinkId
     *
     * @return TblLessonContentLink
     */
    public function createLessonContentLink(TblLessonContent $tblLessonContent, int $LinkId): TblLessonContentLink
    {
        return (new Data($this->getBinding()))->createLessonContentLink($tblLessonContent, $LinkId);
    }

    /**
     * @return int
     */
    public function getNextLinkId(): int
    {
        return (new Data($this->getBinding()))->getNextLinkId();
    }

    /**
     * @param TblLessonContent $tblLessonContent
     *
     * @return false | TblLessonContent[]
     */
    public function getLessonContentLinkAllByLessonContent(TblLessonContent $tblLessonContent)
    {
        return (new Data($this->getBinding()))->getLessonContentLinkAllByLessonContent($tblLessonContent);
    }

    /**
     * @param TblLessonContent[] $tblLessonContentList
     *
     * @return bool
     */
    public function destroyLessonContentLinkList(
        array $tblLessonContentList
    ): bool {
        return (new Data($this->getBinding()))->destroyLessonContentLinkList($tblLessonContentList);
    }

    /**
     * @param $LessonContentId
     *
     * @return string
     */
    public function getLessonContentLinkedDisplayPanel($LessonContentId): string
    {
        if (($tblLessonContent = Digital::useService()->getLessonContentById($LessonContentId))
            && ($tblLessonContentLinkedList = $tblLessonContent->getLinkedLessonContentAll())
        ) {
            $panelContent = array();

            if (($tblDivisionCourse = $tblLessonContent->getServiceTblDivisionCourse())) {
                $panelContent[] = $tblDivisionCourse->getTypeName() . ' ' . $tblDivisionCourse->getDisplayName();
            }

            foreach ($tblLessonContentLinkedList as $tblLessonContentItem) {
                if (($tblDivisionCourseItem = $tblLessonContentItem->getServiceTblDivisionCourse())) {
                    $panelContent[] = $tblDivisionCourseItem->getTypeName() . ' ' . $tblDivisionCourseItem->getDisplayName();
                }
            }

            if (!empty($panelContent)) {
                sort($panelContent);
                return new Panel(
                    'Verknüpfte Thema/Hausaufgaben',
                    $panelContent,
                    Panel::PANEL_TYPE_INFO
                );
            }
        }

        return '';
    }

    /**
     * @param TblType $tblSchoolType
     *
     * @return bool
     */
    public function getHasSaturdayLessonsBySchoolType(TblType $tblSchoolType): bool
    {
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'SaturdayLessonsSchoolTypes'))
            && ($tblSetting->getValue())
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
            && isset($tblSchoolTypeAllowedList[$tblSchoolType->getId()])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblType[] $tblSchoolTypeList
     *
     * @return bool
     */
    public function getHasSaturdayLessonsBySchoolTypeList(array $tblSchoolTypeList): bool
    {
        if (($tblSetting = Consumer::useService()->getSetting('Education', 'ClassRegister', 'LessonContent', 'SaturdayLessonsSchoolTypes'))
            && ($tblSetting->getValue())
            && ($tblSchoolTypeAllowedList = Consumer::useService()->getSchoolTypeBySettingString($tblSetting->getValue()))
        ) {
            foreach ($tblSchoolTypeList as $tblSchoolType) {
                if (isset($tblSchoolTypeAllowedList[$tblSchoolType->getId()])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblFullTimeContent
     */
    public function getFullTimeContentById($Id)
    {
        return (new Data($this->getBinding()))->getFullTimeContentById($Id);
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblFullTimeContent|null $tblFullTimeContent
     *
     * @return bool|Form
     */
    public function checkFormFullTimeContent($Data, TblDivisionCourse $tblDivisionCourse, TblFullTimeContent $tblFullTimeContent = null)
    {
        $error = false;
        $form = Digital::useFrontend()->formFullTimeContent($tblDivisionCourse, $tblFullTimeContent ? $tblFullTimeContent->getId() : null);

        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        } else {
            // Prüfung, ob das Datum innerhalb des Schuljahres liegt.
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                list($startDateSchoolYear, $endDateSchoolYear) = Term::useService()->getStartDateAndEndDateOfYear($tblYear);
                if ($startDateSchoolYear && $endDateSchoolYear) {
                    $date = new DateTime($Data['FromDate']);
                    if ($date < $startDateSchoolYear || $date > $endDateSchoolYear) {
                        $form->setError('Data[FromDate]', 'Das ausgewählte Datum: ' . $Data['FromDate'] . ' befindet sich außerhalb des Schuljahres.');
                        $error = true;
                    }
                } else {
                    $form->setError('Data[FromDate]', 'Das Schuljahr besitzt keinen Zeitraum');
                    $error = true;
                }
            } else {
                $form->setError('Data[FromDate]', 'Kein Schuljahr gefunden');
                $error = true;
            }

            if ($Data['FromDate'] && $Data['ToDate']) {
                $fromDate = new DateTime($Data['FromDate']);
                $toDate = new DateTime($Data['ToDate']);

                if ($toDate < $fromDate) {
                    $form->setError('Data[ToDate]', 'Das Datum bis muss größer sein als das Datum von.');
                    $error = true;
                }
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblFullTimeContent
     */
    public function createFullTimeContent($Data, TblDivisionCourse $tblDivisionCourse): TblFullTimeContent
    {
        $tblPerson = Account::useService()->getPersonByLogin();

        return (new Data($this->getBinding()))->createFullTimeContent(
            $Data['FromDate'],
            $Data['ToDate'],
            $Data['Content'],
            $tblDivisionCourse,
            $tblPerson ?: null
        );
    }

    /**
     * @param TblFullTimeContent $tblFullTimeContent
     * @param $Data
     *
     * @return bool
     */
    public function updateFullTimeContent(TblFullTimeContent $tblFullTimeContent, $Data): bool
    {
        $tblPerson = Account::useService()->getPersonByLogin();

        return (new Data($this->getBinding()))->updateFullTimeContent(
            $tblFullTimeContent,
            $Data['FromDate'],
            $Data['ToDate'],
            $Data['Content'],
            $tblPerson ?: null,
        );
    }

    /**
     * @param TblFullTimeContent $tblFullTimeContent
     *
     * @return bool
     */
    public function destroyFullTimeContent(TblFullTimeContent $tblFullTimeContent): bool
    {
        return (new Data($this->getBinding()))->destroyFullTimeContent($tblFullTimeContent);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param DateTime $date
     *
     * @return TblFullTimeContent[]|false
     */
    public function getFullTimeContentListByDivisionCourseAndDate(TblDivisionCourse $tblDivisionCourse, DateTime $date)
    {
        return (new Data($this->getBinding()))->getFullTimeContentListByDivisionCourseAndDate($tblDivisionCourse, $date);
    }

    /**
     * @param TblSubject $tblSubject
     *
     * @return bool
     */
    public function getIsSubjectUsedInDigital(TblSubject $tblSubject): bool
    {
        return (new Data($this->getBinding()))->getIsSubjectUsedInDigital($tblSubject);
    }
}