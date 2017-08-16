<?php

namespace SPHERE\Application\Reporting\Individual;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Reporting\Individual\Service\Data;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPresetSetting;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;
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
     * @param string         $Field
     * @param string         $View
     * @param int            $Position
     *
     * @param TblPreset|null $tblPreset
     *
     * @return bool|TblWorkSpace
     */
    public function addWorkSpaceField($Field, $View, $Position, TblPreset $tblPreset = null)
    {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            return (new Data($this->getBinding()))->createWorkSpace($tblAccount, $Field, $View, $Position, $tblPreset);
        }
        return false;
    }

    /**
     * @param TblWorkSpace $tblWorkSpace
     * @param int|null     $Position
     *
     * @return mixed
     */
    public function changeWorkSpace(TblWorkSpace $tblWorkSpace, $Position = null)
    {

        return (new Data($this->getBinding()))->changeWorkSpace($tblWorkSpace, $Position);
    }

    /**
     * @param TblWorkSpace $tblWorkSpace
     *
     * @return bool
     */
    public function removeWorkSpace(TblWorkSpace $tblWorkSpace)
    {

        return (new Data($this->getBinding()))->removeWorkSpace($tblWorkSpace);
    }

    public function removeWorkSpaceAll()
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
}