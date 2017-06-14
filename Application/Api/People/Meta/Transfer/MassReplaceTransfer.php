<?php

namespace SPHERE\Application\Api\People\Meta\Transfer;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

class MassReplaceTransfer extends Extension
{

    const CLASS_MASS_REPLACE_TRANSFER = 'SPHERE\Application\Api\People\Meta\Transfer\MassReplaceTransfer';

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
     * @param array  $Meta
     * @param array  $PersonIdArray
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceCurrentSchool($modalField, $CloneField, $Meta, $PersonIdArray = array())
    {

//        $tblCompany = false;
        $tblCourse = false;
        $transferDate = null;
        $Remark = '';
        $StudentTransferTypeIdentifier = 'PROCESS';

//        if (isset($Meta['Transfer'][4]['School']) && $Meta['Transfer'][4]['School']) {
//            $CompanyId = $Meta['Transfer'][4]['School'];
//            $tblCompany = Company::useService()->getCompanyById($CompanyId);
//        }
        if (isset($Meta['Transfer'][4]['Course']) && $Meta['Transfer'][4]['Course']) {
            $CourseId = $Meta['Transfer'][4]['Course'];
            $tblCourse = Course::useService()->getCourseById($CourseId);
        }
        if (isset($Meta['Transfer'][4]['Remark']) && $Meta['Transfer'][4]['Remark']) {
            $Remark = $Meta['Transfer'][4]['Remark'];
        }

        // get selected Company
        $tblCompany = Company::useService()->getCompanyById($CloneField);

        // change miss matched to null
        if (!$tblCompany && null !== $tblCompany) {
            $tblCompany = null;
        }
        if (!$tblCourse && null !== $tblCourse) {
            $tblCourse = null;
        }

        $this->useService()->createTransferByPersonIdList($PersonIdArray, $StudentTransferTypeIdentifier, $tblCompany,
            null, $tblCourse, null, $Remark);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        return ApiMassReplace::pipelineClose($Field, $CloneField);

//        return new Code( print_r( $this->getGlobal()->POST, true ) )
//        .new Code( print_r( $CloneField, true ) );
    }

//    /**
//     * @param string $modalField
//     * @param int    $CloneField
//     * @param array  $Meta
//     * @param array  $PersonIdArray
//     *
//     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
//     */
//    public function replaceCurrentSchoolType($modalField, $CloneField, $Meta, $PersonIdArray = array())
//    {
//
//        $tblCompany = false;
//        $tblCourse = false;
//        $transferDate = null;
//        $Remark = '';
//        $StudentTransferTypeIdentifier = 'PROCESS';
//
//        if (isset($Meta['Transfer'][4]['School']) && $Meta['Transfer'][4]['School']) {
//            $CompanyId = $Meta['Transfer'][4]['School'];
//            $tblCompany = Company::useService()->getCompanyById($CompanyId);
//        }
//        if (isset($Meta['Transfer'][4]['Course']) && $Meta['Transfer'][4]['Course']) {
//            $CourseId = $Meta['Transfer'][4]['Course'];
//            $tblCourse = Course::useService()->getCourseById($CourseId);
//        }
//        if (isset($Meta['Transfer'][4]['Remark']) && $Meta['Transfer'][4]['Remark']) {
//            $Remark = $Meta['Transfer'][4]['Remark'];
//        }
//
//        // get selected Company
//        $tblType = Type::useService()->getTypeById($CloneField);
//
//        // change miss matched to null
//        if (!$tblCompany && null !== $tblCompany) {
//            $tblCompany = null;
//        }
//        if (!$tblType && null !== $tblType) {
//            $tblType = null;
//        }
//        if (!$tblCourse && null !== $tblCourse) {
//            $tblCourse = null;
//        }
//
//        $this->useService()->createTransferByPersonIdList($PersonIdArray, $StudentTransferTypeIdentifier, $tblCompany,
//            $tblType, $tblCourse, null, $Remark);
//
//        /** @var AbstractField $Field */
//        $Field = unserialize(base64_decode($modalField));
//
//        // Success!
//        return ApiMassReplace::pipelineClose($Field, $CloneField);
//    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $Meta
     * @param array  $PersonIdArray
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceCurrentCourse($modalField, $CloneField, $Meta, $PersonIdArray = array())
    {

        $tblCompany = false;
        $transferDate = null;
        $Remark = '';
        $StudentTransferTypeIdentifier = 'PROCESS';

        if (isset($Meta['Transfer'][4]['School']) && $Meta['Transfer'][4]['School']) {
            $CompanyId = $Meta['Transfer'][4]['School'];
            $tblCompany = Company::useService()->getCompanyById($CompanyId);
        }
        if (isset($Meta['Transfer'][4]['Remark']) && $Meta['Transfer'][4]['Remark']) {
            $Remark = $Meta['Transfer'][4]['Remark'];
        }

        // get selected Company
        $tblCourse = Course::useService()->getCourseById($CloneField);

        // change miss matched to null
        if (!$tblCompany && null !== $tblCompany) {
            $tblCompany = null;
        }
        if (!$tblCourse && null !== $tblCourse) {
            $tblCourse = null;
        }

        $this->useService()->createTransferByPersonIdList($PersonIdArray, $StudentTransferTypeIdentifier, $tblCompany,
            null, $tblCourse, null, $Remark);

        /** @var AbstractField $Field */
        $Field = unserialize(base64_decode($modalField));

        // Success!
        return ApiMassReplace::pipelineClose($Field, $CloneField);
    }
}