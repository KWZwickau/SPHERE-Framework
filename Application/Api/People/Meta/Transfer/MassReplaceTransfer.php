<?php

namespace SPHERE\Application\Api\People\Meta\Transfer;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

class MassReplaceTransfer extends Extension
{

    const CLASS_MASS_REPLACE_TRANSFER = 'SPHERE\Application\Api\People\Meta\Transfer\MassReplaceTransfer';

    const METHOD_REPLACE_ENROLLMENT_SCHOOL = 'replaceEnrollmentSchool';
    const METHOD_REPLACE_ENROLLMENT_SCHOOL_TYPE = 'replaceEnrollmentSchoolType';
    const METHOD_REPLACE_ENROLLMENT_COURSE = 'replaceEnrollmentCourse';
    const METHOD_REPLACE_CURRENT_SCHOOL = 'replaceCurrentSchool';
//    const METHOD_REPLACE_CURRENT_SCHOOL_TYPE = 'replaceCurrentSchoolType';
    const METHOD_REPLACE_CURRENT_COURSE = 'replaceCurrentCourse';

    /**
     * @return Service
     */
    private function useService()
    {

        return new Service(
            new Identifier('People', 'Meta', null, null, Consumer::useService()->getConsumerBySession()),
            'SPHERE\Application\People\Meta\Student/Service/Entity',
            'SPHERE\Application\People\Meta\Student\Service\Entity'
        );
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceCurrentSchool($modalField, $CloneField, $PersonIdArray = array())
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');

        // get selected Company
        $tblCompany = Company::useService()->getCompanyById($CloneField);

        // change miss matched to null
        if (!$tblCompany && null !== $tblCompany) {
            $tblCompany = null;
        }

        $this->useService()->createTransferCompany($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblCompany);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        return ApiMassReplace::pipelineClose($Field, $CloneField);

//        return new Code( print_r( $this->getGlobal()->POST, true ) )
//        .new Code( print_r( $CloneField, true ) );
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceCurrentCourse($modalField, $CloneField, $PersonIdArray = array())
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');

        // get selected Company
        $tblCourse = Course::useService()->getCourseById($CloneField);

        // change miss matched to null
        if (!$tblCourse && null !== $tblCourse) {
            $tblCourse = null;
        }

        $this->useService()->createTransferCourse($PersonIdArray, $tblStudentTransferType->getIdentifier(), $tblCourse);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        return ApiMassReplace::pipelineClose($Field, $CloneField);
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceEnrollmentSchool($modalField, $CloneField, $PersonIdArray = array())
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');

        // get selected Company
        $tblCompany = Company::useService()->getCompanyById($CloneField);

        // change miss matched to null
        if (!$tblCompany && null !== $tblCompany) {
            $tblCompany = null;
        }

        $this->useService()->createTransferCompany($PersonIdArray, $tblStudentTransferType->getIdentifier(),
            $tblCompany);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        return ApiMassReplace::pipelineClose($Field, $CloneField);
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceEnrollmentSchoolType($modalField, $CloneField, $PersonIdArray = array())
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');

        // get selected Company
        $tblType = Type::useService()->getTypeById($CloneField);

        // change miss matched to null
        if (!$tblType && null !== $tblType) {
            $tblType = null;
        }

        $this->useService()->createTransferType($PersonIdArray, $tblStudentTransferType->getIdentifier(), $tblType);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        return ApiMassReplace::pipelineClose($Field, $CloneField);
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceEnrollmentCourse($modalField, $CloneField, $PersonIdArray = array())
    {

        $tblStudentTransferType = Student::useService()->getStudentTransferTypeByIdentifier('ENROLLMENT');

        // get selected Company
        $tblCourse = Course::useService()->getCourseById($CloneField);

        // change miss matched to null
        if (!$tblCourse && null !== $tblCourse) {
            $tblCourse = null;
        }

        $this->useService()->createTransferCourse($PersonIdArray, $tblStudentTransferType->getIdentifier(), $tblCourse);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        return ApiMassReplace::pipelineClose($Field, $CloneField);
    }
}