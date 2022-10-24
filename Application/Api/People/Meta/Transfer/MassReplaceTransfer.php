<?php

namespace SPHERE\Application\Api\People\Meta\Transfer;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferType;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\System\Extension\Extension;

/**
 * Class MassReplaceTransfer
 *
 * @package SPHERE\Application\Api\People\Meta\Transfer
 */
class MassReplaceTransfer extends Extension
{

    const CLASS_MASS_REPLACE_TRANSFER = 'SPHERE\Application\Api\People\Meta\Transfer\MassReplaceTransfer';

    const METHOD_REPLACE_ENROLLMENT_SCHOOL = 'replaceEnrollmentSchool';
    const METHOD_REPLACE_ENROLLMENT_SCHOOL_TYPE = 'replaceEnrollmentSchoolType';
    const METHOD_REPLACE_ENROLLMENT_COURSE = 'replaceEnrollmentCourse';
    const METHOD_REPLACE_ENROLLMENT_TYPE = 'replaceEnrollmentType';
    const METHOD_REPLACE_ENROLLMENT_TRANSFER_DATE = 'replaceEnrollmentTransferDate';
    const METHOD_REPLACE_ARRIVE_SCHOOL = 'replaceArriveSchool';
    const METHOD_REPLACE_ARRIVE_STATE_SCHOOL = 'replaceArriveStateSchool';
    const METHOD_REPLACE_ARRIVE_SCHOOL_TYPE = 'replaceArriveSchoolType';
    const METHOD_REPLACE_ARRIVE_COURSE = 'replaceArriveCourse';
    const METHOD_REPLACE_ARRIVE_TRANSFER_DATE = 'replaceArriveTransferDate';
    const METHOD_REPLACE_LEAVE_SCHOOL = 'replaceLeaveSchool';
    const METHOD_REPLACE_LEAVE_SCHOOL_TYPE = 'replaceLeaveSchoolType';
    const METHOD_REPLACE_LEAVE_COURSE = 'replaceLeaveCourse';
    const METHOD_REPLACE_LEAVE_TRANSFER_DATE = 'replaceLeaveTransferDate';
//    const METHOD_REPLACE_CURRENT_SCHOOL = 'replaceCurrentSchool';
//    const METHOD_REPLACE_CURRENT_SCHOOL_TYPE = 'replaceCurrentSchoolType';
//    const METHOD_REPLACE_CURRENT_COURSE = 'replaceCurrentCourse';

    /**
     * @return StudentService
     */
    private function useStudentService()
    {

        return new StudentService();
    }

//    /**
//     * @param string $modalField
//     * @param int    $CloneField
//     * @param array  $PersonIdArray
//     * @param null   $Id
//     *
//     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
//     */
//    public function replaceCurrentSchool($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
//    {
//
//        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
//
//        // get selected Company
//        $tblCompany = Company::useService()->getCompanyById($CloneField);
//
//        // change miss matched to null
//        if (!$tblCompany && null !== $tblCompany) {
//            $tblCompany = null;
//        }
//
//        $this->useStudentService()->createTransferCompany($PersonIdArray, $tblStudentTransferType->getIdentifier(),
//            $tblCompany);
//
//        /** @var AbstractField $Field */
//        $Field = unserialize(base64_decode($modalField));
//
//        // Success!
//        $IsChange = false;
//        if($Id != null && !empty($PersonIdArray)){
//            if(array_search($Id, $PersonIdArray)){
//                $IsChange = true;
//            }
//        }
//        return ApiMassReplace::pipelineClose($Field, $CloneField, $IsChange);
//
////        return new Code( print_r( $this->getGlobal()->POST, true ) )
////        .new Code( print_r( $CloneField, true ) );
//    }

//    /**
//     * @param string $modalField
//     * @param int    $CloneField
//     * @param array  $PersonIdArray
//     * @param null   $Id
//     *
//     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
//     */
//    public function replaceCurrentCourse($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
//    {
//
//        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
//
//        // get selected Company
//        $tblCourse = Course::useService()->getCourseById($CloneField);
//
//        // change miss matched to null
//        if (!$tblCourse && null !== $tblCourse) {
//            $tblCourse = null;
//        }
//
//        $this->useStudentService()->createTransferCourse($PersonIdArray, $tblStudentTransferType->getIdentifier(),
//            $tblCourse);
//
//        /** @var AbstractField $Field */
//        $Field = unserialize(base64_decode($modalField));
//
//        // Success!
//        $IsChange = false;
//        if($Id != null && !empty($PersonIdArray)){
//            if(array_search($Id, $PersonIdArray)){
//                $IsChange = true;
//            }
//        }
//        return ApiMassReplace::pipelineClose($Field, $CloneField, $IsChange);
//    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceEnrollmentSchool($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');

        // get selected Company
        $tblCompany = Company::useService()->getCompanyById($CloneField);

        // change miss matched to null
        if (!$tblCompany && null !== $tblCompany) {
            $tblCompany = null;
        }

