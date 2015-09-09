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
     * @param TblDebtor $tblDebtor
     *
     * @return bool
     */
    public function checkInvoiceFromDebtorIsPaidByDebtor(TblDebtor $tblDebtor)
    {

        return (new Data($this->Binding))->checkInvoiceFromDebtorIsPaidByDebtor($tblDebtor);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return string
     */
    public function sumPriceItemAllStringByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->Binding))->sumPriceItemAllStringByInvoice($tblInvoice);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return float
     */
    public function sumPriceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->Binding))->sumPriceItemAllByInvoice($tblInvoice);
    }

    /**
     * @param $IsVoid
     *
     * @return bool|TblInvoice[]
     */
    public function entityInvoiceAllByIsVoidState($IsVoid)
    {

        return (new Data($this->Binding))->entityInvoiceAllByIsVoidState($IsVoid);
    }

    /**
     * @param $Id
     *
     * @return bool|TblInvoice
     */
    public function entityInvoiceById($Id)
    {

        return (new Data($this->Binding))->entityInvoiceById($Id);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|TblInvoiceItem[]
     */
    public function entityInvoiceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->Binding))->entityInvoiceItemAllByInvoice($tblInvoice);
    }

    /**
     * @param $Id
     *
     * @return bool|TblInvoiceItem
     */
    public function entityInvoiceItemById($Id)
    {

        return (new Data($this->Binding))->entityInvoiceItemById($Id);
    }

    /**
     * @param $IsPaid
     *
     * @return bool|TblInvoice[]
     */
    public function entityInvoiceAllByIsPaidState($IsPaid)
    {

        return (new Data($this->Binding))->entityInvoiceAllByIsPaidState($IsPaid);
    }

    /**
     * @param TblBasket $tblBasket
     *
     * @return bool
     */
    public function executeDestroyTempInvoice(TblBasket $tblBasket)
    {

        return (new Data($this->Binding))->actionDestroyTempInvoice($tblBasket);
    }

    /**
     * @param TblBasket $tblBasket
     * @param           $Date
     *
     * @return bool
     */
    public function executeCreateInvoiceListFromBasket(TblBasket $tblBasket, $Date)
    {

        return (new Data($this->Binding))->actionCreateInvoiceListFromBasket($tblBasket, $Date);
    }

    /**
     * @param $Id
     *
     * @return bool|TblTempInvoice
     */
    public function entityTempInvoiceById($Id)
    {

        return (new Data($this->Binding))->entityTempInvoiceById($Id);
    }

    /**
     * @param $isConfirmed
     *
     * @return array|bool
     */
    public function entityInvoiceAllByIsConfirmedState($isConfirmed)
    {

        $invoiceAllByConfirmed = array();
        $invoiceAllByNotConfirmed = array();
        $tblInvoiceAll = $this->entityInvoiceAll();

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
    public function entityInvoiceAll()
    {

        return (new Data($this->Binding))->entityInvoiceAll();
    }

    /**
     * @param TblInvoice $tblInvoice
     * @param            $Data
     *
     * @return string
     */
    public function executeConfirmInvoice(TblInvoice $tblInvoice, $Data)
    {

        if (Balance::useService()->actionCreateBalance(
            Banking::useService()->entityDebtorByDebtorNumber($tblInvoice->getDebtorNumber()),
            $tblInvoice,
            null
        )
        ) {
            return new Success('Die Rechnung wurde erfolgreich bestätigt und freigegeben')
            .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed', 0);
        } else {
            return new Warning('Die Rechnung wurde konnte nicht bestätigt und freigegeben werden')
            .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 2, array('Id' => $tblInvoice->getId()));
        }
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return string
     */
    public function executeCancelInvoice(
        TblInvoice $tblInvoice
    ) {

        if (!$tblInvoice->getIsConfirmed()) {
            if ((new Data($this->Binding))->actionCancelInvoice($tblInvoice)) {
                return new Success('Die Rechnung wurde erfolgreich storniert')
                .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed', 0);
            } else {
                return new Warning('Die Rechnung konnte nicht storniert werden')
                .new Redirect('/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', 2,
                    array('Id' => $tblInvoice->getId()));
            }
        } else {
            //TODO cancel confirmed invoice
            if ((new Data($this->Binding))->actionCancelInvoice($tblInvoice)) {
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
    public function executeChangeInvoiceAddress(
        TblInvoice $tblInvoice,
        TblAddress $tblAddress
    ) {

        if ((new Data($this->Binding))->actionChangeInvoiceAddress($tblInvoice, $tblAddress)) {
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
    public function executeChangeInvoicePaymentType(TblInvoice $tblInvoice, TblPaymentType $tblPaymentType)
    {

        if ((new Data($this->Binding))->actionChangeInvoicePaymentType($tblInvoice, $tblPaymentType)) {
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
    public function executePayInvoice(
        TblInvoice $tblInvoice
    ) {

        if ((new Data($this->Binding))->actionPayInvoice($tblInvoice)) {
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
    public function executeEditInvoiceItem(IFormInterface &$Stage = null, TblInvoiceItem $tblInvoiceItem, $InvoiceItem)
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
            if ((new Data($this->Binding))->actionEditInvoiceItem(
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
    public function executeRemoveInvoiceItem(
        TblInvoiceItem $tblInvoiceItem
    ) {

        if ((new Data($this->Binding))->actionRemoveInvoiceItem($tblInvoiceItem)) {
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
    public function executeCreateTempInvoice(
        TblBasket $tblBasket,
        TblPerson $tblPerson,
        TblDebtor $tblDebtor
    ) {

        return (new Data($this->Binding))->actionCreateTempInvoice($tblBasket, $tblPerson, $tblDebtor);
    }

    /**
     * @param TblTempInvoice $tblTempInvoice
     * @param TblCommodity   $tblCommodity
     *
     * @return null|Service\Entity\TblTempInvoiceCommodity
     */
    public function executeCreateTempInvoiceCommodity(
        TblTempInvoice $tblTempInvoice,
        TblCommodity $tblCommodity
    ) {

        return (new Data($this->Binding))->actionCreateTempInvoiceCommodity($tblTempInvoice, $tblCommodity);
    }
}
