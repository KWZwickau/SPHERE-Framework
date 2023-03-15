<?php
namespace SPHERE\Application\Contact\Address;

use SPHERE\Application\Contact\Address\Service\Data;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity;
use SPHERE\Application\Contact\Address\Service\Entity\TblRegion;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Address\Service\Entity\TblType;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\ViewAddressToPerson;
use SPHERE\Application\Contact\Address\Service\Setup;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Contact\Address
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewAddressToPerson[]
     */
    public function viewAddressToPersonAll()
    {

        return (new Data($this->getBinding()))->viewAddressToPersonAll();
    }

    /**
     * @return false|ViewAddressToCompany[]
     */
    public function viewAddressToCompanyAll()
    {

        return (new Data($this->getBinding()))->viewAddressToCompanyAll();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCity
     */
    public function getCityById($Id)
    {

        return (new Data($this->getBinding()))->getCityById($Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAddress
     */
    public function getAddressById($Id)
    {

        return (new Data($this->getBinding()))->getAddressById($Id);
    }

    /**
     * @return bool|TblCity[]
     */
    public function getCityAll()
    {

        return (new Data($this->getBinding()))->getCityAll();
    }

    /**
     * @return bool|TblRegion[]
     */
    public function getRegionAll()
    {

        return (new Data($this->getBinding()))->getRegionAll();
    }

    /**
     * @return bool|TblState[]
     */
    public function getStateAll()
    {

        return (new Data($this->getBinding()))->getStateAll();
    }

    /**
     * @param string $Name
     *
     * @return bool|TblState
     */
    public function getStateByName($Name)
    {

        return (new Data($this->getBinding()))->getStateByName($Name);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblRegion
     */
    public function getRegionListByName($Name)
    {

        return (new Data($this->getBinding()))->getRegionListByName($Name);
    }

    /**
     * @param string $Code
     *
     * @return bool|TblRegion[]
     */
    public function getRegionListByCode($Code)
    {

        return (new Data($this->getBinding()))->getRegionListByCode($Code);
    }

    /**
     * @param $Code
     *
     * @return string
     */
    public function getRegionStringByCode($Code)
    {

        $tblRegionList = (new Data($this->getBinding()))->getRegionListByCode($Code);
        if($tblRegionList){
            if(count($tblRegionList) == 1){
                return current($tblRegionList)->getName();
            } else {
                $NameList = array();
                foreach($tblRegionList as $tblRegion){
                    $NameList[] = $tblRegion->getName();
                }
                sort($NameList);
                return implode(', ',$NameList);
            }
        }
        return '';
    }

    /**
     * @return bool|TblAddress[]
     */
    public function getAddressAll()
    {

        return (new Data($this->getBinding()))->getAddressAll();
    }

    /**
     * @return false|ViewAddressToPerson[]
     */
    public function getViewAddressToPersonAll()
    {

        return (new Data($this->getBinding()))->getViewAddressToPersonAll();
    }

    /**
     * @return false|ViewAddressToCompany[]
     */
    public function getViewAddressToCompanyAll()
    {

        return (new Data($this->getBinding()))->getViewAddressToCompanyAll();
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->getBinding()))->getTypeAll();
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Street
     * @param $City
     * @param $Type
     * @param $OnlineContactId
     * @param TblToPerson|null $tblToPerson
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormAddressToPerson(
        TblPerson $tblPerson,
        $Street,
        $City,
        $Type,
        $OnlineContactId,
        TblToPerson $tblToPerson = null
    ) {

        $error = false;
        if (($tblType = $this->getTypeById($Type['Type']))
            && $tblType->getName() == 'Hauptadresse'
        ) {
            $showRelationships = true;
        } else {
            $showRelationships = false;
        }

        $form = Address::useFrontend()->formAddressToPerson($tblPerson->getId(), $tblToPerson ? $tblToPerson->getId() : null, false, $showRelationships, $OnlineContactId);
        if (isset($Street['Name']) && empty($Street['Name'])) {
            $form->setError('Street[Name]', 'Bitte geben Sie eine Strasse an');
            $error = true;
        } else {
            $form->setSuccess('Street[Name]');
        }
        if (isset($Street['Number']) && empty($Street['Number'])) {
            $form->setError('Street[Number]', 'Bitte geben Sie eine Hausnummer an');
            $error = true;
        } else {
            $form->setSuccess('Street[Number]');
        }

        if (isset($City['Code']) && empty($City['Code'])) {
            $form->setError('City[Code]', 'Bitte geben Sie eine Postleitzahl ein');
            $error = true;
        } else {
            $form->setSuccess('City[Code]');
        }
        if (isset($City['Name']) && empty($City['Name'])) {
            $form->setError('City[Name]', 'Bitte geben Sie einen Namen ein');
            $error = true;
        } else {
            $form->setSuccess('City[Name]');
        }
        if (!$tblType) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ ein');
            $error = true;
        } else {
            // control there is no other MainAddress
            if ($tblType->getName() == 'Hauptadresse') {
                if ($tblToPerson && ($tblAddress = $tblToPerson->getTblAddress())) {
                    $tblAddressMain = $tblPerson->fetchMainAddress();
                    if ($tblAddressMain && (($tblAddress && $tblAddress->getId() != $tblAddressMain->getId()) || !$tblAddress)) {
                        $form->setError('Type[Type]', '"' . trim($tblPerson->getFullName())
                            . '" besitzt bereits eine Hauptadresse');
                        $error = true;
                    }
                } else {
                    if (($tblAddressMain = $tblPerson->fetchMainAddress())) {
                        $form->setError('Type[Type]', '"' . trim($tblPerson->getFullName())
                            . '" besitzt bereits eine Hauptadresse');
                        $error = true;
                    }
                }
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Street
     * @param $City
     * @param $Type
     * @param TblToCompany|null $tblToCompany
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormAddressToCompany(
        TblCompany $tblCompany,
        $Street,
        $City,
        $Type,
        TblToCompany $tblToCompany = null
    ) {

        $error = false;
        $form = Address::useFrontend()->formAddressToCompany($tblCompany->getId(), $tblToCompany ? $tblToCompany->getId() : null);
        if (isset($Street['Name']) && empty($Street['Name'])) {
            $form->setError('Street[Name]', 'Bitte geben Sie eine Strasse an');
            $error = true;
        } else {
            $form->setSuccess('Street[Name]');
        }
        if (isset($Street['Number']) && empty($Street['Number'])) {
            $form->setError('Street[Number]', 'Bitte geben Sie eine Hausnummer an');
            $error = true;
        } else {
            $form->setSuccess('Street[Number]');
        }

        if (isset($City['Code']) && empty($City['Code'])) {
            $form->setError('City[Code]', 'Bitte geben Sie eine Postleitzahl ein');
            $error = true;
        } else {
            $form->setSuccess('City[Code]');
        }
        if (isset($City['Name']) && empty($City['Name'])) {
            $form->setError('City[Name]', 'Bitte geben Sie einen Namen ein');
            $error = true;
        } else {
            $form->setSuccess('City[Name]');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ ein');
            $error = true;
        } else {
            // control there is no other MainAddress
            if ($tblType->getName() == 'Hauptadresse') {
                if ($tblToCompany && ($tblAddress = $tblToCompany->getTblAddress())) {
                    $tblAddressMain = $tblCompany->fetchMainAddress();
                    if ($tblAddressMain && (($tblAddress && $tblAddress->getId() != $tblAddressMain->getId()) || !$tblAddress)) {
                        $form->setError('Type[Type]', '"' . trim($tblCompany->getDisplayName())
                            . '" besitzt bereits eine Hauptadresse');
                        $error = true;
                    }
                } else {
                    if (($tblAddressMain = $tblCompany->fetchMainAddress())) {
                        $form->setError('Type[Type]', '"' . trim($tblCompany->getDisplayName())
                            . '" besitzt bereits eine Hauptadresse');
                        $error = true;
                    }
                }
            }
        }

        return $error ? $form : false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param array     $Street
     * @param array     $City
     * @param integer   $State
     * @param array     $Type
     * @param string    $County
     * @param string    $Nation
     *
     * @return IFormInterface|string|TblToPerson
     */
    public function createAddressToPersonByApi(
        TblPerson $tblPerson,
        array $Street,
        array $City,
        int $State,
        array $Type,
        string $County,
        string $Nation
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        if ($tblType) {
            if ($State) {
                $tblState = $this->getStateById($State);
            } else {
                $tblState = null;
            }
            $tblCity = (new Data($this->getBinding()))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->getBinding()))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], '', '', $County, $Nation
            );

            if ($tblType->getName() == 'Hauptadresse'
                && $tblToPersonList = Address::useService()->getAddressAllByPersonAndType($tblPerson, $tblType)
            ) {
                $tblToPerson = current($tblToPersonList);
                if ($tblToPerson->getServiceTblPerson()) {
                    // Update current if exist
                    if ((new Data($this->getBinding()))->updateAddressToPerson(
                        $tblToPerson,
                        $tblAddress,
                        $tblType,
                        $Type['Remark'])
                    ) {
                        return true;
                    }
                }
            }
            // Create if not exist
            if ((new Data($this->getBinding()))->addAddressToPerson($tblPerson, $tblAddress, $tblType,
                $Type['Remark'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param $Street
     * @param $City
     * @param $State
     * @param $Type
     * @param $Region
     * @param $County
     * @param $Nation
     *
     * @return bool
     */
    public function updateAddressToPersonByApi(
        TblToPerson $tblToPerson,
        $Street,
        $City,
        $State,
        $Type,
        $Region,
        $County,
        $Nation
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        if ($tblType) {
            $tblState = $this->getStateById($State);
            if (!$tblState) {
                $tblState = null;
            }
            $tblCity = (new Data($this->getBinding()))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->getBinding()))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], '', $Region, $County, $Nation
            );
            if ($tblToPerson->getServiceTblPerson()) {
                // Update current
                if (( new Data($this->getBinding()) )->updateAddressToPerson(
                    $tblToPerson,
                    $tblAddress,
                    $tblType,
                    $Type['Remark'])
                ) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param array     $Street
     * @param array     $City
     * @param integer   $State
     * @param array     $Type
     * @param string    $County
     * @param string    $Nation
     *
     * @return IFormInterface|string|TblToCompany
     */
    public function createAddressToCompanyByApi(
        TblCompany $tblCompany,
        array $Street,
        array $City,
        int $State,
        array $Type,
        string $County,
        string $Nation
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        if ($tblType) {
            if ($State) {
                $tblState = $this->getStateById($State);
            } else {
                $tblState = null;
            }
            $tblCity = (new Data($this->getBinding()))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->getBinding()))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], '', '', $County, $Nation
            );

            if ($tblType->getName() == 'Hauptadresse'
                && $tblToCompanyList = Address::useService()->getAddressAllByCompanyAndType($tblCompany, $tblType)
            ) {
                $tblToCompany = current($tblToCompanyList);
                if ($tblToCompany->getServiceTblCompany()) {
                    // Update current if exist
                    if ((new Data($this->getBinding()))->updateAddressToCompany(
                        $tblToCompany,
                        $tblAddress,
                        $tblType,
                        $Type['Remark'])
                    ) {
                        return true;
                    }
                }
            }
            // Create if not exist
            if ((new Data($this->getBinding()))->addAddressToCompany($tblCompany, $tblAddress, $tblType,
                $Type['Remark'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param TblToCompany $tblToCompany
     * @param $Street
     * @param $City
     * @param $State
     * @param $Type
     * @param $County
     * @param $Nation
     *
     * @return bool
     */
    public function updateAddressToCompanyByApi(
        TblToCompany $tblToCompany,
        $Street,
        $City,
        $State,
        $Type,
        $County,
        $Nation
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        if ($tblType) {
            $tblState = $this->getStateById($State);
            if (!$tblState) {
                $tblState = null;
            }
            $tblCity = (new Data($this->getBinding()))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->getBinding()))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], '', '', $County, $Nation
            );
            if ($tblToCompany->getServiceTblCompany()) {
                // Update current
                if (( new Data($this->getBinding()) )->updateAddressToCompany(
                    $tblToCompany,
                    $tblAddress,
                    $tblType,
                    $Type['Remark'])
                ) {
                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * @param array  $ProcessList
     * @param string $CityName
     *
     * @return bool
     */
    public function updateAddressAnonymousBulk(
        $ProcessList = array(),
        $CityName = ''
    ) {

        (new Data($this->getBinding()))->updateCityAnonymousBulk($ProcessList, $CityName);
        return (new Data($this->getBinding()))->updateAddressAnonymousBulk($ProcessList);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById($Id)
    {

        return (new Data($this->getBinding()))->getTypeById($Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblType
     */
    public function getTypeByName($Name)
    {

        return (new Data($this->getBinding()))->getTypeByName($Name);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblState
     */
    public function getStateById($Id)
    {

        return (new Data($this->getBinding()))->getStateById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $StreetName
     * @param string $StreetNumber
     * @param string $CityCode
     * @param string $CityName
     * @param string $CityDistrict
     * @param string $PostOfficeBox
     * @param string $County
     * @param string $Nation
     * @param TblState $tblState
     * @param string $Region
     *
     * @return TblToPerson
     */
    public function insertAddressToPerson(
        TblPerson $tblPerson,
        string $StreetName,
        string $StreetNumber,
        string $CityCode,
        string $CityName,
        string $CityDistrict = '',
        string $PostOfficeBox = '',
        string $County = '',
        string $Nation = '',
        TblState $tblState = null,
        string $Region = ''
    ) {

        $tblCity = (new Data($this->getBinding()))->createCity($CityCode, $CityName, $CityDistrict);
        $tblAddress = (new Data($this->getBinding()))->createAddress(
            $tblState,
            $tblCity,
            $StreetName,
            $StreetNumber,
            $PostOfficeBox,
            $Region,
            $County,
            $Nation
        );

        return (new Data($this->getBinding()))->addAddressToPerson($tblPerson, $tblAddress,
            Address::useService()->getTypeById(1), '');
    }

    /**
     * @param TblPerson  $tblPerson
     * @param TblAddress $tblAddress
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToPerson
     */
    public function addAddressToPerson(TblPerson $tblPerson, TblAddress $tblAddress, TblType $tblType, $Remark)
    {

        return (new Data($this->getBinding()))->addAddressToPerson(
            $tblPerson,
            $tblAddress,
            $tblType,
            $Remark
        );
    }

    /**
     * @param TblCompany $tblCompany
     * @param string $StreetName
     * @param string $StreetNumber
     * @param string $CityCode
     * @param string $CityName
     * @param string $CityDistrict
     * @param string $PostOfficeBox
     * @param string $County
     * @param string $Nation
     *
     * @return TblToCompany
     */
    public function insertAddressToCompany(
        TblCompany $tblCompany,
        string $StreetName,
        string $StreetNumber,
        string $CityCode,
        string $CityName,
        string $CityDistrict = '',
        string $PostOfficeBox = '',
        string $County = '',
        string $Nation = ''
    ) {

        $tblCity = (new Data($this->getBinding()))->createCity($CityCode, $CityName, $CityDistrict);
        $tblState = null;

        $tblAddress = (new Data($this->getBinding()))->createAddress(
            $tblState,
            $tblCity,
            $StreetName,
            $StreetNumber,
            $PostOfficeBox,
            '',
            $County,
            $Nation
        );

        $tblType = Address::useService()->getTypeById(1);
        // Nur eine Hauptadresse
        if ($this->getAddressAllByCompanyAndType($tblCompany, $tblType)) {
            return ( new Data($this->getBinding()) )->addAddressToCompany($tblCompany, $tblAddress,
                Address::useService()->getTypeById(2), '');
        }
        return ( new Data($this->getBinding()) )->addAddressToCompany($tblCompany, $tblAddress, $tblType, '');
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getAddressAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getAddressAllByPerson($tblPerson, $isForced);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType   $tblType
     *
     * @return bool|TblToPerson[]
     */
    public function getAddressAllByPersonAndType(TblPerson $tblPerson, TblType $tblType)
    {

        return (new Data($this->getBinding()))->getAddressAllByPersonAndType($tblPerson, $tblType);
    }


    /** get Main Address (Type ID 1)
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblAddress
     */
    public function getAddressByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getAddressByPerson($tblPerson, $isForced);
    }


    /** get Deliver Address (Type ID 4) else Main Address (Type ID 1)
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblAddress
     */
    public function getInvoiceAddressByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getInvoiceAddressByPerson($tblPerson, $isForced);
    }

    /** get Main Address (Type ID 1)
     *
     * @param TblPerson $tblPerson
     *
     * @return false|TblToPerson
     */
    public function getAddressToPersonByPerson(TblPerson $tblPerson)
    {

        return ( new Data($this->getBinding()) )->getAddressToPersonByPerson($tblPerson);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblAddress
     */
    public function getAddressByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getAddressByCompany($tblCompany);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getAddressAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getAddressAllByCompany($tblCompany);
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblType    $tblType
     *
     * @return bool|TblToCompany[]
     */
    public function getAddressAllByCompanyAndType(TblCompany $tblCompany, TblType $tblType)
    {

        return (new Data($this->getBinding()))->getAddressAllByCompanyAndType($tblCompany, $tblType);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getAddressToPersonById($Id)
    {

        return (new Data($this->getBinding()))->getAddressToPersonById($Id);
    }


    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getAddressToCompanyById($Id)
    {

        return (new Data($this->getBinding()))->getAddressToCompanyById($Id);
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeAddressToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->removeAddressToPerson($tblToPerson, $IsSoftRemove);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeAddressToCompany(TblToCompany $tblToCompany)
    {

        return (new Data($this->getBinding()))->removeAddressToCompany($tblToCompany);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return array of TblAddress->Id
     */
    public function fetchIdAddressAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->fetchIdAddressAllByPerson($tblPerson);
    }

    /**
     * @param array $IdArray of TblAddress->Id
     *
     * @return TblAddress[]
     */
    public function fetchAddressAllByIdList($IdArray)
    {

        return (new Data($this->getBinding()))->fetchAddressAllByIdList($IdArray);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removeAddressAllByPerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblAddressToPersonList = $this->getAddressAllByPerson($tblPerson))){
            foreach($tblAddressToPersonList as $tblToPerson){
                $this->removeAddressToPerson($tblToPerson, $IsSoftRemove);
            }
        }
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function restoreToPerson(TblToPerson $tblToPerson)
    {

        return (new Data($this->getBinding()))->restoreToPerson($tblToPerson);
    }

    /**
     * @param TblType $tblType
     * @param $StreetName
     * @param $StreetNumber
     * @param $CityCode
     * @param $CityName
     * @param $CityDistrict
     * @param $County
     * @param $Nation
     * @param array $tblPersonList
     * @param TblState|null $tblState
     * @param string $Remark
     *
     * @return bool
     */
    public function insertAddressToPersonList(
        TblType $tblType,
        $StreetName,
        $StreetNumber,
        $CityCode,
        $CityName,
        $CityDistrict,
        $Region,
        $County,
        $Nation,
        $tblPersonList = array(),
        TblState $tblState = null,
        $Remark = ''
    ) {
        if (($tblCity = (new Data($this->getBinding()))->createCity($CityCode, $CityName, $CityDistrict))
            && ($tblAddress = (new Data($this->getBinding()))->createAddress(
                $tblState,
                $tblCity,
                $StreetName,
                $StreetNumber,
                '',
                $Region,
                $County,
                $Nation
            ))
        ) {
            foreach ($tblPersonList as $tblPerson) {
                (new Data($this->getBinding()))->addAddressToPerson($tblPerson, $tblAddress, $tblType, $Remark);
            }
        }

        return true;
    }

    /**
     * @param TblAddress $tblAddress
     *
     * @return false|TblToPerson[]
     */
    public function getToPersonAllByAddress(TblAddress $tblAddress)
    {
        return (new Data($this->getBinding()))->getToPersonAllByAddress($tblAddress);
    }

    /**
     * @param TblAddress $tblAddress
     *
     * @return false|TblPerson[]
     */
    public function getPersonAllByAddress(TblAddress $tblAddress)
    {
        $result = array();
        if (($tblToPersonList = $this->getToPersonAllByAddress($tblAddress))) {
            foreach ($tblToPersonList as $tblToPerson) {
                if (($tblPerson = $tblToPerson->getServiceTblPerson())) {
                    $result[$tblPerson->getId()] = $tblPerson;
                }
            }
        }

        return empty($result) ? false : $result;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblAddress $tblAddress
     *
     * @return false|TblToPerson
     */
    public function getAddressToPersonByPersonAndAddress(TblPerson $tblPerson, TblAddress $tblAddress)
    {
        return (new Data($this->getBinding()))->getAddressToPersonByPersonAndAddress($tblPerson, $tblAddress);
    }

    /**
     * @param string $StreetName
     * @param string $StreetNumber
     * @param string $CityCode
     * @param string $CityName
     * @param string $CityDistrict
     * @param string $PostOfficeBox
     * @param string $Region
     * @param string $County
     * @param string $Nation
     * @param TblState|null $tblState
     *
     * @return false|TblAddress
     */
    public function insertAddress(
        string $StreetName,
        string $StreetNumber,
        string $CityCode,
        string $CityName,
        string $CityDistrict,
        string $PostOfficeBox = '',
        string $Region = '',
        string $County = '',
        string $Nation = '',
        TblState $tblState = null
    ) {
        if (($tblCity = (new Data($this->getBinding()))->createCity($CityCode, $CityName, $CityDistrict))) {
            return (new Data($this->getBinding()))->createAddress(
                $tblState,
                $tblCity,
                $StreetName,
                $StreetNumber,
                $PostOfficeBox,
                $Region,
                $County,
                $Nation
            );
        }

        return false;
    }
}
