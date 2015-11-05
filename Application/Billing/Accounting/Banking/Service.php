<?php
namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Data;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorCommodity;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;

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
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblReference[]
     */
    public function getReferenceByDebtor(TblDebtor $tblDebtor)
    {

        return (new Data($this->getBinding()))->getReferenceByDebtor($tblDebtor);
    }

    /**
     * @param TblDebtor    $tblDebtor
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblReference
     */
    public function getReferenceByDebtorAndCommodity(TblDebtor $tblDebtor, TblCommodity $tblCommodity)
    {

        return (new Data($this->getBinding()))->getReferenceByDebtorAndCommodity($tblDebtor, $tblCommodity);
    }

    /**
     * @param TblAccount   $tblAccount
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblReference
     */
    public function getReferenceByAccountAndCommodity(TblAccount $tblAccount, TblCommodity $tblCommodity)
    {

        return (new Data($this->getBinding()))->getReferenceByAccountAndCommodity($tblAccount, $tblCommodity);
    }

    /**
     * @param $Id
     *
     * @return bool|TblDebtorCommodity
     */
    public function getDebtorCommodityById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorCommodityById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblReference
     */
    public function getReferenceById($Id)
    {

        return (new Data($this->getBinding()))->getReferenceById($Id);
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
     * @param TblDebtor    $tblDebtor
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblDebtor[]
     */
    public function getDebtorCommodityAllByDebtorAndCommodity(TblDebtor $tblDebtor, TblCommodity $tblCommodity)
    {

        return (new Data($this->getBinding()))->getDebtorCommodityAllByDebtorAndCommodity($tblDebtor, $tblCommodity);
    }

    /**
     * @param $ServiceManagement_Person
     *
     * @return bool|TblDebtor[]
     */
    public function getDebtorByServiceManagementPerson($ServiceManagement_Person)
    {

        return (new Data($this->getBinding()))->getDebtorByServiceManagementPerson($ServiceManagement_Person);
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
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblCommodity[]
     */
    public function getCommodityAllByDebtor(TblDebtor $tblDebtor)
    {

        $tblDebtorCommodityList = $this->getCommodityDebtorAllByDebtor($tblDebtor);
        $tblCommodity = array();
        foreach ($tblDebtorCommodityList as $tblDebtorCommodity) {
            array_push($tblCommodity, $tblDebtorCommodity->getServiceBillingCommodity());
        }
        return $tblCommodity;
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblDebtorCommodity[]
     */
    public function getCommodityDebtorAllByDebtor(TblDebtor $tblDebtor)
    {

        return (new Data($this->getBinding()))->getCommodityDebtorAllByDebtor($tblDebtor);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblReference[]
     */
    public function getReferenceByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getReferenceByAccount($tblAccount);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return TblReference[]
     */
    public function getReferenceActiveByAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->getReferenceActiveByAccount($tblAccount);
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return int
     */
    public function getLeadTimeByDebtor(TblDebtor $tblDebtor)   //ToDO get first/followLeadTime from School
    {

        if ($tblAccount = Banking::useService()->getActiveAccountByDebtor($tblDebtor)) {
            if (Invoice::useService()->checkInvoiceFromDebtorIsPaidByDebtor($tblDebtor) ||
                Balance::useService()->checkPaymentFromDebtorExistsByDebtor($tblDebtor)
            ) {
                return $tblAccount->getLeadTimeFollow();
            } else {
                return $tblAccount->getLeadTimeFirst();
            }
        }
        return false;
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool|Service\Entity\TblAccount
     */
    public function getActiveAccountByDebtor(TblDebtor $tblDebtor)
    {

        return (new Data($this->getBinding()))->getActiveAccountByDebtor($tblDebtor);
    }

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
            if (!$tblInvoice->getIsVoid()) {
                if (!$tblInvoice->getIsPaid()) {
                    if (!$tblInvoice->getIsConfirmed()) {
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
                .new Redirect('/Billing/Accounting/Banking', 1);
            } else {
                return new Danger('Der Debitor konnte nicht gelöscht werden')
                .new Redirect('/Billing/Accounting/Banking', 3);
            }
        }
        return new Danger('Es bestehen noch offene Rechnungen mit diesem Debitor')
        .new Redirect('/Billing/Accounting/Banking', 3);
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
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblAccount[]
     */
    public function getAccountAllByDebtor(TblDebtor $tblDebtor)
    {

        return (new Data($this->getBinding()))->getAccountByDebtor($tblDebtor);
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool
     */
    public function destroyAccount(TblAccount $tblAccount)
    {

        return (new Data($this->getBinding()))->destroyAccount($tblAccount);
    }

    /**
     * @param TblDebtorCommodity $tblDebtorCommodity
     *
     * @return string
     */
    public function removeCommodityToDebtor(TblDebtorCommodity $tblDebtorCommodity)
    {

        if ((new Data($this->getBinding()))->removeCommodityToDebtor($tblDebtorCommodity)) {
            return new Success('Die Leistung '.$tblDebtorCommodity->getServiceBillingCommodity()->getName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Accounting/Banking/Commodity/Select', 0,
                array('Id' => $tblDebtorCommodity->getTblDebtor()->getId()));
        } else {
            return new Warning('Die Leistung '.$tblDebtorCommodity->getServiceBillingCommodity()->getName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Accounting/Banking/Commodity/Select', 3,
                array('Id' => $tblDebtorCommodity->getTblDebtor()->getId()));
        }
    }

    /**
     * @param TblDebtor    $tblDebtor
     * @param TblCommodity $tblCommodity
     *
     * @return string
     */
    public function addCommodityToDebtor(TblDebtor $tblDebtor, TblCommodity $tblCommodity)
    {

        if ((new Data($this->getBinding()))->addCommodityToDebtor($tblDebtor, $tblCommodity)) {
            return new Success('Die Leistung '.$tblCommodity->getName().' wurde erfolgreich hinzugefügt')
            .new Redirect('/Billing/Accounting/Banking/Commodity/Select', 0, array('Id' => $tblDebtor->getId()));
        } else {
            return new Warning('Die Leistung '.$tblCommodity->getName().' konnte nicht hinzugefügt werden')
            .new Redirect('/Billing/Accounting/Banking/Commodity/Select', 3, array('Id' => $tblDebtor->getId()));
        }
    }

    /**
     * @param TblReference $tblReference
     * @param              $AccountId
     *
     * @return string
     */
    public function deactivateBankingReference(TblReference $tblReference, $AccountId)
    {

        if (null === $tblReference) {
            return '';
        }
        if ((new Data($this->getBinding()))->deactivateReference($tblReference)) {
            return new Success('Die Deaktivierung ist erfasst worden')
            .new Redirect('/Billing/Accounting/Banking/Debtor/Reference', 1,
                array('DebtorId'  => $tblReference->getServiceTblDebtor()->getId(),
                      'AccountId' => $AccountId));
        } else {
            return new Danger('Die Referenz konnte nicht deaktiviert werden')
            .new Redirect('/Billing/Accounting/Banking/Debtor/Reference', 3,
                array('DebtorId'  => $tblReference->getServiceTblDebtor()->getId(),
                      'AccountId' => $AccountId));
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
        $tblAccount = Banking::useService()->getAccountById($Account);
        $tblOldAccount = Banking::useService()->getActiveAccountByDebtor($tblDebtor);

        if (!empty( $tblOldAccount )) {
            (new Data($this->getBinding()))->deactivateAccount($tblOldAccount);
        }

        if ((new Data($this->getBinding()))->activateAccount($tblAccount)) {
            return new Success('Das Konto wurde als aktives Konto gesetzt')
            .new Redirect($Path, 1, array('Id' => $IdBack));
        } else {
            return new Warning('Das Konto konte nicht als aktives Konto gesetzt werden')
            .new Redirect($Path, 10, array('Id' => $IdBack));
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
     * @return bool|Service\Entity\TblAccount
     */
    public function getAccountById($Id)
    {

        return (new Data($this->getBinding()))->getAccountById($Id);
    }

    /**
     * @param IFormInterface $Stage
     * @param TblDebtor      $tblDebtor
     * @param                $Debtor
     *
     * @return IFormInterface|string
     */
    public function changeDebtor(IFormInterface &$Stage = null, TblDebtor $tblDebtor, $Debtor)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Debtor
        ) {
            return $Stage;
        }

        $Error = false;
//        if (isset( $Debtor['Description'] ) && empty( $Debtor['Description'] )) {
//            $Stage->setError('Debtor[Description]', 'Bitte geben sie eine Beschreibung an');
//            $Error = true;
//        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateDebtor(
                $tblDebtor,
                $Debtor['Description']
            )
            ) {
                $Stage .= new Success('Änderungen sind erfasst')
                    .new Redirect('/Billing/Accounting/Banking/Debtor/View', 2, array('Id' => $tblDebtor->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Accounting/Banking', 3);
            }
            return $Stage;
        }
        return $Stage;
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
            .new Redirect('/Billing/Accounting/Banking/Debtor/View', 1, array('Id' => $tblDebtor->getId()));
        } else {
            return new Warning('Die Zahlungsart konnte nicht geändert werden')
            .new Redirect('/Billing/Accounting/Banking/Debtor/View', 10, array('Id' => $tblDebtor->getId()));
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
     * @param IFormInterface|null $Stage
     * @param                     $DebtorId
     * @param                     $AccountId
     * @param                     $Account
     *
     * @return IFormInterface|string
     */
    public function changeAccount(IFormInterface &$Stage = null, $DebtorId, $AccountId, $Account)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Account
        ) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Account['Owner'] ) && empty( $Account['Owner'] )) {
            $Stage->setError('Account[Owner]', 'Bitte geben sie einen Besitzer an');
            $Error = true;
        }
        if (isset( $Account['IBAN'] ) && empty( $Account['IBAN'] )) {
            $Stage->setError('Account[IBAN]', 'Bitte geben sie eine IBAN an');
            $Error = true;
        }
//        if (isset( $Account['BIC'] ) && empty( $Account['BIC'] )) {
//            $Stage->setError('Account[BIC]', 'Bitte geben sie einen Besitzer an');
//            $Error = true;
//        }
        if (isset( $Account['BankName'] ) && empty( $Account['BankName'] )) {
            $Stage->setError('Account[BankName]', 'Bitte geben sie einen Besitzer an');
            $Error = true;
        }
//        if (isset( $Account['CashSign'] ) && empty( $Account['CashSign'] )) {
//            $Stage->setError('Account[CashSign]', 'Bitte geben sie einen Besitzer an');
//            $Error = true;
//        }

        $tblDebtor = Banking::useService()->getDebtorById($DebtorId);

        if (!$Error) {

            if ((new Data($this->getBinding()))->updateAccount(
                Banking::useService()->getAccountById($AccountId),
                $Account['Owner'],
                $Account['IBAN'],
                $Account['BIC'],
                $Account['CashSign'],
                $Account['BankName'],
                $DebtorId
            )
            ) {
                $Stage .= new Success('Änderungen sind erfasst')
                    .new Redirect('/Billing/Accounting/Banking/Debtor/View', 2, array('Id' => $tblDebtor->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Accounting/Banking', 3);
            }
            return $Stage;
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblReference        $tblReference
     * @param                     $DebtorId
     * @param                     $AccountId
     * @param                     $Reference
     *
     * @return IFormInterface|string
     */
    public function changeReference(
        IFormInterface &$Stage = null,
        TblReference $tblReference,
        $DebtorId,
        $AccountId,
        $Reference
    ) {

        /**
         * Skip to Frontend
         */
        if (null === $Reference
        ) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Reference['Date'] ) && empty( $Reference['Date'] )) {
            $Stage->setError('Reference[Date]', 'Bitte geben sie ein Datum an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateReference(
                $tblReference,
                $Reference['Date']
            )
            ) {
                $Stage .= new Success('Änderungen sind erfasst')
                    .new Redirect('/Billing/Accounting/Banking/Debtor/Reference', 1, array('DebtorId'  => $DebtorId,
                                                                                           'AccountId' => $AccountId));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Accounting/Banking/Debtor/Reference', 10, array('Id'        => $DebtorId,
                                                                                            'AccountId' => $AccountId));
            }
            return $Stage;
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblDebtor           $tblDebtor
     * @param TblAccount          $tblAccount
     * @param                     $Reference
     *
     * @return IFormInterface|string
     */
    public function createReference(IFormInterface &$Stage = null, TblDebtor $tblDebtor, TblAccount $tblAccount, $Reference)
    {

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
        }
        if (isset( $Reference['Reference'] ) && Banking::useService()->getReferenceByReferenceActive($Reference['Reference'])) {
            $Stage->setError('Reference[Reference]',
                'Die Mandatsreferenz exisitiert bereits. Bitte geben Sie eine andere an');
            $Error = true;
        }

        if (!$Error) {

            (new Data($this->getBinding()))->createReference($Reference['Reference'],
                $tblDebtor->getDebtorNumber(),
                $Reference['ReferenceDate'],
                Commodity::useService()->getCommodityById($Reference['Commodity']),
                $tblAccount);

            return new Success('Die Referenz ist erfasst worden')
            .new Redirect('/Billing/Accounting/Banking/Debtor/Reference', 0, array('DebtorId'  => $tblDebtor->getId(),
                                                                                   'AccountId' => $tblAccount->getId()));
        }

        return $Stage;
    }

    /**
     * @param $Reference
     *
     * @return bool|TblReference
     */
    public function getReferenceByReferenceActive($Reference)
    {

        return (new Data($this->getBinding()))->getReferenceByReferenceActive($Reference);
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
//        if (isset( $Debtor['Reference'] ) && Banking::useService()->getReferenceByReference($Debtor['Reference'])) {
//            $Stage->setError('Debtor[Reference]',
//                'Die Mandatsreferenz exisitiert bereits. Bitte geben Sie eine andere an');
//            $Error = true;
//        }

        if (!$Error) {

            (new Data($this->getBinding()))->createDebtor($Debtor['DebtorNumber'],
                $Debtor['Description'],
                Person::useService()->getPersonById($Id),
                $Debtor['PaymentType']);

//            if (!empty( $Debtor['Reference'] )) {
//                (new Data ($this->getBinding()))->createReference($Debtor['Reference'],
//                    $Debtor['DebtorNumber'],
//                    $Debtor['ReferenceDate'],
//                    Commodity::useService()->getCommodityById($Debtor['Commodity']));
//            }
            return new Success('Der Debitor ist erfasst worden')
            .new Redirect('/Billing/Accounting/Banking', 2);
        }
        return $Stage;
    }

    /**
     * @param IFormInterface|null $Stage
     * @param                     $Account
     * @param TblDebtor           $tblDebtor
     *
     * @return IFormInterface|string
     */
    public function createAccount(IFormInterface &$Stage = null, $Account, TblDebtor $tblDebtor)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Account) {
            return $Stage;
        }

        $Error = false;
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
            if (Banking::useService()->getActiveAccountByDebtor($tblDebtor)) {
                $Account['Active'] = false;
            } else {
                $Account['Active'] = true;
            }
            if ((new Data ($this->getBinding()))->createAccount(
//                $Account['LeadTimeFirst'],
//                $Account['LeadTimeFollow'],
                $Account['BankName'],
                $Account['Owner'],
                $Account['CashSign'],
                $Account['IBAN'],
                $Account['BIC'],
                $Account['Active'],
                $tblDebtor)
            ) {
                return new Success('Der Debitor ist erfasst worden')
                .new Redirect('/Billing/Accounting/Banking/Debtor/View', 1, array('Id' => $tblDebtor->getId()));
            } else {
                return new Warning('Der Debitor konnte nicht erfasst werden')
                .new Redirect('/Billing/Accounting/Banking/Debtor/View', 10, array('Id' => $tblDebtor->getId()));
            }

        }
        return $Stage;
    }

    /**
     * @param $Reference
     *
     * @return bool|TblReference
     */
    public function getReferenceByReference($Reference)
    {

        return (new Data($this->getBinding()))->getReferenceByReference($Reference);
    }
}
