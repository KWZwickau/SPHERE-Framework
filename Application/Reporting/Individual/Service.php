<?php

namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Reporting\Individual\Service\Data;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPresetSetting;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudent;
use SPHERE\Application\Reporting\Individual\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Reporting\Individual
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param $Id
     *
     * @return false|TblWorkSpace
     */
    public function getWorkSpaceById($Id)
    {

        return (new Data($this->getBinding()))->getWorkSpaceById($Id);
    }

    /**
     * @return bool|TblWorkSpace[]
     */
    public function getWorkSpaceAll()
    {
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            return (new Data($this->getBinding()))->getWorkSpaceAllByAccount($tblAccount);
        }
        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblPreset
     */
    public function getPresetById($Id)
    {

        return (new Data($this->getBinding()))->getPresetById($Id);
    }

    /**
     * @return bool|TblWorkSpace[]
     */
    public function getPresetAll()
    {
        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            return (new Data($this->getBinding()))->gePresetAllByAccount($tblAccount);
        }
        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblPresetSetting
     */
    public function getPresetSettingById($Id)
    {

        return (new Data($this->getBinding()))->getPresetSettingById($Id);
    }

    /**
     * @param TblPreset $tblPreset
     *
     * @return false|TblPresetSetting[]
     */
    public function getPresetSettingAllByPreset(TblPreset $tblPreset)
    {

        return (new Data($this->getBinding()))->getPresetSettingAllByPreset($tblPreset);
    }

    /**
     * @param $Field
     * @param $View
     * @param $Position
     *
     * @return bool|TblWorkSpace
     */
    public function addWorkSpaceField($Field, $View, $Position)
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            return (new Data($this->getBinding()))->createWorkSpace($tblAccount, $Field, $View, $Position);
        }
        return false;
    }

    public function removeWorkSpaceFieldAll()
    {

        $tblWorkspaceList = Individual::useService()->getWorkSpaceAll();
        if ($tblWorkspaceList) {
            foreach ($tblWorkspaceList as $tblWorkspace) {
                (new Data($this->getBinding()))->removeWorkSpace($tblWorkspace);
            }
            return true;
        }
        return false;
    }

    /**
     * @return false|Service\Entity\ViewStudent[]|\SPHERE\System\Database\Fitting\Element[]
     */
    public function getView()
    {
        return (new Data($this->getBinding()))->getView();
    }

    /**
     * @param string $FieldName
     *
     * @return string
     */
    public function getFieldLabelByFieldName($FieldName)
    {
        $FieldDefinition = array(
            'TblCommonGender_Name'                    => 'Geschlecht',
            'TblSalutation_Salutation'                => 'Anrede',
            'TblPerson_Title'                         => 'Titel',
            'TblPerson_FirstName'                     => 'Vorname',
            'TblPerson_SecondName'                    => 'Zweiter Vorname',
            'TblPerson_LastName'                      => 'Nachname',
            'TblCommonInformation_IsAssistance'       => 'Mitarbeitsbereitschaft',
            'TblCommonInformation_AssistanceActivity' => 'Mitarbeit - Tätigkeit',
            'TblCommon_Remark'                        => 'Personendaten Bemerkung'
        );

        foreach ($FieldDefinition as $FieldCompare => $Value) {
            if ($FieldName == $FieldCompare) {
                return $Value;
            }
        }

        return $FieldName;
    }

    /**
     * @return array|bool
     */
    public function getStudentViewList()
    {
        $BlockConstantList = array();
        $ConstantList = ViewStudent::getConstants();
        if ($ConstantList) {
            foreach ($ConstantList as $Constant) {
                switch ($Constant) {
                    case 'TblCommonGender_Name':
                        $BlockConstantList['Personendaten'][] = $Constant;
                        break;
                    case 'TblSalutation_Salutation':
                        $BlockConstantList['Personendaten'][] = $Constant;
                        break;
                    case 'TblPerson_Title':
                        $BlockConstantList['Personendaten'][] = $Constant;
                        break;
                    case 'TblPerson_FirstName':
                        $BlockConstantList['Personendaten'][] = $Constant;
                        break;
                    case 'TblPerson_SecondName':
                        $BlockConstantList['Personendaten'][] = $Constant;
                        break;
                    case 'TblPerson_LastName':
                        $BlockConstantList['Personendaten'][] = $Constant;
                        break;
                    case 'TblCommonInformation_IsAssistance':
                        $BlockConstantList['Mitarbeit'][] = $Constant;
                        break;
                    case 'TblCommonInformation_AssistanceActivity':
                        $BlockConstantList['Mitarbeit'][] = $Constant;
                        break;
                    case 'TblCommon_Remark':
                        $BlockConstantList['Personendaten'][] = $Constant;
                        break;
                }
            }
        }

        return (!empty($BlockConstantList) ? $BlockConstantList : false);
    }
}