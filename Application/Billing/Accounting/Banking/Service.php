<?php
namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Data;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Accounting\Banking\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Accounting\Banking
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
     * @return bool|TblDebtor[]
     */
    public function getDebtorAll()
    {

        return (new Data($this->getBinding()))->getDebtorAll();
    }

    /**
     * @return bool|TblPaymentType[]
     */
    public function getPaymentTypeAll()
    {

        return (new Data($this->getBinding()))->getPaymentTypeAll();
    }

    /**
     * @param TblPerson $Person
     *
     * @return bool|TblDebtor[]
     */
    public function getDebtorAllByPerson(TblPerson $Person)
    {

        return (new Data($this->getBinding()))->getDebtorAllByPerson($Person);
    }

    /**
     * @param TblPerson $Person
     *
     * @return false|TblDebtor
     */
    public function getDebtorByPerson(TblPerson $Person)
    {

        return (new Data($this->getBinding()))->getDebtorByPerson($Person);
    }

    /**
     * @param $ServiceManagement_Person
     *
     * @return bool|TblDebtor[]
     */
    public function getDebtorByServicePeoplePerson($ServiceManagement_Person)
    {

        return (new Data($this->getBinding()))->getDebtorByServicePeoplePerson($ServiceManagement_Person);
    }

    /**
     * @param $PaymentType
     *
     * @return bool|TblPaymentType
     */
    public function getPaymentTypeByName($PaymentType)
    {

        return (new Data($this->getBinding()))->getPaymentTypeByName($PaymentType);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|Service\Entity\TblBankAccount[]
     */
    public function getBankAccountByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getBankAccountByPerson($tblPerson);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return false|TblBankReference[]
     */
    public function getBankReferenceByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->getBinding()))->getBankReferenceByPerson($tblPerson);
    }

