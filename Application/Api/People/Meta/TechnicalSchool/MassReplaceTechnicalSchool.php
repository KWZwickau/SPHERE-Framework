<?php

namespace SPHERE\Application\Api\People\Meta\TechnicalSchool;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\System\Extension\Extension;

/**
 * Class MassReplaceTechnicalSchool
 *
 * @package SPHERE\Application\Api\People\Meta\TechnicalSchool
 */
class MassReplaceTechnicalSchool extends Extension
{

    const CLASS_MASS_REPLACE_TECHNICAL_SCHOOL = 'SPHERE\Application\Api\People\Meta\TechnicalSchool\MassReplaceTechnicalSchool';

    const METHOD_REPLACE_COURSE = 'replaceCourse';
    const METHOD_REPLACE_SUBJECT_AREA = 'replaceSubjectArea';
    const METHOD_REPLACE_STUDENT_TENSE_OF_LESSON = 'replaceStudentTenseOfLesson';
    const METHOD_REPLACE_STUDENT_TRAINING_STATUS = 'replaceStudentTrainingStatus';

    /**
     * @return StudentService
     */
    private function useStudentService()
    {

        return new StudentService();
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return Pipeline
     */
    public function replaceCourse(
        $modalField,
        $CloneField,
        $PersonIdArray = array(),
        $Id = null
    ) {
        // get selected Course
        $tblTechnicalCourse = Course::useService()->getTechnicalCourseById($CloneField);

        // change miss matched to null
        if (!$tblTechnicalCourse && null !== $tblTechnicalCourse) {
            $tblTechnicalCourse = null;
        }

        $this->useStudentService()->createTechnicalCourse($PersonIdArray, $tblTechnicalCourse);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        $IsChange = false;
        if($Id != null && !empty($PersonIdArray)){
            if(array_search($Id, $PersonIdArray)){
                $IsChange = true;
            }
        }

        return ApiMassReplace::pipelineClose($Field, $CloneField, $IsChange);
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return Pipeline
     */
    public function replaceSubjectArea(
        $modalField,
        $CloneField,
        $PersonIdArray = array(),
        $Id = null
    ) {
        // get selected Course
        $tblTechnicalSubjectArea = Course::useService()->getTechnicalSubjectAreaById($CloneField);

        // change miss matched to null
        if (!$tblTechnicalSubjectArea && null !== $tblTechnicalSubjectArea) {
            $tblTechnicalSubjectArea = null;
        }

        $this->useStudentService()->createTechnicalSubjectArea($PersonIdArray, $tblTechnicalSubjectArea);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        $IsChange = false;
        if($Id != null && !empty($PersonIdArray)){
            if(array_search($Id, $PersonIdArray)){
                $IsChange = true;
            }
        }

        return ApiMassReplace::pipelineClose($Field, $CloneField, $IsChange);
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return Pipeline
     */
    public function replaceStudentTenseOfLesson(
        $modalField,
        $CloneField,
        $PersonIdArray = array(),
        $Id = null
    ) {
        // get selected StudentTenseOfLesson
        $tblStudentTenseOfLesson = Student::useService()->getStudentTenseOfLessonById($CloneField);

        // change miss matched to null
        if (!$tblStudentTenseOfLesson && null !== $tblStudentTenseOfLesson) {
            $tblStudentTenseOfLesson = null;
        }

        $this->useStudentService()->createStudentTenseOfLesson($PersonIdArray, $tblStudentTenseOfLesson);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        $IsChange = false;
        if($Id != null && !empty($PersonIdArray)){
            if(array_search($Id, $PersonIdArray)){
                $IsChange = true;
            }
        }

        return ApiMassReplace::pipelineClose($Field, $CloneField, $IsChange);
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return Pipeline
     */
    public function replaceStudentTrainingStatus(
        $modalField,
        $CloneField,
        $PersonIdArray = array(),
        $Id = null
    ) {
        // get selected StudentTrainingStatus
        $tblStudentTrainingStatus = Student::useService()->getStudentTrainingStatusById($CloneField);

        // change miss matched to null
        if (!$tblStudentTrainingStatus && null !== $tblStudentTrainingStatus) {
            $tblStudentTrainingStatus = null;
        }

        $this->useStudentService()->createStudentTrainingStatus($PersonIdArray, $tblStudentTrainingStatus);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        $IsChange = false;
        if($Id != null && !empty($PersonIdArray)){
            if(array_search($Id, $PersonIdArray)){
                $IsChange = true;
            }
        }

        return ApiMassReplace::pipelineClose($Field, $CloneField, $IsChange);
    }
}