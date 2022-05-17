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
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Common\Frontend\Form\Structure\Form;
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
     * @param TblToPerson $tblToPerson
     *
     * @return string
     */
    public function getMailTypeShort(TblToPerson $tblToPerson)
    {

        $tblType = $tblToPerson->getTblType();
        if ($tblType) {
            if ($tblType->getName() == 'Privat') {
                return 'p.';
            } elseif ($tblType->getName() == 'Geschäftlich') {
                return 'g.';
            }
        }
        return '';
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $Address
     * @param array $Type
     * @param bool $IsAccountUserAlias
     * @param TblToPerson|null $tblToPerson
     *
     * @return bool|Form
     */
    public function checkFormMailToPerson(
        TblPerson $tblPerson,
        string $Address,
        array $Type,
        bool $IsAccountUserAlias,
        TblToPerson $tblToPerson = null
    ) {

        $error = false;

        $form = Mail::useFrontend()->formAddressToPerson($tblPerson->getId(), $tblToPerson ? $tblToPerson->getId() : null);
        $Address = $this->validateMailAddress($Address);
        if (isset($Address) && empty($Address)) {
            $form->setError('Address[Mail]', 'Bitte geben Sie eine gültige E-Mail Adresse an');
            $isValidatedMailAddress = false;
            $error = true;
        } else {
            $isValidatedMailAddress = true;
        }
        if (!($tblType = $this->getTypeById($Type['Type']))) {
            $form->setError('Type[Type]', 'Bitte geben Sie einen Typ an');
            $error = true;
        } elseif ($IsAccountUserAlias && $tblType && $tblType->getName() != 'Geschäftlich' ) {
            // UCS Benutzername muss als geschäftliche E-Mail Adresse angelegt werden
            $form->setError('Type[Type]', 'Zur Verwendung der E-Mail Adresse als UCS Benutzername muss der E-Mail Typ: 
                Geschäftlich ausgewählt werden.');
            $error = true;
        } else {
            $form->setSuccess('Type[Type]');
        }

        if(!$error && $IsAccountUserAlias){
            $errorMessage = '';
            // Eindeutigkeit UCS Alias
            if (!Account::useService()->isUserAliasUnique($tblPerson, $Address, $errorMessage)) {
                $error = true;
                $form->setError('Address[Mail]', $errorMessage);
            }
        }

        if (!$error && $isValidatedMailAddress) {
            $form->setSuccess('Address[Mail]');
        }

        return $error ? $form : false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param $Address
     * @param $Type
     * @param TblToCompany|null $tblToCompany
     *
     * @return bool|Form
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
     * @param           $Address
     * @param           $Type
     * @param bool      $IsAccountUserAlias
     * @param bool      $IsAccountRecoveryMail
     *
     * @return bool
     */
    public function createMailToPerson(
        TblPerson $tblPerson,
        $Address,
        $Type,
        $IsAccountUserAlias = false,
        $IsAccountRecoveryMail = false
    ): bool {

        $tblType = $this->getTypeById($Type['Type']);
        $tblMail = (new Data($this->getBinding()))->createMail($Address);

        if (!$tblType) {
            return false;
        }
        if (!$tblMail) {
            return false;
        }

        if ($IsAccountUserAlias || $IsAccountRecoveryMail) {
            $tblAccount = false;
//            if(($tblAccountList = Account::useService()->getAccountAllByPersonForUCS($tblPerson))) {
            if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))) {
                if (count($tblAccountList) > 1) {
                    return false;
                } else {
                    $tblAccount = current($tblAccountList);
                }
            }

            $errorMessage = '';
            if (!Account::useService()->isUserAliasUnique($tblPerson, $Address, $errorMessage)) {
                return false;
            }

            if ($tblAccount) {
                if($IsAccountUserAlias){
                    Account::useService()->changeUserAlias($tblAccount, $Address);
                    $this->resetMailWithUserAlias($tblPerson);
                }
                if($IsAccountRecoveryMail){
                    Account::useService()->changeRecoveryMail($tblAccount, $Address);
                    $this->resetMailWithRecoveryMail($tblPerson);
                }
            }
        }

        if ((new Data($this->getBinding()))->addMailToPerson($tblPerson, $tblMail, $tblType, $Type['Remark'],
            $IsAccountUserAlias, $IsAccountRecoveryMail)
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
     * @param bool      $IsUserAlias
     * @param bool      $IsRecoveryMail
     *
     * @return TblToPerson
     */
    public function insertMailToPerson(
        TblPerson $tblPerson,
        $Address,
        TblType $tblType,
        $Remark,
        $IsUserAlias = false,
        $IsRecoveryMail = false
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);
        // falls die Emailadresse bereits vorhanden ist, diese überschreiben
        if (($tblToPersonList = $this->getMailAllByPerson($tblPerson))) {
            foreach ($tblToPersonList as $tblToPerson) {
                if (($tblMailTemp = $tblToPerson->getTblMail())
                    && $tblMail->getId() == $tblMailTemp->getId()
                ) {
                    if($IsUserAlias){
                        return (new Data($this->getBinding()))->updateMailToPersonAlias($tblToPerson, $tblType, $IsUserAlias);
                    } elseif($IsRecoveryMail){
                        return (new Data($this->getBinding()))->updateMailToPersonRecoveryMail($tblToPerson, $tblType, $IsRecoveryMail);
                    } else {
                        return (new Data($this->getBinding()))->updateMailToPerson($tblToPerson, $tblMail, $tblType, $Remark,
                            $tblToPerson->isAccountUserAlias(), $tblToPerson->isAccountRecoveryMail());
                    }
                }
            }
        }
        return (new Data($this->getBinding()))->addMailToPerson($tblPerson, $tblMail, $tblType, $Remark, $IsUserAlias, $IsRecoveryMail);
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
     * @param             $Address
     * @param             $Type
     * @param bool        $IsAccountUserAlias
     * @param bool        $IsAccountRecoveryMail
     *
     * @return bool
     */
    public function updateMailToPerson(
        TblToPerson $tblToPerson,
        $Address,
        $Type,
        $IsAccountUserAlias = false,
        $IsAccountRecoveryMail = false
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);

        if (($tblPerson = $tblToPerson->getServiceTblPerson())
            && ($tblType = $this->getTypeById($Type['Type']))
        ) {

            if ($IsAccountUserAlias){
                $errorMessage = '';
                if(!Account::useService()->isUserAliasUnique($tblPerson, $Address, $errorMessage)){
                    return false;
                }
            }

            /** @var TblAccount $tblAccount */
            $tblAccount = false;
//                if(($tblAccountList = Account::useService()->getAccountAllByPersonForUCS($tblPerson))) {
            if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblPerson))) {
                if (count($tblAccountList) > 1) {
                    return false;
                } else {
                    $tblAccount = current($tblAccountList);
                }
            }

            if ($tblAccount) {
                if($IsAccountUserAlias){
                    Account::useService()->changeUserAlias($tblAccount, $Address);
                    $this->resetMailWithUserAlias($tblPerson, $tblToPerson);
                } else {
                    // Entfernen vorhandener Einträge am Benutzeraccount Wenn E-Mail vergeben ist
                    if($tblAccount->getUserAlias() == $Address){
                        Account::useService()->changeUserAlias($tblAccount, '');
                    }
                }
                if($IsAccountRecoveryMail){
                    Account::useService()->changeRecoveryMail($tblAccount, $Address);
                    $this->resetMailWithRecoveryMail($tblPerson, $tblToPerson);
                } else {
                    // Entfernen vorhandener Einträge am Benutzeraccount Wenn E-Mail vergeben ist
                    if($tblAccount->getRecoveryMail() == $Address){
                        Account::useService()->changeRecoveryMail($tblAccount, '');
                    }
                }
            }



            // update
            if ((new Data($this->getBinding()))->updateMailToPerson($tblToPerson, $tblMail,
                $tblType, $Type['Remark'], $IsAccountUserAlias, $IsAccountRecoveryMail)
            ){
                return true;
            }
        }

        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblToPerson|null $tblToPerson
     */
    public function resetMailWithUserAlias(TblPerson $tblPerson, TblToPerson $tblToPerson = null) {
        if(($tblToPersonList = $this->getMailAllByPerson($tblPerson))){
            foreach($tblToPersonList as $tblToPersonTemp){
                if($tblToPersonTemp->isAccountUserAlias()
                    && (!$tblToPerson || ($tblToPerson->getId() != $tblToPersonTemp->getId()))
                ){
                    $this->updateMailToPersonAlias($tblToPersonTemp, false);
                }
            }
        }
    }

    /**
     * @param TblPerson $tblPerson
     * @param TblToPerson|null $tblToPerson
     */
    private function resetMailWithRecoveryMail(TblPerson $tblPerson, TblToPerson $tblToPerson = null) {
        if(($tblToPersonList = $this->getMailAllByPerson($tblPerson))){
            foreach($tblToPersonList as $tblToPersonTemp){
                if($tblToPersonTemp->isAccountRecoveryMail()
                    && (!$tblToPerson || ($tblToPerson->getId() != $tblToPersonTemp->getId()))
                ){
                    $this->updateMailToPersonRecoveryMail($tblToPersonTemp, false);
                }
            }
        }
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool $IsAccountUserAlias
     *
     * @return bool
     */
    public function updateMailToPersonAlias(
        TblToPerson $tblToPerson,
        $IsAccountUserAlias = false
    ) {

        if ((new Data($this->getBinding()))->updateMailToPersonAlias($tblToPerson, $tblToPerson->getTblType(), $IsAccountUserAlias)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param bool        $IsAccountRecoveryMail
     *
     * @return bool
     */
    public function updateMailToPersonRecoveryMail(
        TblToPerson $tblToPerson,
        $IsAccountRecoveryMail = false
    ) {

        if ((new Data($this->getBinding()))->updateMailToPersonRecoveryMail($tblToPerson, $tblToPerson->getTblType(), $IsAccountRecoveryMail)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param TblToPerson $tblToPerson
     * @param             $Address
     * @param TblType     $tblType
     * @param             $Remark
     * @param bool        $IsAccountUserAlias
     * @param bool        $IsAccountRecoveryMail
     *
     * @return bool
     */
    public function updateMailToPersonService(
        TblToPerson $tblToPerson,
        $Address,
        TblType $tblType,
        $Remark,
        $IsAccountUserAlias = false,
        $IsAccountRecoveryMail = false
    ) {

        $tblMail = (new Data($this->getBinding()))->createMail($Address);

        if ($tblToPerson->getServiceTblPerson()) {
            return (new Data($this->getBinding()))->updateMailToPerson($tblToPerson, $tblMail, $tblType, $Remark,
                $IsAccountUserAlias, $IsAccountRecoveryMail);
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


        if(($tblAccountList = Account::useService()->getAccountAllByPerson($tblToPerson->getServiceTblPerson()))){
            $tblAccount = current($tblAccountList);
            if($tblToPerson->isAccountUserAlias()){
                Account::useService()->changeUserAlias($tblAccount, '');
            }
            if($tblToPerson->isAccountRecoveryMail()){
                Account::useService()->changeRecoveryMail($tblAccount, '');
            }
        }
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

    /**
     * @param $Address
     * @param TblType $tblType
     * @param $Remark
     * @param array $tblPersonList
     *
     * @return bool
     */
    public function insertMailToPersonList(
        $Address,
        TblType $tblType,
        $Remark,
        $IsAccountUserAlias,
        $IsAccountRecoveryMail,
        $tblPersonList = array()
    ) {
        if (($tblMail = (new Data($this->getBinding()))->createMail($Address))) {
            foreach ($tblPersonList as $tblPerson) {
                (new Data($this->getBinding()))->addMailToPerson($tblPerson, $tblMail, $tblType, $Remark, $IsAccountUserAlias, $IsAccountRecoveryMail);
            }

            return  true;
        }

        return false;
    }

    /**
     * @param string $address
     *
     * @return false|TblToPerson[]
     */
    public function getToPersonListByAddress(string $address) {
        return (new Data($this->getBinding()))->getToPersonListByAddress($address);
    }

    /**
     * @param TblMail $tblMail
     *
     * @return false|TblToPerson[]
     */
    public function getToPersonAllByMail(TblMail $tblMail)
    {
        return (new Data($this->getBinding()))->getToPersonAllByMail($tblMail);
    }

    /**
     * @param TblMail $tblMail
     *
     * @return false|TblPerson[]
     */
    public function getPersonAllByMail(TblMail $tblMail)
    {
        $result = array();
        if (($tblToPersonList = $this->getToPersonAllByMail($tblMail))) {
            foreach ($tblToPersonList as $tblToPerson) {
                if (($tblPerson = $tblToPerson->getServiceTblPerson())) {
                    $result[$tblPerson->getId()] = $tblPerson;
                }
            }
        }

        return empty($result) ? false : $result;
    }
}
