<?php
namespace SPHERE\Application\Billing\Inventory\Setting;

use SPHERE\Application\Billing\Inventory\Setting\Service\Data;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSettingGroupPerson;
use SPHERE\Application\Billing\Inventory\Setting\Service\Setup;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Inventory\Setting
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
     * @return bool|Service\Entity\TblSetting
     */
    public function getSettingById($Id)
    {

        return (new Data($this->getBinding()))->getSettingById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|Service\Entity\TblSettingGroupPerson
     */
    public function getSettingGroupPersonById($Id)
    {

        return (new Data($this->getBinding()))->getSettingGroupPersonById($Id);
    }

    /**
     * @param $Identifier
     *
     * @return bool|Service\Entity\TblSetting
     */
    public function getSettingByIdentifier($Identifier)
    {

        return (new Data($this->getBinding()))->getSettingByIdentifier($Identifier);
    }

    /**
     * @param $Identifier
     *
     * @return bool|Service\Entity\TblSettingGroupPerson
     */
    public function getSettingGroupPersonByGroup($Identifier)
    {

        return (new Data($this->getBinding()))->getSettingGroupPersonByGroup($Identifier);
    }

    /**
     * @return bool|Service\Entity\TblSetting[]
     */
    public function getSettingAll()
    {

        return (new Data($this->getBinding()))->getSettingAll();
    }

    /**
     * @return bool|TblSettingGroupPerson[]
     */
    public function getSettingGroupPersonAll()
    {

        return (new Data($this->getBinding()))->getSettingGroupPersonAll();
    }

    /**
     * @param string $Identifier
     * @param string $Value
     * @param string $Type
     *
     * @return TblSetting
     */
    public function createSetting($Identifier, $Value)
    {

        if(($tblSetting = $this->getSettingByIdentifier($Identifier))){
            // update
            return (new Data($this->getBinding()))->updateSetting($tblSetting, $Value);
        } else {
            // create
            return (new Data($this->getBinding()))->createSetting($Identifier, $Value);
        }
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return TblSettingGroupPerson
     */
    public function createSettingGroupPerson(TblGroup $tblGroup)
    {

        return (new Data($this->getBinding()))->createSettingGroupPerson($tblGroup);
    }

    /**
     * @param TblSettingGroupPerson $tblSettingGroupPerson
     *
     * @return TblSettingGroupPerson
     */
    public function removeSettingGroupPerson(TblSettingGroupPerson $tblSettingGroupPerson)
    {
        // ToDO Löschlogik vergleichen und passende behalten

        return (new Data($this->getBinding()))->removeSettingGroupPerson($tblSettingGroupPerson);
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return TblSettingGroupPerson
     */
    public function removeSettingGroupPersonByGroup(TblGroup $tblGroup)
    {

        return (new Data($this->getBinding()))->removeSettingGroupPersonByGroup($tblGroup);
    }
}