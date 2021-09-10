<?php
namespace SPHERE\Application\Api\People\Meta\MedicalRecord;

use DateTime;
use SPHERE\Application\Api\MassReplace\ApiMassReplace;
use SPHERE\Common\Frontend\Form\Repository\AbstractField;
use SPHERE\System\Extension\Extension;

class MassReplaceMedicalRecord extends Extension
{

    const CLASS_MASS_REPLACE_MEDICAL_RECORD = 'SPHERE\Application\Api\People\Meta\MedicalRecord\MassReplaceMedicalRecord';

    const METHOD_REPLACE_MASERN_DATE = 'replaceMasernDate';
    const METHOD_REPLACE_MASERN_DOCUMENT = 'replaceMasernDocument';
    const METHOD_REPLACE_MASERN_CREATOR = 'replaceMasernCreator';

    /**
     * @return MedicalRecordService
     */
    private function useMedicalRecordService()
    {

        return new MedicalRecordService();
    }

    /**
     * @param string $modalField
     * @param int    $CloneField
     * @param array  $PersonIdArray
     * @param null   $Id
     *
     * @return \SPHERE\Common\Frontend\Ajax\Pipeline
     */
    public function replaceMasernDate(
        $modalField,
        $CloneField,
        $PersonIdArray = array(),
        $Id = null
    ) {

        if($CloneField != ''){
            $Date = new DateTime($CloneField);
        } else {
            $Date = null;
        }

        if (!empty($PersonIdArray)) {
            $this->useMedicalRecordService()->replaceMasernDateByPersonIdList($PersonIdArray, $Date);
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
    public function replaceMasernDocument(
        $modalField,
        $CloneField,
        $PersonIdArray = array(),
        $Id = null
    ) {

        if (!empty($PersonIdArray)) {
            $this->useMedicalRecordService()->replaceMasernDocumentByPersonIdList($PersonIdArray, $CloneField);
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
    public function replaceMasernCreator(
        $modalField,
        $CloneField,
        $PersonIdArray = array(),
        $Id = null
    ) {

        if (!empty($PersonIdArray)) {
            $this->useMedicalRecordService()->replaceMasernCreatorByPersonIdList($PersonIdArray, $CloneField);
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