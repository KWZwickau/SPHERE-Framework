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
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblOrder;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblOrderItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblTempInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Setup;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Binding\AbstractService;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
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
     * @param TblOrder $tblOrder
     *
     * @return string
     */
    public function sumPriceItemAllStringByOrder(TblOrder $tblOrder)
    {

        return (new Data($this->getBinding()))->sumPriceItemAllStringByOrder($tblOrder);
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
     * @param $Id
     *
     * @return bool|TblOrder
     */
    public function getOrderById($Id)
    {

        return (new Data($this->getBinding()))->getOrderById($Id);
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
     * @param TblOrder $tblOrder
     *
     * @return bool|TblOrderItem[]
     */
    public function getOrderItemAllByOrder(TblOrder $tblOrder)
    {

        return (new Data($this->getBinding()))->getOrderItemAllByOrder($tblOrder);
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
     * @param $Id
     *
     * @return bool|TblOrderItem
     */
    public function getOrderItemById($Id)
    {

        return (new Data($this->getBinding()))->getOrderItemById($Id);
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
    public function createOrderListFromBasket(TblBasket $tblBasket, $Date)
    {

        return (new Data($this->getBinding()))->createOrderListFromBasket($tblBasket, $Date);
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
                if ($tblInvoice->isConfirmed()) {
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
     * @param TblOrderItem $tblOrderItem
     * @param TblInvoice   $tblInvoice
     *
     * @return TblInvoiceItem
     */
    public function createInvoiceItem(TblOrderItem $tblOrderItem, TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->createInvoiceItem($tblOrderItem, $tblInvoice);
    }

    /**
     * @return mixed
     */
    public function getOrderAll()
    {

        return (new Data($this->getBinding()))->getOrderAll();
    }

    /**
     * @param TblOrder $tblOrder
     * @param          $Account
     *
     * @return string
     */
    public function createInvoice(TblOrder $tblOrder, $Account = null)
    {

        $tblOrderItemList = Invoice::useService()->getOrderItemAllByOrder($tblOrder);
        if (!empty( $tblOrderItemList )) {
            if ($tblInvoice = (new Data($this->getBinding()))->createInvoice($tblOrder)) {

                Debugger::screenDump($tblInvoice);
                foreach ($tblOrderItemList as $tblOrderItem) {
                    Invoice::useService()->createInvoiceItem($tblOrderItem, $tblInvoice);
                }
                if ($Account !== false || $Account !== null) {
                    $tblAccount = Banking::useService()->getAccountById($Account);

                    $tblOrderItemList = Invoice::useService()->getOrderItemAllByOrder($tblOrder);
                    if (!empty( $tblOrderItemList )) {
                        if ($tblOrder->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift') {
                            if ($tblAccount) {
                                $tblCommodity = Commodity::useService()->getCommodityByName($tblOrderItemList[0]->getCommodityName());
                                if ($tblCommodity) {
                                    $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblOrder->getDebtorNumber());
                                    if ($tblDebtor) {
//                                    if (Banking::useService()->getReferenceByDebtorAndCommodity($tblDebtor,
//                                        $tblCommodity)) {
                                        if (Banking::useService()->getReferenceByAccountAndCommodity($tblAccount, $tblCommodity)) {
                                            $tblReference = Banking::useService()->getReferenceByAccountAndCommodity($tblAccount, $tblCommodity);
                                            $Reference = $tblReference->getReference();
                                        }
                                    }
                                }
                            }
                        }
                        if (!isset( $Reference )) {
                            $Reference = null;
                        }

                        if (Balance::useService()->createBalance(
                            Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber()),
                            $tblInvoice,
                            null,
                            $tblAccount->getBankName(),
                            $tblAccount->getIBAN(),
                            $tblAccount->getBIC(),
                            $tblAccount->getOwner(),
                            $tblAccount->getCashSign(),
                            $Reference
                        )
                        ) {
                            if (Invoice::useService()->destroyOrder($tblOrder)) {
                                return new Success('Die Rechnung wurde erstellt')
                                .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_SUCCESS);
                            } else {
                                return new Success('Die Rechnung erstellt, Auftrag aber nicht gelöscht', Redirect::TIMEOUT_ERROR)
                                .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_SUCCESS);
                            }
                        } else {
                            return new Warning('Die Rechnung konnte nicht erstellt werden')
                            .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
                                array('Id' => $tblOrder->getId()));
                        }
                    }
                    return new Warning('Die Rechnung kann ohne Artikel nicht erstellt werden.')
                    .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblOrder->getId()));


                }
//                elseif($Account === null){
//
//                    $tblBalance = Balance::useService()->getBalanceByInvoice($tblInvoice);
//                    if($tblBalance)
//                    {
//                        if(Balance::useService()->copyBalance($tblBalance, $tblInvoice))
//                        {
//                            return new Success('Die Rechnung wurde Stoniert')
//                            .new Redirect('/Billing/Bookkeeping/Invoice', Redirect::TIMEOUT_SUCCESS);
//                        }
//                    }
//
//                }
                else {
                    if (Balance::useService()->createBalance(
                        Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber()),
                        $tblInvoice
                    )
                    ) {
                        return new Success('Die Rechnung wurde erfolgreich bestätigt und freigegeben')
                        .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_SUCCESS);
                    } else {
                        return new Warning('Die Rechnung konnte nicht bestätigt und freigegeben werden')
                        .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
                            array('Id' => $tblOrder->getId()));
                    }
                }
            }
            return new Warning('Die Rechnung konnte nicht erstellt werden')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblOrder->getId()));
        }
        return new Warning('Die Rechnung enthält keine Artikel')
        .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
            array('Id' => $tblOrder->getId()));
    }

