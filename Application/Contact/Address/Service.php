<?php
namespace SPHERE\Application\Contact\Address;

use SPHERE\Application\Contact\Address\Service\Data;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Address\Service\Entity\TblType;
use SPHERE\Application\Contact\Address\Service\Setup;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Contact\Address
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
     * @return bool|TblState[]
     */
    public function getStateAll()
    {

        return (new Data($this->getBinding()))->getStateAll();
    }

    /**
     * @return bool|TblAddress[]
     */
    public function getAddressAll()
    {

        return (new Data($this->getBinding()))->getAddressAll();
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->getBinding()))->getTypeAll();
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param array          $Street
     * @param array          $City
     * @param integer        $State
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createAddressToPerson(
        IFormInterface $Form,
        TblPerson $tblPerson,
        $Street,
        $City,
        $State,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Street
            && null === $City
            && null === $State
        ) {
            return $Form;
        }

        $Error = false;

        if (isset( $Street['Name'] ) && empty( $Street['Name'] )) {
            $Form->setError('Street[Name]', 'Bitte geben Sie eine Strasse an');
            $Error = true;
        } else {
            $Form->setSuccess('Street[Number]');
        }
        if (isset( $Street['Number'] ) && empty( $Street['Number'] )) {
            $Form->setError('Street[Number]', 'Bitte geben Sie eine Hausnummer an');
            $Error = true;
        } else {
            $Form->setSuccess('Street[Number]');
        }

        if (isset( $City['Code'] ) && !preg_match('!^[0-9]{5}$!is', $City['Code'])) {
            $Form->setError('City[Code]', 'Bitte geben Sie eine fünfstellige Postleitzahl ein');
            $Error = true;
        } else {
            $Form->setSuccess('City[Code]');
        }
        if (isset( $City['Name'] ) && empty( $City['Name'] )) {
            $Form->setError('City[Name]', 'Bitte geben Sie einen Namen ein');
            $Error = true;
        } else {
            $Form->setSuccess('City[Name]');
        }

        if (!$Error) {

            $tblType = $this->getTypeById($Type['Type']);
            if ($State) {
                $tblState = $this->getStateById($State);
            } else {
                $tblState = null;
            }
            $tblCity = (new Data($this->getBinding()))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->getBinding()))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], ''
            );

            if ((new Data($this->getBinding()))->addAddressToPerson($tblPerson, $tblAddress, $tblType,
                $Type['Remark'])
            ) {
                return new Success('Die Adresse wurde erfolgreich hinzugefügt')
                .new Redirect('/People/Person', 1,
                    array('Id' => $tblPerson->getId()));
            } else {
                return new Danger('Die Adresse konnte nicht hinzugefügt werden')
                .new Redirect('/People/Person', 10,
                    array('Id' => $tblPerson->getId()));
            }
        }
        return $Form;
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
     * @param           $StreetName
     * @param           $StreetNumber
     * @param           $CityCode
     * @param           $CityName
     * @param           $CityDistrict
     * @param           $PostOfficeBox
     * @param           $State
     *
     * @return TblToPerson
     */
    public function insertAddressToPerson(
        TblPerson $tblPerson,
        $StreetName,
        $StreetNumber,
        $CityCode,
        $CityName,
        $CityDistrict,
        $PostOfficeBox,
        $State
    ) {

        $tblCity = (new Data($this->getBinding()))->createCity($CityCode, $CityName, $CityDistrict);
        $tblState = null;

        $tblAddress = (new Data($this->getBinding()))->createAddress(
            $tblState,
            $tblCity,
            $StreetName,
            $StreetNumber,
            $PostOfficeBox
        );

        return (new Data($this->getBinding()))->addAddressToPerson($tblPerson, $tblAddress,
            Address::useService()->getTypeById(1), '');
    }

    /**
     * @param IFormInterface $Form
     * @param TblToPerson    $tblToPerson
     * @param array          $Street
     * @param array          $City
     * @param int            $State
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updateAddressToPerson(
        IFormInterface $Form,
        TblToPerson $tblToPerson,
        $Street,
        $City,
        $State,
        $Type
    )
    {

        /**
         * Skip to Frontend
         */
        if (null === $Street
            && null === $City
            && null === $State
        ) {
            return $Form;
        }

        $Error = false;

        if (isset( $Street['Name'] ) && empty( $Street['Name'] )) {
            $Form->setError('Street[Name]', 'Bitte geben Sie eine Strasse an');
            $Error = true;
        } else {
            $Form->setSuccess('Street[Number]');
        }
        if (isset( $Street['Number'] ) && empty( $Street['Number'] )) {
            $Form->setError('Street[Number]', 'Bitte geben Sie eine Hausnummer an');
            $Error = true;
        } else {
            $Form->setSuccess('Street[Number]');
        }

        if (isset( $City['Code'] ) && !preg_match('!^[0-9]{5}$!is', $City['Code'])) {
            $Form->setError('City[Code]', 'Bitte geben Sie eine fünfstellige Postleitzahl ein');
            $Error = true;
        } else {
            $Form->setSuccess('City[Code]');
        }
        if (isset( $City['Name'] ) && empty( $City['Name'] )) {
            $Form->setError('City[Name]', 'Bitte geben Sie einen Namen ein');
            $Error = true;
        } else {
            $Form->setSuccess('City[Name]');
        }

        if (!$Error) {

            $tblType = $this->getTypeById($Type['Type']);
            $tblState = $this->getStateById($State);
            if(!$tblState)
            {
                $tblState = null;
            }
            $tblCity = (new Data($this->getBinding()))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->getBinding()))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], ''
            );
            // Remove current
            (new Data($this->getBinding()))->removeAddressToPerson($tblToPerson);
            // Add new
            if ((new Data($this->getBinding()))->addAddressToPerson($tblToPerson->getServiceTblPerson(), $tblAddress,
                $tblType,
                $Type['Remark'])
            ) {
                return new Success('Die Adresse wurde erfolgreich geändert')
                .new Redirect('/People/Person', 1,
                    array('Id' => $tblToPerson->getServiceTblPerson()->getId()));
            } else {
                return new Danger('Die Adresse konnte nicht geändert werden')
                .new Redirect('/People/Person', 10,
                    array('Id' => $tblToPerson->getServiceTblPerson()->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblToCompany   $tblToCompany
     * @param array          $Street
     * @param array          $City
     * @param int            $State
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function updateAddressToCompany(
        IFormInterface $Form,
        TblToCompany $tblToCompany,
        $Street,
        $City,
        $State,
        $Type
    )
    {

        /**
         * Skip to Frontend
         */
        if (null === $Street
            && null === $City
            && null === $State
        ) {
            return $Form;
        }

        $Error = false;

        if (isset( $Street['Name'] ) && empty( $Street['Name'] )) {
            $Form->setError('Street[Name]', 'Bitte geben Sie eine Strasse an');
            $Error = true;
        } else {
            $Form->setSuccess('Street[Number]');
        }
        if (isset( $Street['Number'] ) && empty( $Street['Number'] )) {
            $Form->setError('Street[Number]', 'Bitte geben Sie eine Hausnummer an');
            $Error = true;
        } else {
            $Form->setSuccess('Street[Number]');
        }

        if (isset( $City['Code'] ) && !preg_match('!^[0-9]{5}$!is', $City['Code'])) {
            $Form->setError('City[Code]', 'Bitte geben Sie eine fünfstellige Postleitzahl ein');
            $Error = true;
        } else {
            $Form->setSuccess('City[Code]');
        }
        if (isset( $City['Name'] ) && empty( $City['Name'] )) {
            $Form->setError('City[Name]', 'Bitte geben Sie einen Namen ein');
            $Error = true;
        } else {
            $Form->setSuccess('City[Name]');
        }

        if (!$Error) {

            $tblType = $this->getTypeById($Type['Type']);
            $tblState = $this->getStateById($State);
            $tblCity = (new Data($this->getBinding()))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->getBinding()))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], ''
            );
            // Remove current
            (new Data($this->getBinding()))->removeAddressToCompany($tblToCompany);
            // Add new
            if ((new Data($this->getBinding()))->addAddressToCompany($tblToCompany->getServiceTblCompany(), $tblAddress,
                $tblType,
                $Type['Remark'])
            ) {
                return new Success('Die Adresse wurde erfolgreich geändert')
                .new Redirect('/Corporation/Company', 1,
                    array('Id' => $tblToCompany->getServiceTblCompany()->getId()));
            } else {
                return new Danger('Die Adresse konnte nicht geändert werden')
                .new Redirect('/Corporation/Company', 10,
                    array('Id' => $tblToCompany->getServiceTblCompany()->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param IFormInterface $Form
     * @param TblCompany     $tblCompany
     * @param array          $Street
     * @param array          $City
     * @param integer        $State
     * @param array          $Type
     *
     * @return IFormInterface|string
     */
    public function createAddressToCompany(
        IFormInterface $Form,
        TblCompany $tblCompany,
        $Street,
        $City,
        $State,
        $Type
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Street
            && null === $City
            && null === $State
        ) {
            return $Form;
        }

        $Error = false;

        if (isset( $Street['Name'] ) && empty( $Street['Name'] )) {
            $Form->setError('Street[Name]', 'Bitte geben Sie eine Strasse an');
            $Error = true;
        } else {
            $Form->setSuccess('Street[Number]');
        }
        if (isset( $Street['Number'] ) && empty( $Street['Number'] )) {
            $Form->setError('Street[Number]', 'Bitte geben Sie eine Hausnummer an');
            $Error = true;
        } else {
            $Form->setSuccess('Street[Number]');
        }

        if (isset( $City['Code'] ) && !preg_match('!^[0-9]{5}$!is', $City['Code'])) {
            $Form->setError('City[Code]', 'Bitte geben Sie eine fünfstellige Postleitzahl ein');
            $Error = true;
        } else {
            $Form->setSuccess('City[Code]');
        }
        if (isset( $City['Name'] ) && empty( $City['Name'] )) {
            $Form->setError('City[Name]', 'Bitte geben Sie einen Namen ein');
            $Error = true;
        } else {
            $Form->setSuccess('City[Name]');
        }

        if (!$Error) {

            $tblType = $this->getTypeById($Type['Type']);
            $tblState = $this->getStateById($State);
            $tblCity = (new Data($this->getBinding()))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->getBinding()))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], ''
            );

            if ((new Data($this->getBinding()))->addAddressToCompany($tblCompany, $tblAddress, $tblType,
                $Type['Remark'])
            ) {
                return new Success('Die Adresse wurde erfolgreich hinzugefügt')
                .new Redirect('/Corporation/Company', 1,
                    array('Id' => $tblCompany->getId()));
            } else {
                return new Danger('Die Adresse konnte nicht hinzugefügt werden')
                .new Redirect('/Corporation/Company', 10,
                    array('Id' => $tblCompany->getId()));
            }
        }
        return $Form;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getAddressAllByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getAddressAllByPerson($tblPerson);
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
     *
     * @return bool
     */
    public function removeAddressToPerson(TblToPerson $tblToPerson)
    {

        return (new Data($this->getBinding()))->removeAddressToPerson($tblToPerson);
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
}