//    /**
//     * @param TblDebtor $tblDebtor
//     *
//     * @return int
//     */
//    public function getLeadTimeByDebtor(TblDebtor $tblDebtor)   //ToDO get first/followLeadTime from School
//    {
//
//        if (( $tblAccount = Banking::useService()->getActiveAccountByDebtor($tblDebtor) )) {
//            if (Invoice::useService()->checkInvoiceFromDebtorIsPaidByDebtor($tblDebtor) ||
//                Balance::useService()->checkPaymentFromDebtorExistsByDebtor($tblDebtor)
//            ) {
//                return $tblAccount->getLeadTimeFollow();
//            } else {
//                return $tblAccount->getLeadTimeFirst();
//            }
//        }
//        return false;
//    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return string
     */
    public function destroyBanking(TblDebtor $tblDebtor)
    {

        if (null === $tblDebtor) {
            return '';
        }
        $Error = false;

        $tblInvoiceList = Invoice::useService()->getInvoiceAll();
        foreach ($tblInvoiceList as $tblInvoice) {
            if (!$tblInvoice->isVoid()) {
                if (!$tblInvoice->isPaid()) {
                    if (!$tblInvoice->isConfirmed()) {
                        $tblDebtorInvoice = Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber());
                        if ($tblDebtorInvoice->getId() === $tblDebtor->getId()) {
                            $Error = true;
                        }
                    }
                }
            }
        }

        if (!$Error) {
            if (Banking::useService()->getAccountAllByDebtor($tblDebtor)) {
                $tblAccountList = Banking::useService()->getAccountAllByDebtor($tblDebtor);
                foreach ($tblAccountList as $tblAccount) {
                    Banking::useService()->destroyAccount($tblAccount);
                }
            }

            if ((new Data($this->getBinding()))->removeBanking($tblDebtor)) {
                return new Success('Der Debitor wurde erfolgreich gelöscht')
                .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Der Debitor konnte nicht gelöscht werden')
                .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
            }
        }
        return new Danger('Es bestehen noch offene Rechnungen mit diesem Debitor')
        .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
    }

    /**
     * @param $DebtorNumber
     *
     * @return bool|TblDebtor
     */
    public function getDebtorByDebtorNumber($DebtorNumber)
    {

        return (new Data($this->getBinding()))->getDebtorByDebtorNumber($DebtorNumber);
    }

    /**
     * @param TblBankAccount $tblAccount
     *
     * @return bool
     */
    public function destroyAccount(TblBankAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->destroyAccount($tblAccount);
    }

    /**
     * @param TblBankReference $tblBankReference
     *
     * @return string
     */
    public function deactivateBankReference(TblBankReference $tblBankReference)
    {

        if (null === $tblBankReference) {
            return '';
        }
        if ((new Data($this->getBinding()))->deactivateReference($tblBankReference)) {
            return new Success('Die Deaktivierung ist erfasst worden')
            .new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Danger('Die Referenz konnte nicht deaktiviert werden')
            .new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param $Id
     * @param $Account
     * @param $Path
     * @param $IdBack
     *
     * @return string
     */
    public function changeActiveAccount($Id, $Account, $Path, $IdBack)
    {

        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $tblAccount = Banking::useService()->getBankAccountById($Account);
        $tblOldAccount = Banking::useService()->getActiveAccountByDebtor($tblDebtor);

        if (!empty( $tblOldAccount )) {
            (new Data($this->getBinding()))->deactivateAccount($tblOldAccount);
        }

        if ((new Data($this->getBinding()))->activateAccount($tblAccount)) {
            return new Success('Das Konto wurde als aktives Konto gesetzt')
            .new Redirect($Path, Redirect::TIMEOUT_SUCCESS, array('Id' => $IdBack));
        } else {
            return new Warning('Das Konto konte nicht als aktives Konto gesetzt werden')
            .new Redirect($Path, Redirect::TIMEOUT_ERROR, array('Id' => $IdBack));
        }
    }

    /**
     * @param $Id
     *
     * @return bool|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceById($Id)
    {

        return (new Data($this->getBinding()))->getBankReferenceById($Id);
    }

    /**
     * @return false|TblBankReference[]
     */
    public function getBankReferenceAll()
    {

        return (new Data($this->getBinding()))->getBankReferenceAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblBankAccount
     */
    public function getBankAccountById($Id)
    {

        return (new Data($this->getBinding()))->getBankAccountById($Id);
    }

    /**
     * @return bool|TblBankAccount[]
     */
    public function getBankAccountAll()
    {

        return (new Data($this->getBinding()))->getBankAccountAll();
    }

    /**
     * @param $Id
     * @param $PaymentType
     *
     * @return string
     */
    public function changeDebtorPaymentType($Id, $PaymentType)
    {

        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $tblPaymentType = Banking::useService()->getPaymentTypeById($PaymentType);

        if ((new Data($this->getBinding()))->changePaymentType($tblDebtor, $tblPaymentType)) {
            return new Success('Die Zahlungsart wurde geändert')
            .new Redirect('/Billing/Accounting/Banking/Debtor/View', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblDebtor->getId()));
        } else {
            return new Warning('Die Zahlungsart konnte nicht geändert werden')
            .new Redirect('/Billing/Accounting/Banking/Debtor/View', Redirect::TIMEOUT_ERROR, array('Id' => $tblDebtor->getId()));
        }
    }

    /**
     * @param $Id
     *
     * @return bool|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        return (new Data($this->getBinding()))->getPaymentTypeById($Id);
    }

    /**
     * @param IFormInterface $Stage
     * @param                $Debtor
     * @param                $Id
     *
     * @return IFormInterface|string
     */
    public function createDebtor(IFormInterface &$Stage = null, $Debtor, $Id)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Debtor) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Debtor['DebtorNumber'] ) && empty( $Debtor['DebtorNumber'] )) {
            $Stage->setError('Debtor[DebtorNumber]', 'Bitte geben sie die Debitorennummer an');
            $Error = true;
        }
        if (isset( $Debtor['DebtorNumber'] ) && Banking::useService()->getDebtorByDebtorNumber($Debtor['DebtorNumber'])) {
            $Stage->setError('Debtor[DebtorNumber]',
                'Die Debitorennummer exisitiert bereits. Bitte geben Sie eine andere Debitorennummer an');
            $Error = true;
        }

        if (!$Error) {
            $tblPerson = Person::useService()->getPersonById($Id);
            if ($tblPerson) {
                if (!Banking::useService()->getDebtorAllByPerson($tblPerson)) {
                    (new Data($this->getBinding()))->createDebtor($tblPerson, $Debtor['DebtorNumber']);

                    return new Success('Der Debitor ist erfasst worden')
                    .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_SUCCESS);
                } else {
                    return new Danger('Person beitzt bereits eine Debitor-Nummer')
                    .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
                }
            } else {
                return new Danger('Person nicht gefunden')
                .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
            }
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblDebtor           $tblDebtor
     * @param                     $Debtor
     *
     * @return IFormInterface|string
     */
    public function changeDebtor(IFormInterface &$Stage = null, TblDebtor $tblDebtor, $Debtor)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Debtor) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Debtor['DebtorNumber'] ) && empty( $Debtor['DebtorNumber'] )) {
            $Stage->setError('Debtor[DebtorNumber]', 'Bitte geben sie die Debitorennummer an');
            $Error = true;
        }
        if (isset( $Debtor['DebtorNumber'] ) && Banking::useService()->getDebtorByDebtorNumber($Debtor['DebtorNumber'])) {
            $Stage->setError('Debtor[DebtorNumber]',
                'Die Debitorennummer exisitiert bereits. Bitte geben Sie eine andere Debitorennummer an');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->updateDebtor($tblDebtor, $Debtor['DebtorNumber'])) {
                return new Success('Die Debitor-Nummer ist geändert worden')
                .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Danger('Die Debitor-Nummer konnte nicht geändert werden')
                .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
            }
        }
        return $Stage;
    }

    /**
     * @param $IBAN
     *
     * @return false|TblBankAccount
     */
    public function getIBANIsUsed($IBAN)
    {

        return (new Data($this->getBinding()))->getIBANIsUsed($IBAN);
    }

    /**
     * @param $Reference
     *
     * @return false|TblBankReference
     */
    public function getReferenceIsUsed($Reference)
    {

        return (new Data($this->getBinding()))->getReferenceIsUsed($Reference);
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPerson           $tblPerson
     * @param                     $Account
     *
     * @return IFormInterface|string
     */
    public function createAccount(IFormInterface &$Stage = null, TblPerson $tblPerson, $Account)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Account) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Account['BankName'] ) && empty( $Account['BankName'] )) {
            $Stage->setError('Account[BankName]', 'Bitte geben Sie den Namen der Bank an');
            $Error = true;
        }
        if (isset( $Account['Owner'] ) && empty( $Account['Owner'] )) {
            $Stage->setError('Account[Owner]', 'Bitte geben Sie den Besitzer des Konto\'s an');
            $Error = true;
        }
        if (isset( $Account['IBAN'] ) && empty( $Account['IBAN'] )) {
            $Stage->setError('Account[IBAN]', 'Bitte geben Sie die IBAN des Konto\'s an');
            $Error = true;
        } else {
//            if (Banking::useService()->getIBANIsUsed($Account['IBAN'])) {     //ToDO Doppelte IBAN erlauben?
//                $Stage->setError('Account[IBAN]', 'IBAN-Nummer schon vergeben');
//                $Error = true;
//            }

        }

