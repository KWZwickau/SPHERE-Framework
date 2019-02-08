<?php

namespace SPHERE\Application\Billing\Inventory\Setting\Service;

use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSettingGroupPerson;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 * @package SPHERE\Application\Billing\Inventory\Setting\Service
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {
//        //ToDO Vorbefüllung erstellen
        $this->createSetting(TblSetting::IDENT_DEBTOR_NUMBER_COUNT, '7', TblSetting::TYPE_INTEGER);
        $this->createSetting(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED, '1', TblSetting::TYPE_BOOLEAN);
        $this->createSetting(TblSetting::IDENT_IS_SEPA_ACCOUNT_NEED, '1', TblSetting::TYPE_BOOLEAN);

        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        $this->createSettingGroupPerson($tblGroup);
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
        $this->createSettingGroupPerson($tblGroup);
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CUSTODY);
        $this->createSettingGroupPerson($tblGroup);
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_TEACHER);
        $this->createSettingGroupPerson($tblGroup);
//        $tblSetting = $this->createSetting(TblSetting::IDENT_PERSON_GROUP_ACTIVE_LIST, '1;2;3;4;6');
//        $this->updateSetting($tblSetting, '1;2;3;4;6');
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSetting
     */
    public function getSettingById($Id)
    {

        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(), 'TblSetting', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblSettingGroupPerson
     */
    public function getSettingGroupPersonById($Id)
    {

        $Entity = $this->getCachedEntityById(__Method__, $this->getConnection()->getEntityManager(),
            'TblSettingGroupPerson', $Id);
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblSetting
     */
    public function getSettingByIdentifier($Identifier)
    {

        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(), 'TblSetting',
            array(
                TblSetting::ATTR_IDENTIFIER => $Identifier
            ));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool|TblSetting
     */
    public function getSettingGroupPersonByGroup(TblGroup $tblGroup)
    {

        $Entity = $this->getCachedEntityBy(__Method__, $this->getConnection()->getEntityManager(),
            'TblSettingGroupPerson',
            array(
                TblSettingGroupPerson::ATTR_SERVICE_TBL_GROUP_PERSON => $tblGroup->getId()
            ));
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblSetting[]
     */
    public function getSettingAll()
    {

        $Entity = $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(), 'TblSetting');
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @return bool|TblSetting[]
     */
    public function getSettingGroupPersonAll()
    {

        $Entity = $this->getCachedEntityList(__Method__, $this->getConnection()->getEntityManager(),
            'TblSettingGroupPerson');
        return (null === $Entity ? false : $Entity);
    }

    /**
     * @param string $Identifier
     * @param string $Value
     * @param string $Type
     *
     * @return TblSetting
     */
    public function createSetting($Identifier, $Value, $Type = TblSetting::TYPE_STRING)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntity('TblSetting')->findOneBy(array(
            TblSetting::ATTR_IDENTIFIER => $Identifier,
        ));

        if($Entity === null){

            // create if new
            $Entity = new TblSetting();
            $Entity->setIdentifier($Identifier);
            $Entity->setValue($Value);
            $Entity->setType($Type);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return TblSettingGroupPerson
     */
    public function createSettingGroupPerson(TblGroup $tblGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSettingGroupPerson $Entity */
        $Entity = $Manager->getEntity('TblSettingGroupPerson')->findOneBy(array(
            TblSettingGroupPerson::ATTR_SERVICE_TBL_GROUP_PERSON => $tblGroup->getId(),
        ));

        if($Entity === null){

            // create if new
            $Entity = new TblSettingGroupPerson();
            $Entity->setServiceTblGroupPerson($tblGroup);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblSetting $tblSetting
     * @param string     $Value
     *
     * @return TblSetting
     */
    public function updateSetting(TblSetting $tblSetting, $Value)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSetting $Entity */
        $Entity = $Manager->getEntityById('TblSetting', $tblSetting->getId());

        if(null !== $Entity){
            // update if new Value
            if($Entity->getValue() !== $Value){
                $Protocol = clone $Entity;
                $Entity->setValue($Value);

                $Manager->saveEntity($Entity);
                Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(),
                    $Protocol,
                    $Entity);
            }
        }

        return $Entity;
    }

    /**
     * @param TblSettingGroupPerson $tblSettingGroupPerson
     *
     * @return TblSettingGroupPerson
     */
    public function removeSettingGroupPerson(TblSettingGroupPerson $tblSettingGroupPerson)
    {
        //ToDO BulkSave & Kill eventuell sinnvoller. (kommt auf Frontend umsetzung an...)

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSettingGroupPerson $Entity */
        $Entity = $Manager->getEntityById('TblSettingGroupPerson', $tblSettingGroupPerson->getId());

        if($Entity !== null){

            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return TblSettingGroupPerson
     */
    public function removeSettingGroupPersonByGroup(TblGroup $tblGroup)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblSettingGroupPerson $Entity */
        $Entity = $Manager->getEntity('TblSettingGroupPerson')->findOneBy(array(
            TblSettingGroupPerson::ATTR_SERVICE_TBL_GROUP_PERSON => $tblGroup->getId()
        ));

        if($Entity !== null){

            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(),
                $Entity);
        }

        return $Entity;
    }
}