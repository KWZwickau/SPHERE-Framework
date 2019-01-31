<?php
namespace SPHERE\Application\Contact\Mail;

use SPHERE\Application\Contact\Mail\Service\Data;
use SPHERE\Application\Contact\Mail\Service\Entity\TblMail;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Mail\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Mail\Service\Entity\TblType;
use SPHERE\Application\Contact\Mail\Service\Entity\ViewMailToCompany;
use SPHERE\Application\Contact\Mail\Service\Entity\ViewMailToPerson;
use SPHERE\Application\Contact\Mail\Service\Setup;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\Contact\Mail
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

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        }
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return false|ViewMailToPerson[]
     */
    public function viewMailToPerson()
    {

        return ( new Data($this->getBinding()) )->viewMailToPerson();
    }

    /**
     * @return false|ViewMailToCompany[]
     */
    public function viewMailToCompany()
    {

        return ( new Data($this->getBinding()) )->viewMailToCompany();
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblMail
     */
    public function getMailById($Id)
    {

        return (new Data($this->getBinding()))->getMailById($Id);
    }

    /**
     * @return bool|TblMail[]
     */
    public function getMailAll()
    {

        return (new Data($this->getBinding()))->getMailAll();
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        return (new Data($this->getBinding()))->getTypeAll();
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
     * @param bool $isForced
     *
     * @return bool|TblToPerson[]
     */
    public function getMailAllByPerson(TblPerson $tblPerson, $isForced = false)
    {

        return (new Data($this->getBinding()))->getMailAllByPerson($tblPerson, $isForced);
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getMailAllByCompany(TblCompany $tblCompany)
    {

        return (new Data($this->getBinding()))->getMailAllByCompany($tblCompany);
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Address
     * @param $Type
     * @param TblToPerson|null $tblToPerson
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormMailToPerson(
        TblPerson $tblPerson,
        $Address,
        $Type,
        TblToPerson $tblToPerson = null
    ) {

        $error = false;

        $form = Mail::useFrontend()->formAddressToPerson($tblPerson->getId(), $tblToPerson ? $tblToPerson->getId() : null);
        $Address = $this->validateMailAddress($Address);
        if (isset($Address) && empty($Address)) {
            $form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
            $error = true;
        } else {
            $form->setSuccess('Address');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param $Type
     * @param TblToCompany|null $tblToCompany
     *
     * @return bool|\SPHERE\Common\Frontend\Form\Structure\Form
     */
    public function checkFormMailToCompany(
        TblCompany $tblCompany,
        $Address,
        $Type,
        TblToCompany $tblToCompany = null
    ) {

        $error = false;

        $form = Mail::useFrontend()->formAddressToCompany($tblCompany->getId(), $tblToCompany ? $tblToCompany->getId() : null);
        $Address = $this->validateMailAddress($Address);
        if (isset($Address) && empty($Address)) {
            $form->setError('Address', 'Bitte geben Sie eine gültige E-Mail Adresse an');
            $error = true;
        } else {
            $form->setSuccess('Address');
        }
        if (!($tblType = $this->getTypeById($Type['Type']))) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Address
     * @param $Type
     *
     * @return bool
     */
    public function createMailToPerson(
        TblPerson $tblPerson,
        $Address,
        $Type
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        $tblMail = (new Data($this->getBinding()))->createMail($Address);

        if (!$tblType) {
            return false;
        }
        if (!$tblMail) {
            return false;
        }

        if ((new Data($this->getBinding()))->addMailToPerson($tblPerson, $tblMail, $tblType, $Type['Remark'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param $Type
     *
     * @return bool
     */
    public function createMailToCompany(
        TblCompany $tblCompany,
        $Address,
        $Type
    ) {

        $tblType = $this->getTypeById($Type['Type']);
        $tblMail = (new Data($this->getBinding()))->createMail($Address);

        if (!$tblType) {
            return false;
        }
        if (!$tblMail) {
            return false;
        }

        if ((new Data($this->getBinding()))->addMailToCompany($tblCompany, $tblMail, $tblType, $Type['Remark'])
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param           $Address
     * @param TblType   $tblType
     * @param           $Remark
     *
     * @return TblToPerson
     */
    public function insertMailToPerson(
        TblPerson $tblPerson,
        $Address,
        TblType $tblType,
        $Remark
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
        return (new Data($this->getBinding()))->addMailToPerson($tblPerson, $tblMail, $tblType, $Remark);
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param TblType $tblType
     * @param $Remark
     *
     * @return TblToCompany
     */
    public function insertMailToCompany(
        TblCompany $tblCompany,
        $Address,
        TblType $tblType,
        $Remark
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
        return (new Data($this->getBinding()))->addMailToCompany($tblCompany, $tblMail, $tblType, $Remark);
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param $Address
     * @param $Type
     *
     * @return bool
     */
    public function updateMailToPerson(
        TblToPerson $tblToPerson,
        $Address,
        $Type
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
        // Remove current
        (new Data($this->getBinding()))->removeMailToPerson($tblToPerson);

        if ($tblToPerson->getServiceTblPerson()
            && ($tblType = $this->getTypeById($Type['Type']))
        ) {
            // Add new
            if ((new Data($this->getBinding()))->addMailToPerson($tblToPerson->getServiceTblPerson(), $tblMail,
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
     * @param TblToCompany $tblToCompany
     * @param $Address
     * @param $Type
     *
     * @return bool
     */
    public function updateMailToCompany(
        TblToCompany $tblToCompany,
        $Address,
        $Type
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
        // Remove current
        (new Data($this->getBinding()))->removeMailToCompany($tblToCompany);

        if ($tblToCompany->getServiceTblCompany()
            && ($tblType = $this->getTypeById($Type['Type']))
        ) {
            // Add new
            if ((new Data($this->getBinding()))->addMailToCompany($tblToCompany->getServiceTblCompany(), $tblMail,
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
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getMailToPersonById($Id)
    {

        return (new Data($this->getBinding()))->getMailToPersonById($Id);
    }


    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getMailToCompanyById($Id)
    {

        return (new Data($this->getBinding()))->getMailToCompanyById($Id);
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function removeMailToPerson(TblToPerson $tblToPerson, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->removeMailToPerson($tblToPerson, $IsSoftRemove);
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeMailToCompany(TblToCompany $tblToCompany)
    {

        return (new Data($this->getBinding()))->removeMailToCompany($tblToCompany);
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $IsSoftRemove
     */
    public function removeSoftMailAllByPerson(TblPerson $tblPerson, $IsSoftRemove = false)
    {

        if (($tblMailToPersonList = $this->getMailAllByPerson($tblPerson))){
            foreach($tblMailToPersonList as $tblToPerson){
                $this->removeMailToPerson($tblToPerson, $IsSoftRemove);
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
}
