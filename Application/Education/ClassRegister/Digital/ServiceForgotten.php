<?php

namespace SPHERE\Application\Education\ClassRegister\Digital;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Data;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblForgotten;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblForgottenStudent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Message\Repository\Danger;

abstract class ServiceForgotten extends ServiceCourseContent
{
    // wiedervorlage → abhaken? Fach kann da ja auch ausgefallen sein
    // erstmal die letzten 3 fälligen Hausaufgaben anzeigen
    // auch bei Vertretungsfach
    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblSubject|null $tblSubject
     * @param DateTime|null $date
     * @param int|null $limit
     *
     * @return array|false
     */
    public function getDueDateHomeworkListBySubject(
        TblDivisionCourse $tblDivisionCourse,
        ?TblSubject $tblSubject,
        ?DateTime $date = null,
        ?int $limit = null
    ): bool|array {
        $resultList = [];
        if ($tblDivisionCourse->getType()->getIsCourseSystem()) {
            if ($list = (new Data($this->getBinding()))->getDueDateHomeworkListByCourseSystem($tblDivisionCourse, $date, $limit)) {
                foreach ($list as $tblCourseContent) {
                    $resultList[$tblCourseContent->getId()] = array(
                        'Id' => $tblCourseContent->getId(),
                        'Date' => $tblCourseContent->getDate(),
                        'DueDateHomework' => $tblCourseContent->getDueDateHomework(),
                        'Homework' => $tblCourseContent->getHomework()
                    );
                }
            }
        } elseif ($tblSubject) {
            if ($list = (new Data($this->getBinding()))->getDueDateHomeworkListBySubject($tblDivisionCourse, $tblSubject, $date, $limit)) {
                foreach ($list as $tblLessonContent) {
                    $resultList[$tblLessonContent->getId()] = array(
                        'Id' => $tblLessonContent->getId(),
                        'Date' => $tblLessonContent->getDate(),
                        'DueDateHomework' => $tblLessonContent->getDueDateHomework(),
                        'Homework' => $tblLessonContent->getHomework()
                    );
                }
            }
        }

        return empty($resultList) ? false : $resultList;
    }

    /**
     * @param $Id
     *
     * @return false|TblForgotten
     */
    public function getForgottenById($Id): bool|TblForgotten
    {
        return ((new Data($this->getBinding())))->getForgottenById($Id);
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     * @param TblForgotten|null $tblForgotten
     *
     * @return false|Form
     */
    public function checkFormForgotten(
        $Data,
        TblDivisionCourse $tblDivisionCourse,
        ?TblForgotten $tblForgotten = null
    ): Form|bool {
        $error = false;

        $form = Digital::useFrontend()->formForgotten($tblDivisionCourse, $tblForgotten?->getId());

        if (isset($Data['Date']) && empty($Data['Date'])) {
            $form->setError('Data[Date]', 'Bitte geben Sie ein Datum an');
            $error = true;
        }
        if (isset($Data['serviceTblSubject']) && !(Subject::useService()->getSubjectById($Data['serviceTblSubject']))) {
            $form->setError('Data[serviceTblSubject]', 'Bitte wählen Sie ein Fach aus');
            $error = true;
        }
        // kein Schüler ausgewählt
        if (!isset($Data['Students'])) {
            $form->prependGridGroup(new FormGroup(new FormRow(new FormColumn(
                new Danger('Bitte wählen Sie mindestens einen Schüler aus')
            ))));
            $error = true;
        }

        return $error ? $form : false;
    }

    /**
     * @param $Data
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool
     */
    public function createForgotten($Data, TblDivisionCourse $tblDivisionCourse): bool
    {
        $tblSubject = $tblDivisionCourse->getServiceTblSubject() ?: Subject::useService()->getSubjectById($Data['serviceTblSubject']);
        if ($tblSubject &&
            ($tblForgotten = (new Data($this->getBinding()))->createForgotten(
                $tblDivisionCourse,
                $Data['Date'],
                $tblSubject,
                $Data['Remark'],
                isset($Data['LessonContentId']) && ($tblLessonContent = Digital::useService()->getLessonContentById($Data['LessonContentId']))
                    ? $tblLessonContent : null,
                isset($Data['CourseContentId']) && ($tblLessonContent = Digital::useService()->getCourseContentById($Data['CourseContentId']))
                    ? $tblLessonContent : null
            ))
        ) {
            if (isset($Data['Students'])) {
                foreach($Data['Students'] as $personId => $value) {
                    if (($tblPersonItem = Person::useService()->getPersonById($personId))) {
                        (new Data($this->getBinding()))->addForgottenStudent($tblForgotten, $tblPersonItem);
                    }
                }
            }
        }

        return  true;
    }

    /**
     * @param TblForgotten $tblForgotten
     * @param $Data
     *
     * @return bool
     */
    public function updateForgotten(TblForgotten $tblForgotten, $Data): bool
    {
        $tblSubject = $tblForgotten->getServiceTblDivisionCourse()->getServiceTblSubject() ?: Subject::useService()->getSubjectById($Data['serviceTblSubject']);

        (new Data($this->getBinding()))->updateForgotten(
            $tblForgotten,
            $Data['Date'],
            $tblSubject,
            $Data['Remark'],
            isset($Data['LessonContentId']) && ($tblLessonContent = Digital::useService()->getLessonContentById($Data['LessonContentId']))
                ? $tblLessonContent : null,
            isset($Data['CourseContentId']) && ($tblLessonContent = Digital::useService()->getCourseContentById($Data['CourseContentId']))
                ? $tblLessonContent : null
        );

        if (($tblForgottenStudentList = $this->getStudentsByForgotten($tblForgotten))) {
            foreach ($tblForgottenStudentList as $tblForgottenStudent) {
                if (($tblPersonRemove = $tblForgottenStudent->getServiceTblPerson())
                    && !isset($Data['Students'][$tblPersonRemove->getId()])
                ) {
                    (new Data($this->getBinding()))->removeForgottenStudent($tblForgottenStudent);
                }
            }
        }

        if (isset($Data['Students'])) {
            foreach($Data['Students'] as $personId => $value) {
                if (($tblPersonAdd = Person::useService()->getPersonById($personId))) {
                    (new Data($this->getBinding()))->addForgottenStudent($tblForgotten, $tblPersonAdd);
                }
            }
        }

        return true;
    }

    /**
     * @param TblForgotten $tblForgotten
     *
     * @return bool
     */
    public function destroyForgotten(TblForgotten $tblForgotten): bool
    {
        if (($list = $this->getStudentsByForgotten($tblForgotten))) {
            foreach ($list as $tblForgottenStudent) {
                (new Data($this->getBinding()))->removeForgottenStudent($tblForgottenStudent);
            }
        }

        return (new Data($this->getBinding()))->destroyForgotten($tblForgotten);
    }

    /**
     * @param TblForgotten $tblForgotten
     *
     * @return false|TblForgottenStudent[]
     */
    public function getStudentsByForgotten(TblForgotten $tblForgotten): bool|array
    {
        return (new Data($this->getBinding()))->getStudentsByForgotten($tblForgotten);
    }
}