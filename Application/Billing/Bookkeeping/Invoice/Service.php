<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblTempInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Setup;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
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
     * @param TblDebtor $tblDebtor
     *
     * @return bool
     */
    public function checkInvoiceFromDebtorIsPaidByDebtor(TblDebtor $tblDebtor)
    {

        return (new Data($this->getBinding()))->checkInvoiceFromDebtorIsPaidByDebtor($tblDebtor);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return string
     */
    public function sumPriceItemAllStringByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->sumPriceItemAllStringByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return float
     */
    public function sumPriceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->sumPriceItemAllByInvoice($tblInvoice);
    }

    /**
     * @param $IsVoid
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByIsVoidState($IsVoid)
    {

        return (new Data($this->getBinding()))->getInvoiceAllByIsVoidState($IsVoid);
    }

    /**
     * @param $Id
     *
     * @return bool|TblInvoice
     */
    public function getInvoiceById($Id)
    {

        return (new Data($this->getBinding()))->getInvoiceById($Id);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|TblInvoiceItem[]
     */
    public function getInvoiceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getInvoiceItemAllByInvoice($tblInvoice);
    }

    /**
     * @param $Id
     *
     * @return bool|TblInvoiceItem
     */
    public function getInvoiceItemById($Id)
    {

        return (new Data($this->getBinding()))->getInvoiceItemById($Id);
    }

    /**
     * @param $IsPaid
     *
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAllByIsPaidState($IsPaid)
    {

        return (new Data($this->getBinding()))->getInvoiceAllByIsPaidState($IsPaid);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function destroyTempInvoice(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->destroyTempInvoice($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     * @param           $Date
     *
     * @return bool
     */
    public function createInvoiceListFromBasket(TblBasket $tblBasket, $Date)
    {

        return (new Data($this->getBinding()))->createInvoiceListFromBasket($tblBasket, $Date);
    }

    /**
     * @param $Id
     *
     * @return bool|TblTempInvoice
     */
    public function getTempInvoiceById($Id)
    {

        return (new Data($this->getBinding()))->getTempInvoiceById($Id);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool|TblTempInvoice[]
     */
    public function getTempInvoiceAllByBasket(TblBasket $tblBasket)
    {

        return (new Data($this->getBinding()))->getTempInvoiceAllByBasket($tblBasket);
    }

    /**
     * @param $isConfirmed
     *
     * @return array|bool
     */
    public function getInvoiceAllByIsConfirmedState($isConfirmed)
    {

        $invoiceAllByConfirmed = array();
        $invoiceAllByNotConfirmed = array();
        $tblInvoiceAll = $this->getInvoiceAll();

        if ($tblInvoiceAll) {
            foreach ($tblInvoiceAll as $tblInvoice) {
                if ($tblInvoice->getIsConfirmed()) {
                    $invoiceAllByConfirmed[] = $tblInvoice;
                } else {
                    $invoiceAllByNotConfirmed[] = $tblInvoice;
                }
            }
        }

        if ($isConfirmed) {
            if (!empty( $invoiceAllByConfirmed )) {
                return $invoiceAllByConfirmed;
            } else {
                return false;
            }
        } else {
            if (!empty( $invoiceAllByNotConfirmed )) {
                return $invoiceAllByNotConfirmed;
            } else {
                return false;
            }
        }
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAll()
    {

        return (new Data($this->getBinding()))->getInvoiceAll();
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param            $Data
     * @param            $Account
     *
     * @return string
     */
    public function confirmInvoice(TblInvoice $tblInvoice, $Data, $Account)
    {

        If ($Account !== false) {
            $tblAccount = Banking::useService()->getAccountById($Account);
            if (Balance::useService()->createBalance(
                Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber()),
                $tblInvoice,
                null,
                $tblAccount->getBankName(),
                $tblAccount->getIBAN(),
                $tblAccount->getBIC(),
                $tblAccount->getOwner(),
                $tblAccount->getCashSign()
            )
            ) {
                return new Success('Die Rechnung wurde erfolgreich bestätigt und freigegeben')
                .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed', 0);
            } else {
                return new Warning('Die Rechnung wurde konnte nicht bestätigt und freigegeben werden')
                .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 2, array('Id' => $tblInvoice->getId()));
            }
        } else {
            if (Balance::useService()->createBalance(
                Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber()),
                $tblInvoice
            )
            ) {
                return new Success('Die Rechnung wurde erfolgreich bestätigt und freigegeben')
                .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed', 0);
            } else {
                return new Warning('Die Rechnung wurde konnte nicht bestätigt und freigegeben werden')
                .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 2, array('Id' => $tblInvoice->getId()));
            }
        }
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return string
     */
    public function cancelInvoice(
        TblInvoice $tblInvoice
    ) {

        if (!$tblInvoice->getIsConfirmed()) {
            if ((new Data($this->getBinding()))->cancelInvoice($tblInvoice)) {
                return new Success('Die Rechnung wurde erfolgreich storniert')
                .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed', 0);
            } else {
                return new Warning('Die Rechnung konnte nicht storniert werden')
                .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 2,
                    array('Id' => $tblInvoice->getId()));
            }
        } else {
            //TODO cancel confirmed invoice
            if ((new Data($this->getBinding()))->cancelInvoice($tblInvoice)) {
                return new Success('Die Rechnung wurde erfolgreich storniert')
                .new Redirect('/Billing/Bookkeeping/Invoice', 0);
            } else {
                return new Warning('Die Rechnung konnte nicht storniert werden')
                .new Redirect('/Billing/Bookkeeping/Invoice', 2);
            }
        }
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param TblAddress $tblAddress
     *
     * @return string
     */
    public function changeInvoiceAddress(
        TblInvoice $tblInvoice,
        TblAddress $tblAddress
    ) {

        if ((new Data($this->getBinding()))->updateInvoiceAddress($tblInvoice, $tblAddress)) {
            return new Success('Die Rechnungsadresse wurde erfolgreich geändert')
            .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 0, array('Id' => $tblInvoice->getId()));
        } else {
            return new Warning('Die Rechnungsadresse konnte nicht geändert werden')
            .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 2, array('Id' => $tblInvoice->getId()));
        }
    }

    /**
     * @param TblInvoice     $tblInvoice
     * @param TblPaymentType $tblPaymentType
     *
     * @return string
     */
    public function changeInvoicePaymentType(TblInvoice $tblInvoice, TblPaymentType $tblPaymentType)
    {

        if ((new Data($this->getBinding()))->changeInvoicePaymentType($tblInvoice, $tblPaymentType)) {
            return new Success('Die Zahlungsart wurde erfolgreich geändert')
            .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 0, array('Id' => $tblInvoice->getId()));
        } else {
            return new Warning('Die Zahlungsart konnte nicht geändert werden')
            .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 2, array('Id' => $tblInvoice->getId()));
        }
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return string
     */
    public function createPayInvoice(
        TblInvoice $tblInvoice
    ) {

        if ((new Data($this->getBinding()))->createPayInvoice($tblInvoice)) {
            return new Success('Die Rechnung wurde erfolgreich bezahlt')
            .new Redirect('/Billing/Bookkeeping/Balance', 0);
        } else {
            return new Warning('Die Rechnung konnte nicht bezahlt werden')
            .new Redirect('/Billing/Bookkeeping/Balance', 2);
        }
    }

    /**
     * @param IFormInterface $Stage
     * @param TblInvoiceItem $tblInvoiceItem
     * @param                $InvoiceItem
     *
     * @return IFormInterface|string
     */
    public function changeInvoiceItem(IFormInterface &$Stage = null, TblInvoiceItem $tblInvoiceItem, $InvoiceItem)
    {

        /**
         * Skip to Frontend
         */
        if (null === $InvoiceItem
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $InvoiceItem['Price'] ) && empty( $InvoiceItem['Price'] )) {
            $Stage->setError('InvoiceItem[Price]', 'Bitte geben Sie einen Preis an');
            $Error = true;
        }
        if (isset( $InvoiceItem['Quantity'] ) && empty( $InvoiceItem['Quantity'] )) {
            $Stage->setError('InvoiceItem[Quantity]', 'Bitte geben Sie eine Menge an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateInvoiceItem(
                $tblInvoiceItem,
                $InvoiceItem['Price'],
                $InvoiceItem['Quantity']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 1,
                        array('Id' => $tblInvoiceItem->getTblInvoice()->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 2,
                        array('Id' => $tblInvoiceItem->getTblInvoice()->getId()));
            };
        }
        return $Stage;
    }

    /**
     * @param TblInvoiceItem $tblInvoiceItem
     *
     * @return string
     */
    public function removeInvoiceItem(
        TblInvoiceItem $tblInvoiceItem
    ) {

        if ((new Data($this->getBinding()))->destroyInvoiceItem($tblInvoiceItem)) {
            return new Success('Der Artikel '.$tblInvoiceItem->getItemName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 0,
                array('Id' => $tblInvoiceItem->getTblInvoice()->getId()));
        } else {
            return new Warning('Der Artikel '.$tblInvoiceItem->getItemName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 2,
                array('Id' => $tblInvoiceItem->getTblInvoice()->getId()));
        }
    }

    /**
     * @param TblBasket $tblBasket
     * @param TblPerson $tblPerson
     * @param TblDebtor $tblDebtor
     *
     * @return null|TblTempInvoice
     */
    public function createTempInvoice(
        TblBasket $tblBasket,
        TblPerson $tblPerson,
        TblDebtor $tblDebtor
    ) {

        return (new Data($this->getBinding()))->createTempInvoice($tblBasket, $tblPerson, $tblDebtor);
    }

    /**
     * @param TblTempInvoice $tblTempInvoice
     * @param TblCommodity   $tblCommodity
     *
     * @return null|Service\Entity\TblTempInvoiceCommodity
     */
    public function createTempInvoiceCommodity(
        TblTempInvoice $tblTempInvoice,
        TblCommodity $tblCommodity
    ) {

        return (new Data($this->getBinding()))->createTempInvoiceCommodity($tblTempInvoice, $tblCommodity);
    }
}
