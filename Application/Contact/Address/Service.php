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
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\Contact\Address
 */
class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
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

        return (new Data($this->Binding))->getCityById($Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAddress
     */
    public function getAddressById($Id)
    {

        return (new Data($this->Binding))->getAddressById($Id);
    }

    /**
     * @return bool|TblCity[]
     */
    public function getCityAll()
    {

        return (new Data($this->Binding))->getCityAll();
    }

    /**
     * @return bool|TblState[]
     */
    public function getStateAll()
    {

        return (new Data($this->Binding))->getStateAll();
    }

    /**
     * @return bool|TblAddress[]
     */
    public function getAddressAll()
    {

        return (new Data($this->Binding))->getAddressAll();
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->Binding))->getTypeAll();
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
            $tblState = $this->getStateById($State);
            $tblCity = (new Data($this->Binding))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->Binding))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], ''
            );

            if ((new Data($this->Binding))->addAddressToPerson($tblPerson, $tblAddress, $tblType,
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

        return (new Data($this->Binding))->getTypeById($Id);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblState
     */
    public function getStateById($Id)
    {

        return (new Data($this->Binding))->getStateById($Id);
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
            $tblCity = (new Data($this->Binding))->createCity(
                $City['Code'], $City['Name'], $City['District']
            );
            $tblAddress = (new Data($this->Binding))->createAddress(
                $tblState, $tblCity, $Street['Name'], $Street['Number'], ''
            );

            if ((new Data($this->Binding))->addAddressToCompany($tblCompany, $tblAddress, $tblType,
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

        return (new Data($this->Binding))->getAddressAllByPerson($tblPerson);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getAddressAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->Binding))->getAddressAllByCompany($tblCompany);
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getAddressToPersonById($Id)
    {

        return (new Data($this->Binding))->getAddressToPersonById($Id);
    }


    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getAddressToCompanyById($Id)
    {

        return (new Data($this->Binding))->getAddressToCompanyById($Id);
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function removeAddressToPerson(TblToPerson $tblToPerson)
    {

        return (new Data($this->Binding))->removeAddressToPerson($tblToPerson);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeAddressToCompany(TblToCompany $tblToCompany)
    {

        return (new Data($this->Binding))->removeAddressToCompany($tblToCompany);
    }
}
