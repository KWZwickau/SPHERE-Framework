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
     * @param $Id
     *
     * @return bool|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorById($Id);
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
     * @param $Id
     *
     * @return bool|TblPaymentType
     */
    public function getPaymentTypeById($Id)
    {

        return (new Data($this->getBinding()))->getPaymentTypeById($Id);
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
     * @param TblDebtor $tblDebtor
     *
     * @return int
     */
    public function getLeadTimeByDebtor(TblDebtor $tblDebtor)
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
    public function destroyBanking(TblDebtor $tblDebtor)
    {

        if (null === $tblDebtor) {
            return '';
        }

        if ((new Data($this->getBinding()))->removeBanking($tblDebtor)) {
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
     *
     * @return string
     */
    public function deactivateBankingReference(TblReference $tblReference)
    {

        if (null === $tblReference) {
            return '';
        }
        if ((new Data($this->getBinding()))->deactivateReference($tblReference)) {
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
    public function changeDebtor(IFormInterface &$Stage = null, TblDebtor $tblDebtor, $Debtor) //ToDO
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
            if ((new Data($this->getBinding()))->updateDebtor(
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
    public function createReference(IFormInterface &$Stage = null, TblDebtor $Debtor, $Reference)
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
                $Debtor->getDebtorNumber(),
                $Reference['ReferenceDate'],
                Commodity::useService()->getCommodityById($Reference['Commodity']));

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
        if (isset( $Debtor['Reference'] ) && Banking::useService()->getReferenceByReference($Debtor['Reference'])) {
            $Stage->setError('Debtor[Reference]',
                'Die Mandatsreferenz exisitiert bereits. Bitte geben Sie eine andere an');
            $Error = true;
        }

        if (!$Error) {

            (new Data($this->getBinding()))->createDebtor($Debtor['DebtorNumber'],
                $Debtor['LeadTimeFirst'],
                $Debtor['LeadTimeFollow'],
                $Debtor['BankName'],
                $Debtor['Owner'],
                $Debtor['CashSign'],
                $Debtor['IBAN'],
                $Debtor['BIC'],
                $Debtor['Description'],
                $Debtor['PaymentType'],
                Person::useService()->getPersonById($Id));
            if (!empty( $Debtor['Reference'] )) {
                (new Data ($this->getBinding()))->createReference($Debtor['Reference'],
                    $Debtor['DebtorNumber'],
                    $Debtor['ReferenceDate'],
                    Commodity::useService()->getCommodityById($Debtor['Commodity']));
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
    public function getDebtorByDebtorNumber($DebtorNumber)
    {

        return (new Data($this->getBinding()))->getDebtorByDebtorNumber($DebtorNumber);
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
