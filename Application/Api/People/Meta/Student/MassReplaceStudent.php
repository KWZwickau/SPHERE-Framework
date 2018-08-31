<?php
namespace SPHERE\Application\Api\People\Meta\Student;

use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\System\Extension\Extension;

class MassReplaceStudent extends Extension
{

    const CLASS_MASS_REPLACE_STUDENT = 'SPHERE\Application\Api\People\Meta\Student\MassReplaceStudent';

    const METHOD_REPLACE_PREFIX = 'replacePrefix';
    const METHOD_REPLACE_START_DATE = 'replaceStartDate';

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
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replacePrefix(
        $modalField,
        $CloneField,
        $PersonIdArray = array(),
        $Id = null
    ) {


        $Prefix = $CloneField;
        if (!empty($PersonIdArray)) {
            $this->useStudentService()->replacePrefixByPersonIdList($PersonIdArray, $Prefix);
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
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceStartDate(
        $modalField,
        $CloneField,
        $PersonIdArray = array(),
        $Id = null
    ) {


        $Date = $CloneField;

        if (!empty($PersonIdArray) && ($Date = new \DateTime($Date))) {
            $this->useStudentService()->replaceStartDateByPersonIdList($PersonIdArray, $Date);
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
}