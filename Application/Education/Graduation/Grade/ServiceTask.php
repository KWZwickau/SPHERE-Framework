<?php

namespace SPHERE\Application\Education\Graduation\Grade;

use DateInterval;
use DateTime;
use SPHERE\Application\Education\Graduation\Grade\Service\Data;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblScoreType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskCourseLink;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGrade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTaskGradeTypeLink;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Extern;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Warning;

abstract class ServiceTask extends ServiceStudentOverview
{
    /**
     * @param TblYear $tblYear
     *
     * @return float
     */
    public function migrateTasks(TblYear $tblYear): float
    {
        return (new Data($this->getBinding()))->migrateTasks($tblYear);
    }

    /**
     * @param $id
     *
     * @return false|TblTask
     */
    public function getTaskById($id)
    {
        return (new Data($this->getBinding()))->getTaskById($id);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblTask[]
     */
    public function getTaskListByYear(TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getTaskListByYear($tblYear);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblTask[]
     */
    public function getBehaviorTaskListByYear(TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getBehaviorTaskListByYear($tblYear);
    }

    /**
     * @param TblYear $tblYear
     *
     * @return false|TblTask[]
     */
    public function getAppointedDateTaskListByYear(TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getAppointedDateTaskListByYear($tblYear);
    }

    /**
     * @param TblTask $tblTask
     *
     * @return TblGradeType[]|false
     */
    public function getGradeTypeListByTask(TblTask $tblTask)
    {
        return (new Data($this->getBinding()))->getGradeTypeListByTask($tblTask);
    }

    /**
     * @param TblTask $tblTask
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblTaskGradeTypeLink
     */
    public function getTaskGradeTypeLinkBy(TblTask $tblTask, TblGradeType $tblGradeType)
    {
        return (new Data($this->getBinding()))->getTaskGradeTypeLinkBy($tblTask, $tblGradeType);
    }

    /**
     * @param TblTask $tblTask
     *
     * @return TblDivisionCourse[]|false
     */
    public function getDivisionCourseListByTask(TblTask $tblTask)
    {
        return (new Data($this->getBinding()))->getDivisionCourseListByTask($tblTask);
    }

    /**
     * @param TblTask $tblTask
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return false|TblTaskCourseLink
     */
    public function getTaskCourseLinkBy(TblTask $tblTask, TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getTaskCourseLinkBy($tblTask, $tblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblTask[]|false
     */
    public function getTaskListByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getTaskListByDivisionCourse($tblDivisionCourse);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblTask[]|false
     */
    public function getBehaviorTaskListByDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        return (new Data($this->getBinding()))->getBehaviorTaskListByDivisionCourse($tblDivisionCourse);
    }

    /**
     * @param TblTask $tblTask
     *
     * @return false|TblTaskGrade[]
     */
    public function getTaskGradeListByTask(TblTask $tblTask)
    {
        return (new Data($this->getBinding()))->getTaskGradeListByTask($tblTask);
    }

    /**
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function getHasTaskGradesByTask(TblTask $tblTask): bool
    {
        return (new Data($this->getBinding()))->getHasTaskGradesByTask($tblTask);
    }

    /**
     * @param TblTask $tblTask
     * @param TblPerson $tblPerson
     * @param TblSubject|null $tblSubject
     *
     * @return false|TblTaskGrade[]
     */
    public function getTaskGradeListByTaskAndPerson(TblTask $tblTask, TblPerson $tblPerson, ?TblSubject $tblSubject = null)
    {
        return (new Data($this->getBinding()))->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson, $tblSubject);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     *
     * @return TblTaskGrade[]|false
     */
    public function getTaskGradeListByPersonAndYearAndSubject(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getTaskGradeListByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblTask $tblTask
     * @param TblSubject $tblSubject
     *
     * @return TblTaskGrade[]|false
     */
    public function getTaskGradeListByPersonAndYearAndSubjectAndTask(TblPerson $tblPerson, TblTask $tblTask, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getTaskGradeListByPersonAndYearAndSubjectAndTask($tblPerson, $tblTask, $tblSubject);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblTask $tblTask
     * @param TblSubject $tblSubject
     *
     * @return TblTaskGrade|false
     */
    public function getTaskGradeByPersonAndTaskAndSubject(TblPerson $tblPerson, TblTask $tblTask, TblSubject $tblSubject)
    {
        return (new Data($this->getBinding()))->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblTask, $tblSubject);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblTask $tblTask
     * @param TblSubject $tblSubject
     * @param TblGradeType $tblGradeType
     *
     * @return TblTaskGrade|false
     */
    public function getTaskGradeByPersonAndTaskAndSubjectAndGradeType(TblPerson $tblPerson, TblTask $tblTask, TblSubject $tblSubject, TblGradeType $tblGradeType)
    {
        return (new Data($this->getBinding()))->getTaskGradeByPersonAndTaskAndSubjectAndGradeType($tblPerson, $tblTask, $tblSubject, $tblGradeType);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param DateTime $date
     * @param TblGradeType $tblGradeType
     *
     * @return false|TblTaskGrade
     */
    public function getPreviousBehaviorTaskGrade(TblPerson $tblPerson, TblYear $tblYear, TblSubject $tblSubject, DateTime $date, TblGradeType $tblGradeType)
    {
        return (new Data($this->getBinding()))->getPreviousBehaviorTaskGrade($tblPerson, $tblYear, $tblSubject, $date, $tblGradeType);
    }

    /**
     * @param $Data
     * @param $YearId
     * @param $TaskId
     *
     * @return false|Form
     */
    public function checkFormTask($Data, $YearId, $TaskId)
    {
        $error = false;
        $form = Grade::useFrontend()->formTask($YearId, $TaskId, false, $Data);

        if (!$TaskId && !isset($Data['Type'])) {
            $form->setError('Data[Type]', 'Bitte wählen Sie eine Kategorie aus');
            $error = true;
        }
        if (isset($Data['Name']) && empty($Data['Name'])) {
            $form->setError('Data[Name]', 'Bitte geben Sie einen Namen an');
            $error = true;
        }
        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }
        if (isset($Data['FromDate']) && empty($Data['FromDate'])) {
            $form->setError('Data[FromDate]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }
        if (isset($Data['ToDate']) && empty($Data['ToDate'])) {
            $form->setError('Data[ToDate]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }

        if (!$error) {
            $toDate = new DateTime($Data['ToDate']);
            $fromDate = new DateTime($Data['FromDate']);

            if ($fromDate > $toDate) {
                $form->setError('Data[ToDate]', 'Der "Bearbeitungszeitraum bis" darf nicht kleiner sein, als der "Bearbeitungszeitraum von".');
                $error = true;
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param TblYear $tblYear
     * @param bool $IsTypeBehavior
     * @param string $Name
     * @param DateTime|null $Date
     * @param DateTime|null $FromDate
     * @param DateTime|null $ToDate
     * @param bool $IsAllYears
     * @param TblScoreType|null $tblScoreType
     *
     * @return TblTask
     */
    public function createTask(TblYear $tblYear, bool $IsTypeBehavior, string $Name, ?DateTime $Date, ?DateTime $FromDate, ?DateTime $ToDate,
        bool  $IsAllYears, ?TblScoreType $tblScoreType): TblTask
    {
        return (new Data($this->getBinding()))->createTask($tblYear, $IsTypeBehavior, $Name, $Date, $FromDate, $ToDate, $IsAllYears, $tblScoreType);
    }

    /**
     * @param TblTask $tblTask
     * @param string $Name
     * @param DateTime|null $Date
     * @param DateTime|null $FromDate
     * @param DateTime|null $ToDate
     * @param TblScoreType|null $tblScoreType
     * @param bool $IsAllYears
     *
     * @return bool
     */
    public function updateTask(TblTask $tblTask, string $Name, ?DateTime $Date, ?DateTime $FromDate, ?DateTime $ToDate,
        bool  $IsAllYears, ?TblScoreType $tblScoreType): bool
    {
        return (new Data($this->getBinding()))->updateTask($tblTask, $Name, $Date, $FromDate, $ToDate, $IsAllYears, $tblScoreType);
    }

    /**
     * @param TblTask $tblTask
     * @param array $Data
     */
    public function createTaskCourseLinks(TblTask $tblTask, array $Data)
    {
        if (isset($Data['DivisionCourses'])) {
            $createList = array();
            foreach ($Data['DivisionCourses'] as $divisionCourseId => $value) {
                if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))) {
                    $createList[] = new TblTaskCourseLink($tblTask, $tblDivisionCourse);
                }
            }

            Grade::useService()->createEntityListBulk($createList);
        }
    }

    /**
     * @param TblTask $tblTask
     * @param array $Data
     */
    public function updateTaskCourseLinks(TblTask $tblTask, array $Data)
    {
        $createList = array();
        $removeList = array();

        if (($tblDivisionCourseList = $tblTask->getDivisionCourses())) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                // löschen
                if (!isset($Data['DivisionCourses'][$tblDivisionCourse->getId()])) {
                    $removeList[] = $this->getTaskCourseLinkBy($tblTask, $tblDivisionCourse);
                }
            }
        } else {
            $tblDivisionCourseList = array();
        }

        // neu
        if (isset($Data['DivisionCourses'])) {
            foreach ($Data['DivisionCourses'] as $divisionCourseId => $value) {
                if (($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($divisionCourseId))
                    && !isset($tblDivisionCourseList[$divisionCourseId])
                ) {
                    $createList[] = new TblTaskCourseLink($tblTask, $tblDivisionCourse);
                }
            }
        }

        if (!empty($createList)) {
            Grade::useService()->createEntityListBulk($createList);
        }
        if (!empty($removeList)) {
            Grade::useService()->deleteEntityListBulk($removeList);
        }
    }

    /**
     * @param TblTask $tblTask
     * @param array $Data
     */
    public function createTaskGradeTypeLinks(TblTask $tblTask, array $Data)
    {
        if (isset($Data['GradeTypes'])) {
            $createList = array();
            foreach ($Data['GradeTypes'] as $gradeTypeId => $value) {
                if (($tblGradeType = Grade::useService()->getGradeTypeById($gradeTypeId))) {
                    $createList[] = new TblTaskGradeTypeLink($tblTask, $tblGradeType);
                }
            }

            Grade::useService()->createEntityListBulk($createList);
        }
    }

    /**
     * @param TblTask $tblTask
     * @param array $Data
     */
    public function updateTaskGradeTypeLinks(TblTask $tblTask, array $Data)
    {
        $createList = array();
        $removeList = array();

        if (($tblGradeTypeList = $tblTask->getGradeTypes())) {
            foreach ($tblGradeTypeList as $tblGradeType) {
                // löschen
                if (!isset($Data['GradeTypes'][$tblGradeType->getId()])) {
                    $removeList[] = $this->getTaskGradeTypeLinkBy($tblTask, $tblGradeType);
                }
            }
        } else {
            $tblGradeTypeList = array();
        }

        // neu
        if (isset($Data['GradeTypes'])) {
            foreach ($Data['GradeTypes'] as $gradeTypeId => $value) {
                if (($tblGradeType = Grade::useService()->getGradeTypeById($gradeTypeId))
                    && !isset($tblGradeTypeList[$gradeTypeId])
                ) {
                    $createList[] = new TblTaskGradeTypeLink($tblTask, $tblGradeType);
                }
            }
        }

        if (!empty($createList)) {
            Grade::useService()->createEntityListBulk($createList);
        }
        if (!empty($removeList)) {
            Grade::useService()->deleteEntityListBulk($removeList);
        }
    }

    /**
     * @param TblTask $tblTask
     * @param bool $isString
     *
     * @return false|Type[]|string
     */
    public function getSchoolTypeListFromTask(TblTask $tblTask, bool $isString = false)
    {
        $resultList = array();
        if (($tblDivisionCourseList = $tblTask->getDivisionCourses())) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tempList = $tblDivisionCourse->getSchoolTypeListFromStudents())) {
                    $resultList = array_merge($resultList, $tempList);
                }
            }

            $resultList = array_unique($resultList);
        }

        if (empty($resultList)) {
            return false;
        } elseif ($isString) {
            $list = array();
            foreach ($resultList as $item) {
                $list[] = $item->getShortName() ?: $item->getName();
            }
            return implode(", ", $list);
        } else {
            return $resultList;
        }
    }

    /**
     * @param TblTask $tblTask
     *
     * @return bool
     */
    public function deleteTask(TblTask $tblTask): bool
    {
        if (($tempList = (new Data($this->getBinding()))->getTaskCourseLinkListByTask($tblTask))) {
            Grade::useService()->deleteEntityListBulk($tempList);
        }
        if (($tempList = (new Data($this->getBinding()))->getTaskGradeTypeLinkListBy($tblTask))) {
            Grade::useService()->deleteEntityListBulk($tempList);
        }

        return Grade::useService()->deleteEntityListBulk(array($tblTask));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return TblTask[]|false
     */
    public function getTaskListByStudentsInDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $tblTaskList = array();
        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByStudentsInDivisionCourse($tblDivisionCourse))) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tempList = $this->getTaskListByDivisionCourse($tblDivisionCourse))) {
                    foreach ($tempList as $temp) {
                        if (!isset($tblTaskList[$temp->getId()])) {
                            $tblTaskList[$temp->getId()] = $temp;
                        }
                    }
                }
            }
        }

        return empty($tblTaskList) ? false : $tblTaskList;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblYear $tblYear
     *
     * @return TblTask[]|false
     */
    public function getTaskListByStudentAndYear(TblPerson $tblPerson, TblYear $tblYear)
    {
        $tblTaskList = array();
        if (($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByStudentAndYear($tblPerson, $tblYear))) {
            foreach ($tblDivisionCourseList as $tblDivisionCourse) {
                if (($tempList = $this->getTaskListByDivisionCourse($tblDivisionCourse))) {
                    foreach ($tempList as $temp) {
                        if (!isset($tblTaskList[$temp->getId()])) {
                            $tblTaskList[$temp->getId()] = $temp;
                        }
                    }
                }
            }
        }

        return empty($tblTaskList) ? false : $tblTaskList;
    }

    /**
     * @param $Data
     * @param TblTask $tblTask
     * @param TblYear $tblYear
     * @param TblSubject $tblSubject
     * @param $DivisionCourseId
     * @param $Filter
     *
     * @return false|Form
     */
    public function checkFormTaskGrades($Data, TblTask $tblTask, TblYear $tblYear, TblSubject $tblSubject, $DivisionCourseId, $Filter)
    {
        $errorList = array();
        if ($Data) {
            foreach ($Data as $personId => $item) {
                if (($tblPerson = Person::useService()->getPersonById($personId))) {
                    // Kopfnoten
                    if ($tblTask->getIsTypeBehavior()) {
                        $tblScoreType = Grade::useService()->getScoreTypeByIdentifier('GRADES_BEHAVIOR_TASK');
                        if (($tblGradeTypes = $tblTask->getGradeTypes())) {
                            $comment = trim($item['Comment']);
                            foreach ($tblGradeTypes as $tblGradeType) {
                                $gradeValue = $item['GradeTypes'][$tblGradeType->getId()] ?? '';
                                if ($gradeValue === '0' || (!empty($gradeValue) && $gradeValue != -1)) {
                                    // Bewertungssystem Pattern prüfen
                                    if ($tblScoreType && ($pattern = $tblScoreType->getPattern())) {
                                        if (!preg_match('!' . $pattern . '!is', $gradeValue)) {
                                            $errorList[$personId]['GradeTypes'][$tblGradeType->getId()] = true;
                                        }
                                    }

                                    // Grund bei Noten-Änderung angeben
                                    if (empty($comment)
                                        && ($tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubjectAndGradeType(
                                            $tblPerson, $tblTask, $tblSubject, $tblGradeType))
                                        && $gradeValue != $tblTaskGrade->getGrade()
                                    ) {
                                        $errorList[$personId]['Comment'] = true;
                                    }
                                }
                            }
                        }
                    // Stichtagsnoten
                    } else {
                        if (DivisionCourse::useService()->getIsCourseSystemByPersonAndYear($tblPerson, $tblYear)) {
                            $tblScoreType = Grade::useService()->getScoreTypeByIdentifier('POINTS');
                        } else {
                            $tblScoreType = Grade::useService()->getScoreTypeByIdentifier('GRADES');
                        }
                        $comment = trim($item['Comment']);
                        $gradeValue = str_replace(',', '.', trim($item['Grade']));
                        $tblTaskGrade = Grade::useService()->getTaskGradeByPersonAndTaskAndSubject($tblPerson, $tblTask, $tblSubject);

                        // Zeugnistext
                        if (($tblGradeText = isset($item['GradeText']) ? Grade::useService()->getGradeTextById($item['GradeText']) : null)) {
                            $gradeValue = null;
                            // Grund bei Noten-Änderung angeben
                            if (empty($comment)
                                && $tblTaskGrade
                                && (!$tblTaskGrade->getTblGradeText() || $tblTaskGrade->getTblGradeText()->getId() != $tblGradeText->getId())
                            ) {
                                $errorList[$personId]['Comment'] = true;
                            }
                        }

                        // Zensur
                        if ($gradeValue === '0' || (!empty($gradeValue) && $gradeValue != -1)) {
                            // Bewertungssystem Pattern prüfen
                            if ($tblScoreType && ($pattern = $tblScoreType->getPattern())) {
                                if (!preg_match('!' . $pattern . '!is', $gradeValue)) {
                                    $errorList[$personId]['Grade'] = true;
                                }
                            }

                            // Grund bei Noten-Änderung angeben
                            if (empty($comment)
                                && $tblTaskGrade
                                && $gradeValue != $tblTaskGrade->getGrade()
                            ) {
                                $errorList[$personId]['Comment'] = true;
                            }
                        }
                    }
                }
            }
        }

        return empty($errorList) ? false : Grade::useFrontend()->formTaskGrades($tblTask, $tblYear, $tblSubject, $DivisionCourseId, $Filter, false, $errorList, $Data);
    }

    /**
     * @param TblPerson $tblPersonLogin
     *
     * @return false|Layout
     */
    public function getTeacherWelcomeGradeTask(TblPerson $tblPersonLogin)
    {
        $appointedDateTaskList = array();
        $behaviorTaskList = array();
        $futureAppointedDateTaskList = array();
        $futureBehaviorTaskList = array();
        $dataList = array();

        $tblSettingBehaviorHasGrading = ($tblSetting = Consumer::useService()->getSetting('Education', 'Graduation', 'Evaluation', 'HasBehaviorGradesForSubjectsWithNoGrading'))
            && $tblSetting->getValue();
        $now = new DateTime('now');

        if (($tblYearList = Term::useService()->getYearByNow())) {
            foreach ($tblYearList as $tblYear) {
                if (($tblTeacherLectureshipList = DivisionCourse::useService()->getTeacherLectureshipListBy($tblYear, $tblPersonLogin))) {
                    foreach ($tblTeacherLectureshipList as $tblTeacherLectureship) {
                        if (($tblDivisionCourse = $tblTeacherLectureship->getTblDivisionCourse())
                            && ($tblSubject = $tblTeacherLectureship->getServiceTblSubject())
                            && ($tblTaskList = $this->getTaskListByDivisionCourse($tblDivisionCourse))
                        ) {
                            foreach ($tblTaskList as $tblTask) {
                                // current task
                                if ($now > $tblTask->getFromDate() && $now <= $tblTask->getToDate()) {
                                    if ($this->setCurrentTask($tblDivisionCourse, $tblSubject, $tblYear, $tblTask, $dataList, $tblSettingBehaviorHasGrading)) {
                                        if ($tblTask->getIsTypeBehavior()) {
                                            if (!isset($behaviorTaskList[$tblTask->getId()])) {
                                                $behaviorTaskList[$tblTask->getId()] = $tblTask;
                                            }
                                        } else {
                                            if (!isset($appointedDateTaskList[$tblTask->getId()])) {
                                                $appointedDateTaskList[$tblTask->getId()] = $tblTask;
                                            }
                                        }
                                    }
                                // future task
                                } elseif ($now < $tblTask->getFromDate() && $now > ($tblTask->getFromDate()->sub(new DateInterval('P7D')))) {
                                    if ($tblTask->getIsTypeBehavior()) {
                                        $futureBehaviorTaskList[$tblTask->getId()] = $tblTask;
                                    } else {
                                        $futureAppointedDateTaskList[$tblTask->getId()] = $tblTask;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $columns = array();
        $columns = $this->getWelcomeContent($appointedDateTaskList, $columns, $dataList);
        $columns = $this->getWelcomeContent($behaviorTaskList, $columns, $dataList);
        $columns = $this->getWelcomeContent($futureAppointedDateTaskList, $columns, $dataList, true);
        $columns = $this->getWelcomeContent($futureBehaviorTaskList, $columns, $dataList, true);

        if (empty($columns)) {
            return false;
        } else {
            return new Layout(new LayoutGroup(Grade::useService()->getLayoutRowsByLayoutColumnList($columns, 6)));
        }
    }

    private function setCurrentTask(TblDivisionCourse $tblDivisionCourse, TblSubject $tblSubject, TblYear $tblYear, TblTask $tblTask, array &$dataList,
        bool $tblSettingBehaviorHasGrading): bool
    {
        $countPersons = 0;
        $countGrades = 0;
        if (($tblPersonList = $tblDivisionCourse->getStudentsWithSubCourses())) {
            foreach ($tblPersonList as $tblPerson) {
                if (($virtualSubject = DivisionCourse::useService()->getVirtualSubjectFromRealAndVirtualByPersonAndYearAndSubject($tblPerson, $tblYear, $tblSubject))
                    && ($virtualSubject->getHasGrading() || ($tblTask->getIsTypeBehavior() && $tblSettingBehaviorHasGrading))
                ) {
                    $countPersons++;
                    if (($tblTaskGradeList = $this->getTaskGradeListByPersonAndYearAndSubjectAndTask($tblPerson, $tblTask, $tblSubject))) {
                        $countGrades += count($tblTaskGradeList);
                    }
                }
            }
        }

        if ($countPersons == 0) {
            return false;
        }

        if ($tblTask->getIsTypeBehavior() && ($tblGradeTypeList = $tblTask->getGradeTypes())) {
            $countPersons = $countPersons * count($tblGradeTypeList);
        }

        $text = ' ' . $tblDivisionCourse->getDisplayName()
            . ' ' . $tblSubject->getAcronym()
            . ' ' . $tblSubject->getName()
            . ': ' . $countGrades . ' von ' . $countPersons . ' Zensuren vergeben';

        $dataList[$tblTask->getId()][] = new PullClear(($countGrades < $countPersons
                ? new Warning(new Exclamation() . $text)
                : new \SPHERE\Common\Frontend\Text\Repository\Success(new \SPHERE\Common\Frontend\Icon\Repository\Success()
                    . $text))
            . new PullRight(new Standard(
                '',
                '/Education/Graduation/Grade/GradeBook',
                new Extern(),
                array(
                    'DivisionCourseId' => $tblDivisionCourse->getId(),
                    'SubjectId' => $tblSubject->getId(),
                    'TaskId' => $tblTask->getId()
                ),
                'Zur Noteneingabe wechseln'
            )));

        return true;
    }

    /**
     * @param $tblTaskList
     * @param $columns
     * @param $dataList
     * @param bool $isFuture
     *
     * @return array
     */
    private function getWelcomeContent($tblTaskList, $columns, $dataList, bool $isFuture = false): array
    {
        /** @var TblTask $tblTask */
        foreach ($tblTaskList as $tblTask) {
            if ($isFuture) {
                $panel = new Panel(
                    (!$tblTask->getIsTypeBehavior()
                        ? 'Nächster Stichtagsnotenauftrag '
                        : 'Nächster Kopfnotenauftrag '),
                    array(
                        new Muted('Stichtag: ' . $tblTask->getDateString()),
                        new Muted('Bearbeitungszeitraum: ' . $tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString())
                    ),
                    Panel::PANEL_TYPE_INFO
                );
                $columns[] = new LayoutColumn($panel, 6);
            } else {
                $messageList = array();
                if (isset($dataList[$tblTask->getId()])) {
                    sort($dataList[$tblTask->getId()], SORT_NATURAL);
                    $messageList = $dataList[$tblTask->getId()];
                }
                array_unshift($messageList,
                    new Muted('Bearbeitungszeitraum: ' . $tblTask->getFromDateString() . ' - ' . $tblTask->getToDateString()));
                array_unshift($messageList, new Muted('Stichtag: ' . $tblTask->getDateString()));
                $panel = new Panel(
                    (!$tblTask->getIsTypeBehavior()
                        ? 'Aktueller Stichtagsnotenauftrag '
                        : 'Aktueller Kopfnotenauftrag '),
                    $messageList,
                    Panel::PANEL_TYPE_INFO
                );
                $columns[] = new LayoutColumn($panel, 6);
            }
        }

        return $columns;
    }
}