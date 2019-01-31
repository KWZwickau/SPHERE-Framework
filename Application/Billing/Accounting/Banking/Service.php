<?php
namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Data;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorNumber;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPersonBilling;
use SPHERE\Application\Billing\Accounting\Banking\Service\Setup;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
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
     * @param $Id
     *
     * @return false|TblBankAccount
     */
    public function getBankAccountById($Id)
    {

        return (new Data($this->getBinding()))->getBankAccountById($Id);
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
     * @param $Id
     *
     * @return false|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorNumber
     */
    public function getDebtorNumberById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorNumberById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblPersonBilling
     */
    public function getPersonBillingById($Id)
    {

        return (new Data($this->getBinding()))->getPersonBillingById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblBankAccount
     */
    public function getBankAccountAll($Id)
    {

        return (new Data($this->getBinding()))->getBankAccountAll($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblBankReference
     */
    public function getBankReferenceAll($Id)
    {

        return (new Data($this->getBinding()))->getBankReferenceAll($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtor
     */
    public function getDebtorAll($Id)
    {

        return (new Data($this->getBinding()))->getDebtorAll($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorNumber
     */
    public function getDebtorNumberAll($Id)
    {

        return (new Data($this->getBinding()))->getDebtorNumberAll($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtorSelection
     */
    public function getDebtorSelectionAll($Id)
    {

        return (new Data($this->getBinding()))->getDebtorSelectionAll($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblPersonBilling
     */
    public function getPersonBillingAll($Id)
    {

        return (new Data($this->getBinding()))->getPersonBillingAll($Id);
    }

    /**
     * @param IFormInterface $Stage
     * @param                $Debtor
     * @param                $Id
     *
     * @return IFormInterface|string
     */
    public function createDebtor(IFormInterface &$Stage, $Debtor, $Id)
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
                (new Data($this->getBinding()))->createDebtor($tblPerson, $Debtor['DebtorNumber']);

                return new Success('Der Debitor ist erfasst worden')
                .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblPerson->getId()));
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
    public function changeDebtor(IFormInterface &$Stage, TblDebtor $tblDebtor, $Debtor)
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
            if ($tblDebtor->getId() !== Banking::useService()->getDebtorByDebtorNumber($Debtor['DebtorNumber'])->getId()) {
                $Stage->setError('Debtor[DebtorNumber]',
                    'Die Debitorennummer exisitiert bereits. Bitte geben Sie eine andere Debitorennummer an');
                $Error = true;
            }
        }
        if (!$tblDebtor->getServiceTblPerson()) {
            $Stage->setError('Debtor[DebtorNumber]',
                'Person nicht mehr gefunden');
            $Error = true;
        }

        if (!$Error) {

            if ((new Data($this->getBinding()))->updateDebtor($tblDebtor, $Debtor['DebtorNumber'])) {
                return new Success('Die Debitor-Nummer ist erfasst')
                .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS,
                    array('Id' => $tblDebtor->getServiceTblPerson()->getId()));
            } else {
                return new Danger('Die Debitor-Nummer konnte nicht erfasst werden')
                .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblDebtor->getServiceTblPerson()->getId()));
            }
        }
        return $Stage;
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return bool|string
     */
    public function removeDebtor(TblDebtor $tblDebtor)
    {

        if (null === $tblDebtor) {
            return '';
        }

        if ((new Data($this->getBinding()))->removeDebtor($tblDebtor)) {
            return new Success('Der Debitor wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Accounting/Banking/View', Redirect::TIMEOUT_SUCCESS);
        }

        return new Danger('Der Debitor konnte nicht gelöscht werden')
        .new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
    }
}