//        if (isset( $Account['LeadTimeFirst'] ) && empty( $Account['LeadTimeFirst'] )) {
//            $Stage->setError('Account[LeadTimeFirst]', 'Bitte geben sie den Ersteinzug an.');
//            $Error = true;
//        }
//        if (isset( $Account['LeadTimeFirst'] ) && !is_numeric($Account['LeadTimeFirst'])) {
//            $Stage->setError('Account[LeadTimeFirst]', 'Bitte geben sie eine Zahl an.');
//            $Error = true;
//        }
//        if (isset( $Account['LeadTimeFollow'] ) && empty( $Account['LeadTimeFollow'] )) {
//            $Stage->setError('Account[LeadTimeFollow]', 'Bitte geben sie den Folgeeinzug an.');
//            $Error = true;
//        }
//        if (isset( $Account['LeadTimeFollow'] ) && !is_numeric($Account['LeadTimeFollow'])) {
//            $Stage->setError('Account[LeadTimeFollow]', 'Bitte geben sie eine Zahl an.');
//            $Error = true;
//        }

        if (!$Error) {
            if ((new Data ($this->getBinding()))->createAccount(
                $tblPerson,
//                $Account['LeadTimeFirst'],
//                $Account['LeadTimeFollow'],
                $Account['BankName'],
                $Account['Owner'],
                $Account['CashSign'],
                $Account['IBAN'],
                $Account['BIC'])
            ) {
                return new Success('Das Konto ist erfasst worden')
                .new Redirect('/Billing/Accounting/BankAccount/', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Warning('Das Konto konnte nicht erfasst werden')
                .new Redirect('/Billing/Accounting/BankAccount', Redirect::TIMEOUT_ERROR);
            }

        }
        return $Stage;
    }

    public function changeAccount(
        IFormInterface &$Stage = null,
        TblBankAccount $tblBankAccount,
        $Account
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Account) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Account['BankName'] ) && empty( $Account['BankName'] )) {
            $Stage->setError('Account[BankName]', 'Bitte geben Sie den Namen der Bank an');
            $Error = true;
        }
        if (isset( $Account['Owner'] ) && empty( $Account['Owner'] )) {
            $Stage->setError('Account[Owner]', 'Bitte geben Sie den Besitzer des Konto\'s an');
            $Error = true;
        }
        if (isset( $Account['IBAN'] ) && empty( $Account['IBAN'] )) {
            $Stage->setError('Account[IBAN]', 'Bitte geben Sie die IBAN des Konto\'s an');
            $Error = true;
        } else {
//            if (($tblBankAcc =  Banking::useService()->getIBANIsUsed($Account['IBAN']))) {    //ToDO Doppelte IBAN erlauben?
//                if($tblBankAcc->getId() !== $tblBankAccount->getId())
//                {
//                    $Stage->setError('Account[IBAN]', 'IBAN-Nummer schon vergeben');
//                    $Error = true;
//                }
//            }
        }

        if (!$Error) {
            if ((new Data ($this->getBinding()))->updateAccount(
                $tblBankAccount,
                $Account['Owner'],
                $Account['IBAN'],
                $Account['BIC'],
                $Account['CashSign'],
                $Account['BankName']
            )
            ) {
                return new Success('Das Konto ist geändert worden')
                .new Redirect('/Billing/Accounting/BankAccount/View', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblBankAccount->getId()));
            } else {
                return new Warning('Das Konto konnte nicht geändert werden werden')
                .new Redirect('/Billing/Accounting/BankAccount/View', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblBankAccount->getId()));
            }

        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblPerson           $tblPerson
     * @param                     $Reference
     *
     * @return IFormInterface|string
     */
    public function createReference(
        IFormInterface &$Stage = null,
        TblPerson $tblPerson,
        $Reference
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Reference) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Reference['Reference'] ) && empty( $Reference['Reference'] )) {
            $Stage->setError('Reference[Reference]', 'Bitte geben sie eine Mandatsreferenz an');
            $Error = true;
        } else {
            if (Banking::useService()->getReferenceIsUsed($Reference['Reference'])) {
                $Stage->setError('Reference[Reference]', 'Referenz ist schon vergeben');
                $Error = true;
            }
        }

        if (!$Error) {

            (new Data($this->getBinding()))->createReference(
                $tblPerson,
                $Reference['Reference'],
                $Reference['ReferenceDate']);

            return new Success('Die Referenz ist erfasst worden')
            .new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }

    public function changeReference(
        IFormInterface &$Stage = null,
        TblBankReference $tblBankReference,
        $Reference
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Reference) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Reference['ReferenceDate'] ) && empty( $Reference['ReferenceDate'] )) {
            $Stage->setError('Reference[ReferenceDate]', 'Bitte geben sie eine Datum an');
            $Error = true;
        }

        if (!$Error) {

            (new Data($this->getBinding()))->updateReference(
                $tblBankReference,
                $Reference['ReferenceDate']);

            return new Success('Die Referenzdatum ist geändert worden')
            .new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_SUCCESS);
        }

        return $Stage;
    }
}
