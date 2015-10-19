<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity\TblPayment;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendBalance()
    {

        $Stage = new Stage();
        $Stage->setTitle('Posten');
        $Stage->setDescription('Offen');

        $invoiceAllByIsConfirmedState = Invoice::useService()->getInvoiceAllByIsConfirmedState(true);
        $invoiceAllByIsVoidState = Invoice::useService()->getInvoiceAllByIsVoidState(true);
        $invoiceAllByIsPaidState = Invoice::useService()->getInvoiceAllByIsPaidState(true);
        $invoiceHasFullPaymentAll = Balance::useService()->getInvoiceHasFullPaymentAll();

        if ($invoiceAllByIsConfirmedState && $invoiceAllByIsVoidState) {
            $invoiceAllByIsConfirmedState = array_udiff($invoiceAllByIsConfirmedState, $invoiceAllByIsVoidState,
                function (TblInvoice $invoiceA, TblInvoice $invoiceB) {

                    return $invoiceA->getId() - $invoiceB->getId();
                });
        }
        if ($invoiceAllByIsConfirmedState && $invoiceAllByIsPaidState) {
            $invoiceAllByIsConfirmedState = array_udiff($invoiceAllByIsConfirmedState, $invoiceAllByIsPaidState,
                function (TblInvoice $invoiceA, TblInvoice $invoiceB) {

                    return $invoiceA->getId() - $invoiceB->getId();
                });
        }
        if ($invoiceAllByIsConfirmedState && $invoiceHasFullPaymentAll) {
            $invoiceAllByIsConfirmedState = array_udiff($invoiceAllByIsConfirmedState, $invoiceHasFullPaymentAll,
                function (TblInvoice $invoiceA, TblInvoice $invoiceB) {

                    return $invoiceA->getId() - $invoiceB->getId();
                });
        }
        if (!empty( $invoiceAllByIsConfirmedState )) {
            /** @var TblInvoice $invoiceByIsConfirmedState */
            foreach ($invoiceAllByIsConfirmedState as $invoiceByIsConfirmedState) {
                $tblBalance = Balance::useService()->getBalanceByInvoice($invoiceByIsConfirmedState);
                $AdditionInvoice = Invoice::useService()->sumPriceItemAllStringByInvoice($invoiceByIsConfirmedState);
                $AdditionPayment = Balance::useService()->sumPriceItemStringByBalance($tblBalance);

                $invoiceByIsConfirmedState->FullName = $invoiceByIsConfirmedState->getDebtorFullName();
                $invoiceByIsConfirmedState->PaidPayment = $AdditionPayment;
                $invoiceByIsConfirmedState->PaidInvoice = $AdditionInvoice;
                $invoiceByIsConfirmedState->Option = new Primary('Bezahlt', '/Billing/Bookkeeping/Invoice/Pay',
                    new Ok(), array(
                        'Id' => $invoiceByIsConfirmedState->getId()
                    ));
            }
        }

        $Stage->setContent(
            new TableData($invoiceAllByIsConfirmedState, null,
                array(
                    'Number'       => 'Nummer',
                    'InvoiceDate'  => 'Rechnungsdatum',
                    'PaymentDate'  => 'Zahlungsdatum',
                    'FullName'     => 'Debitor',
                    'DebtorNumber' => 'Debitorennummer',
                    'PaidPayment'  => 'Bezahlt',
                    'PaidInvoice'  => 'Gesamt',
                    'Option'       => 'Option'
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendPayment()
    {

        $Stage = new Stage();
        $Stage->setTitle('Zahlungen');
        $Stage->setDescription('Importierte');

        $paymentList = Balance::useService()->getPaymentAll();
        if ($paymentList) {
            array_walk($paymentList, function (TblPayment &$tblPayment) {

                $tblInvoice = $tblPayment->getTblBalance()->getServiceBillingInvoice();
                if ($tblInvoice) {
                    $tblPayment->InvoiceNumber = $tblInvoice->getNumber();
                    $tblPayment->InvoiceDate = $tblInvoice->getInvoiceDate();
                    $tblPayment->DebtorFullName = $tblInvoice->getDebtorFullName();
                    $tblPayment->DebtorNumber = $tblInvoice->getDebtorNumber();
                    $tblPayment->ValueString = $tblPayment->getValueString();
                }
            });
        }

        $Stage->setContent(
            new TableData($paymentList, null,
                array(
                    'InvoiceNumber'  => 'Rechnungs-Nr.',
                    'InvoiceDate'    => 'Rechnungsdatum',
                    'Date'           => 'Zahlungseingangsdatum',
                    'DebtorFullName' => 'Debitor',
                    'DebtorNumber'   => 'Debitorennummer',
                    'ValueString'    => 'Betrag'
                )
            )
        );

        return $Stage;
    }
}
