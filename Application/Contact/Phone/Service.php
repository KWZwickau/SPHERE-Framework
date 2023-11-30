<?php
namespace SPHERE\Application\Contact\Phone;

use SPHERE\Application\Contact\Phone\Service\Data;
use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
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
            if ($tblToPerson->getIsEmergencyContact()) {
                return 'n.';
            } elseif ($tblType->getName() == 'Privat') {
                return 'p.';
            } elseif ($tblType->getName() == 'Geschäftlich') {
                return 'g.';
            } elseif ($tblType->getName() == 'Fax') {
                return 'f.';
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

        if ((new Data($this->getBinding()))->addPhoneToPerson($tblPerson, $tblPhone, $tblType, $Type['Remark'], isset($Type['IsEmergencyContact']))) {
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
            if ((new Data($this->getBinding()))->addPhoneToPerson(
                $tblToPerson->getServiceTblPerson(), $tblPhone, $tblType, $Type['Remark'], isset($Type['IsEmergencyContact']))
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
     * @param $OnlineContactId
     * @param TblToPerson|null $tblToPerson
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormPhoneToPerson(
        TblPerson $tblPerson,
        $Number,
        $Type,
        $OnlineContactId,
        TblToPerson $tblToPerson = null
    ) {

        $error = false;
        $form = Phone::useFrontend()->formNumberToPerson($tblPerson->getId(), $tblToPerson ? $tblToPerson->getId() : null, false, $OnlineContactId);
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
     * @param string $Remark
     * @param bool $isEmergencyContact
     *
     * @return TblToPerson
     */
    public function insertPhoneToPerson(
        TblPerson $tblPerson,
        $Number,
        TblType $tblType,
        string $Remark,
        bool $isEmergencyContact = false
    ): TblToPerson {

        $tblPhone = (new Data($this->getBinding()))->createPhone($Number);
        return (new Data($this->getBinding()))->addPhoneToPerson($tblPerson, $tblPhone, $tblType, $Remark, $isEmergencyContact);
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

        if ((new Data($this->getBinding()))->addPhoneToCompany($tblCompany, $tblPhone, $tblType, $Type['Remark'], isset($Type['IsEmergencyContact']))) {
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
            if ((new Data($this->getBinding()))->addPhoneToCompany(
                $tblToCompany->getServiceTblCompany(), $tblPhone, $tblType, $Type['Remark'], isset($Type['IsEmergencyContact'])
            )) {
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
     * @param TblPerson $tblPerson
     *
     * @return false|TblToPerson[]
     */
    public function getPhoneToPersonAllEmergencyContactByPerson(TblPerson $tblPerson)
    {
        return (new Data($this->getBinding()))->getPhoneToPersonAllEmergencyContactByPerson($tblPerson);
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
     * @param $Number
     * @param TblType $tblType
     * @param bool $isEmergencyContact
     * @param $Remark
     * @param array $tblPersonList
     *
     * @return bool
     */
    public function insertPhoneToPersonList(
        $Number,
        TblType $tblType,
        bool $isEmergencyContact,
        $Remark,
        $tblPersonList = array()
    ): bool {

        if (($tblPhone = (new Data($this->getBinding()))->createPhone($Number))) {
            foreach ($tblPersonList as $tblPerson) {
                (new Data($this->getBinding()))->addPhoneToPerson($tblPerson, $tblPhone, $tblType, $Remark, $isEmergencyContact);
            }

            return  true;
        }

        return false;
    }

    /**
     * @param TblPhone $tblPhone
     *
     * @return false|TblToPerson[]
     */
    public function getToPersonAllByPhone(TblPhone $tblPhone)
    {
        return (new Data($this->getBinding()))->getToPersonAllByPhone($tblPhone);
    }

    /**
     * @param TblPhone $tblPhone
     *
     * @return false|TblPerson[]
     */
    public function getPersonAllByPhone(TblPhone $tblPhone)
    {
        $result = array();
        if (($tblToPersonList = $this->getToPersonAllByPhone($tblPhone))) {
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
     * @param TblPhone $tblPhone
     *
     * @return false|TblToPerson
     */
    public function getPhoneToPersonByPersonAndPhone(TblPerson $tblPerson, TblPhone $tblPhone)
    {
        return (new Data($this->getBinding()))->getPhoneToPersonByPersonAndPhone($tblPerson, $tblPhone);
    }

    /**
     * @param $Number
     *
     * @return TblPhone
     */
    public function insertPhone(
        $Number
    ): TblPhone {
        return (new Data($this->getBinding()))->createPhone($Number);
    }
}
