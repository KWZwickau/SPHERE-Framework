<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\Lesson\Course\Course;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\System\Extension\Extension;

class MassReplaceStudentEducation extends Extension
{
    const CLASS_MASS_REPLACE_STUDENT_EDUCATION = 'SPHERE\Application\Api\Education\DivisionCourse\MassReplaceStudentEducation';

    const METHOD_REPLACE_LEVEL = 'replaceLevel';
    const METHOD_REPLACE_SCHOOL_TYPE = 'replaceSchoolType';
    const METHOD_REPLACE_COMPANY = 'replaceCompany';
    const METHOD_REPLACE_COURSE = 'replaceCourse';

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return Pipeline
     */
    public function replaceLevel($modalField, $CloneField, $PersonIdArray = array(), $Id = null, $Data = null)
    {
        $level = intval($CloneField);
        if ($level < 1) {
            $level = null;
        }

        if (isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year']))) {
            $this->updateLevel($PersonIdArray, $tblYear, $level);
        }

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
     * @param array $PersonIdArray
     * @param TblYear $tblYear
     * @param $level
     *
     * @return bool
     */
    private function updateLevel(
        array $PersonIdArray,
        TblYear $tblYear,
        $level
    ): bool {
        if (!empty($PersonIdArray)) {
            $tblStudentEducationBulkList = array();
            foreach ($PersonIdArray as $PersonId) {
                if (($tblPerson = Person::useService()->getPersonById($PersonId))
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                ) {
                    $tblStudentEducation->setLevel($level);
                    $tblStudentEducationBulkList[] = $tblStudentEducation;
                }
            }
            if (!empty($tblStudentEducationBulkList)) {
                return DivisionCourse::useService()->updateStudentEducationBulk($tblStudentEducationBulkList);
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return Pipeline
     */
    public function replaceSchoolType($modalField, $CloneField, $PersonIdArray = array(), $Id = null, $Data = null)
    {
        // get selected SchoolType
        $tblSchoolType = Type::useService()->getTypeById($CloneField);

        // change miss matched to null
        if (!$tblSchoolType && null !== $tblSchoolType) {
            $tblSchoolType = null;
        }

        if (isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year']))) {
            $this->updateSchoolType($PersonIdArray, $tblYear, $tblSchoolType);
        }

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
     * @param array $PersonIdArray
     * @param TblYear $tblYear
     * @param $tblSchoolType
     *
     * @return bool
     */
    private function updateSchoolType(
        array $PersonIdArray,
        TblYear $tblYear,
        $tblSchoolType
    ): bool {
        if (!empty($PersonIdArray)) {
            $tblStudentEducationBulkList = array();
            foreach ($PersonIdArray as $PersonId) {
                if (($tblPerson = Person::useService()->getPersonById($PersonId))
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                ) {
                    $tblStudentEducation->setServiceTblSchoolType($tblSchoolType);
                    $tblStudentEducationBulkList[] = $tblStudentEducation;
                }
            }
            if (!empty($tblStudentEducationBulkList)) {
                return DivisionCourse::useService()->updateStudentEducationBulk($tblStudentEducationBulkList);
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return Pipeline
     */
    public function replaceCompany($modalField, $CloneField, $PersonIdArray = array(), $Id = null, $Data = null)
    {
        // get selected Company
        $tblCompany = Company::useService()->getCompanyById($CloneField);

        // change miss matched to null
        if (!$tblCompany && null !== $tblCompany) {
            $tblCompany = null;
        }

        if (isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year']))) {
            $this->updateCompany($PersonIdArray, $tblYear, $tblCompany);
        }

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
     * @param array $PersonIdArray
     * @param TblYear $tblYear
     * @param $tblCompany
     *
     * @return bool
     */
    private function updateCompany(
        array $PersonIdArray,
        TblYear $tblYear,
        $tblCompany
    ): bool {
        if (!empty($PersonIdArray)) {
            $tblStudentEducationBulkList = array();
            foreach ($PersonIdArray as $PersonId) {
                if (($tblPerson = Person::useService()->getPersonById($PersonId))
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                ) {
                    $tblStudentEducation->setServiceTblCompany($tblCompany);
                    $tblStudentEducationBulkList[] = $tblStudentEducation;
                }
            }
            if (!empty($tblStudentEducationBulkList)) {
                return DivisionCourse::useService()->updateStudentEducationBulk($tblStudentEducationBulkList);
            }

            return true;
        }

        return false;
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return Pipeline
     */
    public function replaceCourse($modalField, $CloneField, $PersonIdArray = array(), $Id = null, $Data = null)
    {
        // get selected Course
        $tblCourse = Course::useService()->getCourseById($CloneField);

        // change miss matched to null
        if (!$tblCourse && null !== $tblCourse) {
            $tblCourse = null;
        }

        if (isset($Data['Year']) && ($tblYear = Term::useService()->getYearById($Data['Year']))) {
            $this->updateCourse($PersonIdArray, $tblYear, $tblCourse);
        }

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
     * @param array $PersonIdArray
     * @param TblYear $tblYear
     * @param $tblCourse
     *
     * @return bool
     */
    private function updateCourse(
        array $PersonIdArray,
        TblYear $tblYear,
        $tblCourse
    ): bool {
        if (!empty($PersonIdArray)) {
            $tblStudentEducationBulkList = array();
            foreach ($PersonIdArray as $PersonId) {
                if (($tblPerson = Person::useService()->getPersonById($PersonId))
                    && ($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))
                ) {
                    $tblStudentEducation->setServiceTblCourse($tblCourse);
                    $tblStudentEducationBulkList[] = $tblStudentEducation;
                }
            }
            if (!empty($tblStudentEducationBulkList)) {
                return DivisionCourse::useService()->updateStudentEducationBulk($tblStudentEducationBulkList);
            }

            return true;
        }

        return false;
    }
}