        $this->useStudentService()->createTransferCompany($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblCompany);

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
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceEnrollmentSchoolType($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');

        // get selected Company
        $tblType = Type::useService()->getTypeById($CloneField);

        // change miss matched to null
        if (!$tblType && null !== $tblType) {
            $tblType = null;
        }

        $this->useStudentService()->createTransferType($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblType);

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
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceEnrollmentCourse($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');

        // get selected Company
        $tblCourse = Course::useService()->getCourseById($CloneField);

        // change miss matched to null
        if (!$tblCourse && null !== $tblCourse) {
            $tblCourse = null;
        }

        $this->useStudentService()->createTransferCourse($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblCourse);

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
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceEnrollmentType($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');

        // get selected Company
        $tblStudentSchoolEnrollmentType = Student::useService()->getStudentSchoolEnrollmentTypeById($CloneField);

        // change miss matched to null
        if (!$tblStudentSchoolEnrollmentType && null !== $tblStudentSchoolEnrollmentType) {
            $tblStudentSchoolEnrollmentType = null;
        }

        $this->useStudentService()->createTransferEnrollmentType($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblStudentSchoolEnrollmentType);

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
     * @param $modalField
     * @param $CloneField
     * @param array $PersonIdArray
     * @param null $Id
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceEnrollmentTransferDate($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');

        return $this->replaceTransferDate($modalField, $CloneField, $PersonIdArray, $Id, $tblStudentTransferType);
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceArriveSchool($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');

        // get selected Company
        $tblCompany = Company::useService()->getCompanyById($CloneField);

        // change miss matched to null
        if (!$tblCompany && null !== $tblCompany) {
            $tblCompany = null;
        }

        $this->useStudentService()->createTransferCompany($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblCompany);

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
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceArriveStateSchool($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');

        // get selected Company
        $tblStateCompany = Company::useService()->getCompanyById($CloneField);

        // change miss matched to null
        if (!$tblStateCompany && null !== $tblStateCompany) {
            $tblStateCompany = null;
        }

        $this->useStudentService()->createTransferStateCompany($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblStateCompany);

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
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceArriveSchoolType($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');

        // get selected Company
        $tblType = Type::useService()->getTypeById($CloneField);

        // change miss matched to null
        if (!$tblType && null !== $tblType) {
            $tblType = null;
        }

        $this->useStudentService()->createTransferType($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblType);

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
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceArriveCourse($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');

        // get selected Company
        $tblCourse = Course::useService()->getCourseById($CloneField);

        // change miss matched to null
        if (!$tblCourse && null !== $tblCourse) {
            $tblCourse = null;
        }

        $this->useStudentService()->createTransferCourse($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblCourse);

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
     * @param $modalField
     * @param $CloneField
     * @param array $PersonIdArray
     * @param null $Id
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceArriveTransferDate($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ARRIVE');

        return $this->replaceTransferDate($modalField, $CloneField, $PersonIdArray, $Id, $tblStudentTransferType);
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceLeaveSchool($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');

        // get selected Company
        $tblCompany = Company::useService()->getCompanyById($CloneField);

        // change miss matched to null
        if (!$tblCompany && null !== $tblCompany) {
            $tblCompany = null;
        }

        $this->useStudentService()->createTransferCompany($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblCompany);

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
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceLeaveSchoolType($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');

        // get selected Company
        $tblType = Type::useService()->getTypeById($CloneField);

        // change miss matched to null
        if (!$tblType && null !== $tblType) {
            $tblType = null;
        }

        $this->useStudentService()->createTransferType($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblType);

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
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceLeaveCourse($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');

        // get selected Company
        $tblCourse = Course::useService()->getCourseById($CloneField);

        // change miss matched to null
        if (!$tblCourse && null !== $tblCourse) {
            $tblCourse = null;
        }

        $this->useStudentService()->createTransferCourse($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblCourse);

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
     * @param $modalField
     * @param $CloneField
     * @param array $PersonIdArray
     * @param null $Id
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceLeaveTransferDate($modalField, $CloneField, $PersonIdArray = array(), $Id = null)
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('LEAVE');

        return $this->replaceTransferDate($modalField, $CloneField, $PersonIdArray, $Id, $tblStudentTransferType);
    }

    /**
     * @param $modalField
     * @param $CloneField
     * @param $PersonIdArray
     * @param $Id
     * @param $tblStudentTransferType
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceTransferDate($modalField, $CloneField, $PersonIdArray, $Id, TblStudentTransferType $tblStudentTransferType)
    {
        // get selected Date
        $transferDate = $CloneField;

        $this->useStudentService()->createTransferDate($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $transferDate);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        $IsChange = false;
        if ($Id != null && !empty($PersonIdArray)) {
            if (array_search($Id, $PersonIdArray)) {
                $IsChange = true;
            }
        }

        return ApiMassReplace::pipelineClose($Field, $CloneField, $IsChange);
    }
}