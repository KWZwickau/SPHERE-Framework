<?php

namespace SPHERE\Application\Education\Graduation\Grade;

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
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Structure\Form;

abstract class ServiceTask extends ServiceGradeType
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
     * @param TblYear $tblYear
     *
     * @return false|TblTask[]
     */
    public function getTaskListByYear(TblYear $tblYear)
    {
        return (new Data($this->getBinding()))->getTaskListByYear($tblYear);
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
     *
     * @return false|TblTaskGrade[]
     */
    public function getTaskGradeListByTaskAndPerson(TblTask $tblTask, TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->getTaskGradeListByTaskAndPerson($tblTask, $tblPerson);
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
}