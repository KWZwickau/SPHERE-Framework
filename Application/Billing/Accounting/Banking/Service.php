<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Data;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorCommodity;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Setup;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

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
     * @param bool $Simulate
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($Simulate, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($Simulate);
        if (!$Simulate && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @return bool|TblDebtor[]
     */
    public function entityDebtorAll()
    {

        return (new Data($this->Binding))->entityDebtorAll();
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblReference[]
     */
    public function entityReferenceByDebtor(TblDebtor $tblDebtor)
    {

        return (new Data($this->Binding))->entityReferenceByDebtor($tblDebtor);
    }

    /**
     * @param $Id
     *
     * @return bool|TblDebtor
     */
    public function entityDebtorById($Id)
    {

        return (new Data($this->Binding))->entityDebtorById($Id);
    }

    /**
     * @param TblDebtor    $tblDebtor
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblReference
     */
    public function entityReferenceByDebtorAndCommodity(TblDebtor $tblDebtor, TblCommodity $tblCommodity)
    {

        return (new Data($this->Binding))->entityReferenceByDebtorAndCommodity($tblDebtor, $tblCommodity);
    }

    /**
     * @param $Id
     *
     * @return bool|TblPaymentType
     */
    public function entityPaymentTypeById($Id)
    {

        return (new Data($this->Binding))->entityPaymentTypeById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblDebtorCommodity
     */
    public function entityDebtorCommodityById($Id)
    {

        return (new Data($this->Binding))->entityDebtorCommodityById($Id);
    }

    /**
     * @param $Id
     *
     * @return bool|TblReference
     */
    public function entityReferenceById($Id)
    {

        return (new Data($this->Binding))->entityReferenceById($Id);
    }

    /**
     * @return bool|TblPaymentType[]
     */
    public function entityPaymentTypeAll()
    {

        return (new Data($this->Binding))->entityPaymentTypeAll();
    }

    /**
     * @param TblPerson $Person
     *
     * @return bool|TblDebtor[]
     */
    public function entityDebtorAllByPerson(TblPerson $Person)    //todo
    {

        return (new Data($this->Binding))->entityDebtorAllByPerson($Person);
    }

    /**
     * @param TblDebtor    $tblDebtor
     * @param TblCommodity $tblCommodity
     *
     * @return bool|TblDebtor[]
     */
    public function entityDebtorCommodityAllByDebtorAndCommodity(TblDebtor $tblDebtor, TblCommodity $tblCommodity)
    {

        return (new Data($this->Binding))->entityDebtorAllByPerson($tblDebtor, $tblCommodity);
    }

    /**
     * @param $ServiceManagement_Person
     *
     * @return bool|TblDebtor[]
     */
    public function entityDebtorByServiceManagementPerson($ServiceManagement_Person)
    {

        return (new Data($this->Binding))->entityDebtorByServiceManagementPerson($ServiceManagement_Person);
    }

    /**
     * @param $PaymentType
     *
     * @return bool|TblPaymentType
     */
    public function entityPaymentTypeByName($PaymentType)
    {

        return (new Data($this->Binding))->entityPaymentTypeByName($PaymentType);
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool|TblCommodity[]
     */
    public function entityCommodityAllByDebtor(TblDebtor $tblDebtor)
    {

        $tblDebtorCommodityList = $this->entityCommodityDebtorAllByDebtor($tblDebtor);
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
    public function entityCommodityDebtorAllByDebtor(TblDebtor $tblDebtor)
    {

        return (new Data($this->Binding))->entityCommodityDebtorAllByDebtor($tblDebtor);
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return int
     */
    public function entityLeadTimeByDebtor(TblDebtor $tblDebtor)
    {

        if (Invoice::useService()->checkInvoiceFromDebtorIsPaidByDebtor($tblDebtor) || Balance::useService()->checkPaymentFromDebtorExistsByDebtor($tblDebtor)) {
            return $tblDebtor->getLeadTimeFollow();
        } else {
            return $tblDebtor->getLeadTimeFirst();
        }
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return string
     */
    public function executeBankingDelete(TblDebtor $tblDebtor)
    {

        if (null === $tblDebtor) {
            return '';
        }

        if ((new Data($this->Binding))->actionRemoveBanking($tblDebtor)) {
            return new Success('Die Leistung wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Accounting/Banking', 1);
        } else {
            return new Danger('Die Leistung konnte nicht gelöscht werden')
            .new Redirect('/Billing/Accounting/Banking', 3);
        }
    }

    /**
     * @param TblDebtorCommodity $tblDebtorCommodity
     *
     * @return string
     */
    public function executeRemoveDebtorCommodity(TblDebtorCommodity $tblDebtorCommodity)
    {

        if ((new Data($this->Binding))->actionRemoveDebtorCommodity($tblDebtorCommodity)) {
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
    public function executeAddDebtorCommodity(TblDebtor $tblDebtor, TblCommodity $tblCommodity)
    {

        if ((new Data($this->Binding))->actionAddDebtorCommodity($tblDebtor, $tblCommodity)) {
            return new Success('Die Leistung '.$tblCommodity->getName().' wurde erfolgreich hinzugefügt')
            .new Redirect('/Billing/Accounting/Banking/Commodity/Select', 0, array('Id' => $tblDebtor->getId()));
        } else {
            return new Warning('Die Leistung '.$tblCommodity->getName().' konnte nicht hinzugefügt werden')
            .new Redirect('/Billing/Accounting/Banking/Commodity/Select', 3, array('Id' => $tblDebtor->getId()));
        }
    }

    /**
     * @param TblReference $tblReference
     *
     * @return string
     */
    public function setBankingReferenceDeactivate(TblReference $tblReference)
    {

        if (null === $tblReference) {
            return '';
        }
        if ((new Data($this->Binding))->actionDeactivateReference($tblReference)) {
            return new Success('Die Deaktivierung ist erfasst worden')
            .new Redirect('/Billing/Accounting/Banking/Debtor/Reference', 1,
                array('Id' => $tblReference->getServiceBillingBanking()->getId()));
        } else {
            return new Danger('Die Referenz konnte nicht deaktiviert werden')
            .new Redirect('/Billing/Accounting/Banking/Debtor/Reference', 3,
                array('Id' => $tblReference->getServiceBillingBanking()->getId()));
        }
    }

    /**
     * @param IFormInterface $Stage
     * @param TblDebtor      $tblDebtor
     * @param                $Debtor
     *
     * @return IFormInterface|string
     */
    public function executeEditDebtor(IFormInterface &$Stage = null, TblDebtor $tblDebtor, $Debtor)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Debtor
        ) {
            return $Stage;
        }

        $Error = false;
        if (isset( $Debtor['LeadTimeFirst'] ) && empty( $Debtor['LeadTimeFirst'] )) {
            $Stage->setError('Debtor[LeadTimeFirst]', 'Bitte geben sie eine Vorlaufzeit ein');
            $Error = true;
        }
        if (isset( $Debtor['LeadTimeFollow'] ) && empty( $Debtor['LeadTimeFollow'] )) {
            $Stage->setError('Debtor[LeadTimeFollow]', 'Bitte geben sie eine Vorlaufzeit ein');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->Binding))->actionEditDebtor(
                $tblDebtor,
                $Debtor['Description'],
                $Debtor['PaymentType'],
                $Debtor['Owner'],
                $Debtor['IBAN'],
                $Debtor['BIC'],
                $Debtor['CashSign'],
                $Debtor['BankName'],
                $Debtor['LeadTimeFirst'],
                $Debtor['LeadTimeFollow']
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
     * @param IFormInterface $Stage
     * @param TblDebtor      $Debtor
     * @param                $Reference
     *
     * @return IFormInterface|string
     */
    public function executeAddReference(IFormInterface &$Stage = null, TblDebtor $Debtor, $Reference)
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
        if (isset( $Reference['Reference'] ) && Banking::useService()->entityReferenceByReferenceActive($Reference['Reference'])) {
            $Stage->setError('Reference[Reference]',
                'Die Mandatsreferenz exisitiert bereits. Bitte geben Sie eine andere an');
            $Error = true;
        }

        if (!$Error) {

            (new Data($this->Binding))->actionAddReference($Reference['Reference'],
                $Debtor->getDebtorNumber(),
                $Reference['ReferenceDate'],
                Commodity::useService()->entityCommodityById($Reference['Commodity']));

            return new Success('Die Referenz ist erfasst worden')
            .new Redirect('/Billing/Accounting/Banking/Debtor/Reference', 0, array('Id' => $Debtor->getId()));
        }

        return $Stage;
    }

    /**
     * @param $Reference
     *
     * @return bool|TblReference
     */
    public function entityReferenceByReferenceActive($Reference)
    {

        return (new Data($this->Binding))->entityReferenceByReferenceActive($Reference);
    }

    /**
     * @param IFormInterface $Stage
     * @param                $Debtor
     * @param                $Id
     *
     * @return IFormInterface|string
     */
    public function executeAddDebtor(IFormInterface &$Stage = null, $Debtor, $Id)
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
        if (isset( $Debtor['DebtorNumber'] ) && Banking::useService()->entityDebtorByDebtorNumber($Debtor['DebtorNumber'])) {
            $Stage->setError('Debtor[DebtorNumber]',
                'Die Debitorennummer exisitiert bereits. Bitte geben Sie eine andere Debitorennummer an');
            $Error = true;
        }
        if (isset( $Debtor['LeadTimeFirst'] ) && empty( $Debtor['LeadTimeFirst'] )) {
            $Stage->setError('Debtor[LeadTimeFirst]', 'Bitte geben sie den Ersteinzug an.');
            $Error = true;
        }
        if (isset( $Debtor['LeadTimeFirst'] ) && !is_numeric($Debtor['LeadTimeFirst'])) {
            $Stage->setError('Debtor[LeadTimeFirst]', 'Bitte geben sie eine Zahl an.');
            $Error = true;
        }
        if (isset( $Debtor['LeadTimeFollow'] ) && empty( $Debtor['LeadTimeFollow'] )) {
            $Stage->setError('Debtor[LeadTimeFollow]', 'Bitte geben sie den Folgeeinzug an.');
            $Error = true;
        }
        if (isset( $Debtor['LeadTimeFollow'] ) && !is_numeric($Debtor['LeadTimeFollow'])) {
            $Stage->setError('Debtor[LeadTimeFollow]', 'Bitte geben sie eine Zahl an.');
            $Error = true;
        }
        if (isset( $Debtor['Reference'] ) && Banking::useService()->entityReferenceByReference($Debtor['Reference'])) {
            $Stage->setError('Debtor[Reference]',
                'Die Mandatsreferenz exisitiert bereits. Bitte geben Sie eine andere an');
            $Error = true;
        }

        if (!$Error) {

            (new Data($this->Binding))->actionAddDebtor($Debtor['DebtorNumber'],
                $Debtor['LeadTimeFirst'],
                $Debtor['LeadTimeFollow'],
                $Debtor['BankName'],
                $Debtor['Owner'],
                $Debtor['CashSign'],
                $Debtor['IBAN'],
                $Debtor['BIC'],
                $Debtor['Description'],
                $Debtor['PaymentType'],
                Management::servicePerson()->entityPersonById($Id)); //todo
            if (!empty( $Debtor['Reference'] )) {
                (new Data ($this->Binding))->actionAddReference($Debtor['Reference'],
                    $Debtor['DebtorNumber'],
                    $Debtor['ReferenceDate'],
                    Commodity::useService()->entityCommodityById($Debtor['Commodity']));
            }
            return new Success('Der Debitor ist erfasst worden')
            .new Redirect('/Billing/Accounting/Banking', 2);
        }
        return $Stage;
    }

    /**
     * @param $DebtorNumber
     *
     * @return bool|TblDebtor
     */
    public function entityDebtorByDebtorNumber($DebtorNumber)
    {

        return (new Data($this->Binding))->entityDebtorByDebtorNumber($DebtorNumber);
    }

    /**
     * @param $Reference
     *
     * @return bool|TblReference
     */
    public function entityReferenceByReference($Reference)
    {

        return (new Data($this->Binding))->entityReferenceByReference($Reference);
    }

}