//    /**
//     * @param TblInvoice $tblInvoice
//     *
//     * @return string
//     */
//    public function createStorno(TblInvoice $tblInvoice)
//    {
//
//        if ($tblInvoice = (new Data($this->getBinding()))->createInvoice($tblInvoice)) {
//
//        }
//        return new Warning('Storno konnte nicht erstellt werden')
//        .new Redirect('/Billing/Bookkeeping/Invoice', Redirect::TIMEOUT_ERROR);
//    }

    /**
     * @param TblInvoice $tblInvoice
     * @param            $Account
     *
     * @return string
     */
    public function confirmInvoice(TblInvoice $tblInvoice, $Account)
    {

        If ($Account !== false) {
            $tblAccount = Banking::useService()->getAccountById($Account);

            $tblInvoiceItemList = Invoice::useService()->getInvoiceItemAllByInvoice($tblInvoice);
            if (!empty( $tblInvoiceItemList )) {
                if ($tblInvoice->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift') {
                    if ($tblAccount) {
                        $tblCommodity = Commodity::useService()->getCommodityByName($tblInvoiceItemList[0]->getCommodityName());
                        if ($tblCommodity) {
                            $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber());
                            if ($tblDebtor) {
//                                    if (Banking::useService()->getReferenceByDebtorAndCommodity($tblDebtor,
//                                        $tblCommodity)) {
                                if (Banking::useService()->getReferenceByAccountAndCommodity($tblAccount, $tblCommodity)) {
                                    $tblReference = Banking::useService()->getReferenceByAccountAndCommodity($tblAccount, $tblCommodity);
                                    $Reference = $tblReference->getReference();
                                }
                            }
                        }
                    }
                }
                if (!isset( $Reference )) {
                    $Reference = null;
                }

                if (Balance::useService()->createBalance(
                    Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber()),
                    $tblInvoice,
                    null,
                    $tblAccount->getBankName(),
                    $tblAccount->getIBAN(),
                    $tblAccount->getBIC(),
                    $tblAccount->getOwner(),
                    $tblAccount->getCashSign(),
                    $Reference
                )
                ) {
                    return new Success('Die Rechnung wurde erfolgreich bestätigt und freigegeben')
                    .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_SUCCESS);
                } else {
                    return new Warning('Die Rechnung konnte nicht bestätigt und freigegeben werden')
                    .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblInvoice->getId()));
                }
            }
            return new Warning('Die Rechnung konnte nicht bestätigt und freigegeben werden. Fehlende Artikel!')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblInvoice->getId()));


        } else {
            if (Balance::useService()->createBalance(
                Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber()),
                $tblInvoice
            )
            ) {
                return new Success('Die Rechnung wurde erfolgreich bestätigt und freigegeben')
                .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Warning('Die Rechnung konnte nicht bestätigt und freigegeben werden')
                .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblInvoice->getId()));
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

        if (!$tblInvoice->isConfirmed()) {
            if ((new Data($this->getBinding()))->cancelInvoice($tblInvoice)) {
                return new Success('Die Rechnung wurde erfolgreich storniert')
                .new Redirect('/Billing/Bookkeeping/Invoice', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Warning('Die Rechnung konnte nicht storniert werden')
                .new Redirect('/Billing/Bookkeeping/Invoice/Show', Redirect::TIMEOUT_ERROR,
                    array('Id' => $tblInvoice->getId()));
            }
        } else {
            //TODO cancel confirmed invoice
            if ((new Data($this->getBinding()))->cancelInvoice($tblInvoice)) {
                return new Success('Die Rechnung wurde erfolgreich storniert')
                .new Redirect('/Billing/Bookkeeping/Invoice', Redirect::TIMEOUT_SUCCESS);
            } else {
                return new Warning('Die Rechnung konnte nicht storniert werden')
                .new Redirect('/Billing/Bookkeeping/Invoice', Redirect::TIMEOUT_ERROR);
            }
        }
    }

    /**
     * @param TblOrder $tblOrder
     *
     * @return string
     */
    public function destroyOrder(TblOrder $tblOrder)
    {

        $tblOrderItemList = Invoice::useService()->getOrderItemAllByOrder($tblOrder);
        if ($tblOrderItemList) {
            foreach ($tblOrderItemList as $tblOrderItem) {
                $this->removeOrderItem($tblOrderItem);
            }
        }

        if ((new Data($this->getBinding()))->destroyOrder($tblOrder)) {
            return new Success('Der Auftrag wurde erfolgreich gelöscht')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Warning('Der Auftrag konnte nicht gelöscht werden')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param TblOrder   $tblOrder
     * @param TblAddress $tblAddress
     *
     * @return string
     */
    public function changeOrderAddress(
        TblOrder $tblOrder,
        TblAddress $tblAddress
    ) {

        if ((new Data($this->getBinding()))->updateOrderAddress($tblOrder, $tblAddress)) {
            return new Success('Die Rechnungsadresse wurde erfolgreich geändert')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblOrder->getId()));
        } else {
            return new Warning('Die Rechnungsadresse konnte nicht geändert werden')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR, array('Id' => $tblOrder->getId()));
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
            .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblInvoice->getId()));
        } else {
            return new Warning('Die Zahlungsart konnte nicht geändert werden')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR, array('Id' => $tblInvoice->getId()));
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
            .new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_SUCCESS);
        } else {
            return new Warning('Die Rechnung konnte nicht bezahlt werden')
            .new Redirect('/Billing/Bookkeeping/Balance', Redirect::TIMEOUT_ERROR);
        }
    }

    /**
     * @param IFormInterface|null $Stage
     * @param TblOrderItem        $tblInvoiceItem
     * @param                     $OrderItem
     *
     * @return IFormInterface|string
     */
    public function changeOrderItem(IFormInterface &$Stage = null, TblOrderItem $tblOrderItem, $OrderItem)
    {

        /**
         * Skip to Frontend
         */
        if (null === $OrderItem
        ) {
            return $Stage;
        }

        $Error = false;

        if (isset( $OrderItem['Price'] ) && empty( $OrderItem['Price'] )) {
            $Stage->setError('InvoiceItem[Price]', 'Bitte geben Sie einen Preis an');
            $Error = true;
        }
        if (isset( $OrderItem['Quantity'] ) && empty( $OrderItem['Quantity'] )) {
            $Stage->setError('InvoiceItem[Quantity]', 'Bitte geben Sie eine Menge an');
            $Error = true;
        }

        if (!$Error) {
            if ((new Data($this->getBinding()))->updateOrderItem(
                $tblOrderItem,
                $OrderItem['Price'],
                $OrderItem['Quantity']
            )
            ) {
                $Stage .= new Success('Änderungen gespeichert, die Daten werden neu geladen...')
                    .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_SUCCESS,
                        array('Id' => $tblOrderItem->getTblOrder()->getId()));
            } else {
                $Stage .= new Danger('Änderungen konnten nicht gespeichert werden')
                    .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
                        array('Id' => $tblOrderItem->getTblOrder()->getId()));
            };
        }
        return $Stage;
    }

    /**
     * @param TblOrderItem $tblOrderItem
     *
     * @return string
     */
    public function removeOrderItem(
        TblOrderItem $tblOrderItem
    ) {

        if ((new Data($this->getBinding()))->destroyOrderItem($tblOrderItem)) {
            return new Success('Der Artikel '.$tblOrderItem->getItemName().' wurde erfolgreich entfernt')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_SUCCESS,
                array('Id' => $tblOrderItem->getTblOrder()->getId()));
        } else {
            return new Warning('Der Artikel '.$tblOrderItem->getItemName().' konnte nicht entfernt werden')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order/Edit', Redirect::TIMEOUT_ERROR,
                array('Id' => $tblOrderItem->getTblOrder()->getId()));
        }
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|string
     */
    public function removeInvoice(
        TblInvoice $tblInvoice
    ) {

        return (new Data($this->getBinding()))->destroyInvoice($tblInvoice);
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
