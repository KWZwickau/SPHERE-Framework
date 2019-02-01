<?php
namespace SPHERE\Application\Contact\Phone;

use SPHERE\Application\Contact\Phone\Service\Data;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
use SPHERE\Application\Contact\Phone\Service\Entity\ViewPhoneToPerson;
use SPHERE\Application\Contact\Phone\Service\Setup;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Contact\Phone
 */
class Service extends AbstractService
{

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
     * @return false|ViewPhoneToPerson[]
     */
    public function viewPhoneToPerson()
    {

        return ( new Data($this->getBinding()) )->viewPhoneToPerson();
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPhone
     */
    public function getPhoneById($Id)
    {

        return (new Data($this->getBinding()))->getPhoneById($Id);
    }

    /**
     * @return bool|TblPhone[]
     */
    public function getPhoneAll()
    {

        return (new Data($this->getBinding()))->getPhoneAll();
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
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getPhoneAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getPhoneAllByPerson($tblPerson, $isForced);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getPhoneAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getPhoneAllByCompany($tblCompany);
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return string
     */
    public function getPhoneTypeShort(TblToPerson $tblToPerson)
    {

        $tblType = $tblToPerson->getTblType();
        if ($tblType) {
            if ($tblType->getName() == 'Privat') {
                return 'p.';
            } elseif ($tblType->getName() == 'Geschäftlich') {
                return 'd.';
            } elseif ($tblType->getName() == 'Notfall') {
                return 'N.';
            } elseif ($tblType->getName() == 'Fax') {
                return 'F.';
            }
        }
        return '';
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Number
     * @param $Type
     *
     * @return bool
     */
    public function createPhoneToPerson(
        TblPerson $tblPerson,
        $Number,
        $Type
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        $tblPhone = (new Data($this->getBinding()))->createPhone($Number);

        if (!$tblType) {
            return false;
        }
        if (!$tblPhone) {
            return false;
        }

        if ((new Data($this->getBinding()))->addPhoneToPerson($tblPerson, $tblPhone, $tblType, $Type['Remark'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param $Number
     * @param $Type
     *
     * @return bool
     */
    public function updatePhoneToPerson(
        TblToPerson $tblToPerson,
        $Number,
        $Type
    ) {

        $tblPhone = (new Data($this->getBinding()))->createPhone($Number);
        // Remove current
        (new Data($this->getBinding()))->removePhoneToPerson($tblToPerson);

        if ($tblToPerson->getServiceTblPerson()
            && ($tblType = $this->getTypeById($Type['Type']))
        ) {
            // Add new
            if ((new Data($this->getBinding()))->addPhoneToPerson($tblToPerson->getServiceTblPerson(), $tblPhone,
                $tblType, $Type['Remark'])
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Number
     * @param $Type
     * @param TblToPerson|null $tblToPerson
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormPhoneToPerson(
        TblPerson $tblPerson,
        $Number,
        $Type,
        TblToPerson $tblToPerson = null
    ) {

        $error = false;
        $form = Phone::useFrontend()->formNumberToPerson($tblPerson->getId(), $tblToPerson ? $tblToPerson->getId() : null);
        if (isset( $Number ) && empty( $Number )) {
            $form->setError('Number', 'Bitte geben Sie eine gültige Telefonnummer an');
            $error = true;
        } else {
            $form->setSuccess('Number');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))){
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }

        return $error ? $form : false;
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
     * @param TblPerson $tblPerson
     * @param $Number
     * @param TblType $tblType
     * @param $Remark
     *
     * @return TblToPerson
     */
    public function insertPhoneToPerson(
        TblPerson $tblPerson,
        $Number,
        TblType $tblType,
        $Remark
    ) {

        $tblPhone = (new Data($this->getBinding()))->createPhone($Number);
        return (new Data($this->getBinding()))->addPhoneToPerson($tblPerson, $tblPhone, $tblType, $Remark);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Number
     * @param TblType $tblType
     * @param $Remark
     *
     * @return TblToCompany
     */
    public function insertPhoneToCompany(
        TblCompany $tblCompany,
        $Number,
        TblType $tblType,
        $Remark
    ) {

        $tblPhone = (new Data($this->getBinding()))->createPhone($Number);
        return (new Data($this->getBinding()))->addPhoneToCompany($tblCompany, $tblPhone, $tblType, $Remark);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Number
     * @param $Type
     *
     * @return bool
     */
    public function createPhoneToCompany(
        TblCompany $tblCompany,
        $Number,
        $Type
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        $tblPhone = (new Data($this->getBinding()))->createPhone($Number);

        if (!$tblType) {
            return false;
        }
        if (!$tblPhone) {
            return false;
        }

        if ((new Data($this->getBinding()))->addPhoneToCompany($tblCompany, $tblPhone, $tblType, $Type['Remark'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblToCompany $tblToCompany
     * @param $Number
     * @param $Type
     *
     * @return bool
     */
    public function updatePhoneToCompany(
        TblToCompany $tblToCompany,
        $Number,
        $Type
    ) {

        $tblPhone = (new Data($this->getBinding()))->createPhone($Number);
        // Remove current
        (new Data($this->getBinding()))->removePhoneToCompany($tblToCompany);

        if ($tblToCompany->getServiceTblCompany()
            && ($tblType = $this->getTypeById($Type['Type']))
        ) {
            // Add new
            if ((new Data($this->getBinding()))->addPhoneToCompany($tblToCompany->getServiceTblCompany(), $tblPhone,
                $tblType, $Type['Remark'])
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Number
     * @param $Type
     * @param TblToCompany|null $tblToCompany
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormPhoneToCompany(
        TblCompany $tblCompany,
        $Number,
        $Type,
        TblToCompany $tblToCompany = null
    ) {

        $error = false;
        $form = Phone::useFrontend()->formNumberToCompany($tblCompany->getId(), $tblToCompany ? $tblToCompany->getId() : null);
        if (isset( $Number ) && empty( $Number )) {
            $form->setError('Number', 'Bitte geben Sie eine gültige Telefonnummer an');
            $error = true;
        } else {
            $form->setSuccess('Number');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))){
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }

        return $error ? $form : false;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getPhoneToPersonById($Id)
    {

        return (new Data($this->getBinding()))->getPhoneToPersonById($Id);
    }


    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getPhoneToCompanyById($Id)
    {

        return (new Data($this->getBinding()))->getPhoneToCompanyById($Id);
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removePhoneToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->removePhoneToPerson($tblToPerson, $IsSoftRemove);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removePhoneToCompany(TblToCompany $tblToCompany)
    {

        return (new Data($this->getBinding()))->removePhoneToCompany($tblToCompany);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removeSoftPhoneAllByPerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblPhoneToPersonList = $this->getPhoneAllByPerson($tblPerson))){
            foreach($tblPhoneToPersonList as $tblToPerson){
                $this->removePhoneToPerson($tblToPerson, $IsSoftRemove);
            }
        }
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return false|TblType
     */
    public function getTypeByNameAndDescription($Name, $Description)
    {

        return (new Data($this->getBinding()))->getTypeByNameAndDescription($Name, $Description);
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblType $tblType
     *
     * @return false|TblToPerson[]
     */
    public function getPhoneToPersonAllBy(TblPerson $tblPerson, TblType $tblType)
    {

        return (new Data($this->getBinding()))->getPhoneToPersonAllBy($tblPerson, $tblType);
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
}
