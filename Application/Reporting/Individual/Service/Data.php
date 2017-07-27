<?php

namespace SPHERE\Application\Reporting\Individual\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPreset;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblPresetSetting;
use SPHERE\Application\Reporting\Individual\Service\Entity\TblWorkSpace;
use SPHERE\Application\Reporting\Individual\Service\Entity\ViewStudent;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Reporting\Individual\Service
 */
class Data extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {
        // TODO: Implement setupDatabaseContent() method.
    }

    /**
     * @param $Id
     *
     * @return false|TblWorkspace
     */
    public function getWorkspaceById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblWorkspace', $Id);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblWorkSpace[]
     */
    public function getWorkSpaceAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblWorkSpace',
            array(
                TblWorkSpace::ATTR_SERVICE_TBL_ACCOUNT => $tblAccount->getId()
            ));
    }

    /**
     * @param $Id
     *
     * @return false|TblPreset
     */
    public function getPresetById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPreset', $Id);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblWorkSpace[]
     */
    public function gePresetAllByAccount(TblAccount $tblAccount)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPreset', array(
            TblPreset::ATTR_TBL_ACCOUNT => $tblAccount->getId()
        ));
    }

    /**
     * @param $Id
     *
     * @return false|TblPresetSetting
     */
    public function getPresetSettingById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPresetSetting',
            $Id);
    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $Field
     * @param string     $View
     * @param int        $Position
     *
     * @return TblWorkSpace
     */
    public function createWorkSpace(TblAccount $tblAccount, $Field, $View, $Position)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblWorkSpace();
        $Entity->setServiceTblAccount($tblAccount);
        $Entity->setField($Field);
        $Entity->setView($View);
        $Entity->setPosition($Position);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $Name
     *
     * @return TblPreset
     */
    public function createPreset(TblAccount $tblAccount, $Name)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblPreset();
        $Entity->setTblAccount($tblAccount);
        $Entity->setName($Name);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblPreset $tblPreset
     * @param string    $Field
     * @param string    $View
     * @param int       $Position
     *
     * @return TblPresetSetting
     */
    public function createPresetSetting(TblPreset $tblPreset, $Field, $View, $Position)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = new TblPresetSetting();
        $Entity->setTblPreset($tblPreset);
        $Entity->setField($Field);
        $Entity->setView($View);
        $Entity->setPosition($Position);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        return $Entity;
    }

    /**
     * @param TblWorkSpace $tblWorkSpace
     * @param int          $Position
     *
     * @return bool|TblWorkSpace
     */
    public function updateWorkSpacePosition(TblWorkSpace $tblWorkSpace, $Position = 0)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /**
         * @var TblWorkSpace $Protocol
         * @var TblWorkSpace $Entity
         */
        $Entity = $Manager->getEntityById('TblWorkSpace', $tblWorkSpace->getId());
        $Protocol = clone $Entity;
        if ($Entity !== null) {
            $Entity->setPosition($Position);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblPreset $tblPreset
     * @param string    $Name
     *
     * @return bool|TblPreset
     */
    public function updatePreset(TblPreset $tblPreset, $Name)
    {
        $Manager = $this->getConnection()->getEntityManager();

        /**
         * @var TblPreset $Protocol
         * @var TblPreset $Entity
         */
        $Entity = $Manager->getEntityById('TblPreset', $tblPreset->getId());
        $Protocol = clone $Entity;
        if ($Entity !== null) {
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return $Entity;
        }
        return false;
    }

    /**
     * @param TblWorkSpace $tblWorkSpace
     *
     * @return bool
     */
    public function removeWorkSpace(TblWorkSpace $tblWorkSpace)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblWorkSpace $Entity */
        $Entity = $Manager->getEntityById('TblWorkSpace', $tblWorkSpace->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPreset $tblPreset
     *
     * @return bool
     */
    public function removePreset(TblPreset $tblPreset)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPreset $Entity */
        $Entity = $Manager->getEntityById('TblPreset', $tblPreset->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPresetSetting $tblPresetSetting
     *
     * @return bool
     */
    public function removePresetSetting(TblPresetSetting $tblPresetSetting)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblPresetSetting $Entity */
        $Entity = $Manager->getEntityById('TblPresetSetting', $tblPresetSetting->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @return false|\SPHERE\System\Database\Fitting\Element[]|ViewStudent[]
     */
    public function getView()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'ViewStudent');
    }
}
