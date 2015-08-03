<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Common\Frontend\Form\Repository\Aspect;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\MoneyEuro;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Address;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendInvoiceStatus()
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnungen' );
        $Stage->setDescription( 'Übersicht' );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendInvoiceList()
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnungen' );
        $Stage->setDescription( 'Übersicht' );
        $Stage->setMessage( 'Zeigt alle vorhandenen Rechnungen an' );
        $Stage->addButton( new Primary( 'Freigeben', '/Billing/Bookkeeping/Invoice/IsNotConfirmed', new Ok() ) );

        $tblInvoiceAll = Invoice::useService()->entityInvoiceAll();

        if (!empty( $tblInvoiceAll )) {
            array_walk( $tblInvoiceAll, function ( TblInvoice &$tblInvoice ) {

                $paymentType = $tblInvoice->getServiceBillingBankingPaymentType();
                if ($paymentType) {
                    $tblInvoice->PaymentType = $paymentType->getName();
                } else {
                    $tblInvoice->PaymentType = "";
                }

                $tblInvoice->Person = $tblInvoice->getServiceManagementPerson()->getFullName();
                $tblInvoice->Debtor = $tblInvoice->getDebtorFullName();
                $tblInvoice->TotalPrice = Invoice::useService()->sumPriceItemAllStringByInvoice( $tblInvoice );
                $tblInvoice->Option = ( new Primary( 'Anzeigen', '/Billing/Bookkeeping/Invoice/Show',
                    new EyeOpen(), array( 'Id' => $tblInvoice->getId() ) ) )->__toString();;
                if ($tblInvoice->getIsPaid()) {
                    $tblInvoice->IsPaidString = "Bezahlt (manuell)";
                } else {
                    if (Balance::useService()->entityBalanceByInvoice( $tblInvoice )
                        && ( Balance::useService()->sumPriceItemByBalance( Balance::useService()->entityBalanceByInvoice( $tblInvoice ) )
                            >= Invoice::useService()->sumPriceItemAllByInvoice( $tblInvoice ) )
                    ) {
                        $tblInvoice->IsPaidString = "Bezahlt";
                    } else {
                        $tblInvoice->IsPaidString = "";
                    }
                }
                if ($tblInvoice->getIsVoid()) {
                    $tblInvoice->IsVoidString = "Storniert";
                } else {
                    $tblInvoice->IsVoidString = "";
//                    $tblInvoice->Option .= (new Danger( 'Stornieren', '/Billing/Bookkeeping/Invoice/Cancel',
//                        new Remove(), array('Id' => $tblInvoice->getId())))->__toString();
                }
                if ($tblInvoice->getIsConfirmed()) {
                    $tblInvoice->IsConfirmedString = "Bestätigt";
                } else {
                    $tblInvoice->IsConfirmedString = "";
                }
            } );
        }

        $Stage->setContent(
            new TableData( $tblInvoiceAll, null,
                array(
                    'Number'            => 'Nummer',
                    'InvoiceDate'       => 'Rechnungsdatum',
                    'BasketName'        => 'Warenkorb',
                    'Person'            => 'Person',
                    'Debtor'            => 'Debitor',
                    'DebtorNumber'      => 'Debitoren-Nr',
                    'PaymentType'       => 'Zahlungsart',
                    'TotalPrice'        => 'Gesamtpreis',
                    'IsConfirmedString' => 'Bestätigt',
                    'IsPaidString'      => 'Bezahlt',
                    'IsVoidString'      => 'Storniert',
                    'Option'            => 'Option'
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendInvoiceIsNotConfirmedList()
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnungen' );
        $Stage->setDescription( 'Freigeben' );
        $Stage->setMessage( 'Zeigt alle noch nicht freigegebenen Rechnungen an' );

        $tblInvoiceAllByIsConfirmedState = Invoice::useService()->entityInvoiceAllByIsConfirmedState( false );
        $tblInvoiceAllByIsVoid = Invoice::useService()->entityInvoiceAllByIsVoidState( true );

        if ($tblInvoiceAllByIsConfirmedState && $tblInvoiceAllByIsVoid) {
            $tblInvoiceAllByIsConfirmedState = array_udiff( $tblInvoiceAllByIsConfirmedState, $tblInvoiceAllByIsVoid,
                function ( TblInvoice $invoiceA, TblInvoice $invoiceB ) {

                    return $invoiceA->getId() - $invoiceB->getId();
                } );
        }

        if (!empty( $tblInvoiceAllByIsConfirmedState )) {
            array_walk( $tblInvoiceAllByIsConfirmedState, function ( TblInvoice &$tblInvoice ) {

                $paymentType = $tblInvoice->getServiceBillingBankingPaymentType();
                if ($paymentType) {
                    $tblInvoice->PaymentType = $paymentType->getName();
                } else {
                    $tblInvoice->PaymentType = "";
                }

                if ($tblInvoice->getIsPaymentDateModified()) {
                    $tblInvoice->InvoiceDateString = new Warning( $tblInvoice->getInvoiceDate() );
                    $tblInvoice->PaymentDateString = new Warning( $tblInvoice->getPaymentDate() );
                } else {
                    $tblInvoice->InvoiceDateString = $tblInvoice->getInvoiceDate();
                    $tblInvoice->PaymentDateString = $tblInvoice->getPaymentDate();
                }

                $tblInvoice->Person = $tblInvoice->getServiceManagementPerson()->getFullName();
                $tblInvoice->Debtor = $tblInvoice->getDebtorFullName();
                $tblInvoice->TotalPrice = Invoice::useService()->sumPriceItemAllStringByInvoice( $tblInvoice );
                $tblInvoice->Option =
                    ( new Primary( 'Bearbeiten und Freigeben', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit',
                        new Edit(), array(
                            'Id' => $tblInvoice->getId()
                        ) ) )->__toString();
            } );
        }

        $Stage->setContent(
            new TableData( $tblInvoiceAllByIsConfirmedState, null,
                array(
                    'Number'            => 'Nummer',
                    'InvoiceDateString' => 'Rechnungsdatum',
                    'PaymentDateString' => 'Zahlungsdatum',
                    'BasketName'        => 'Warenkorb',
                    'Person'            => 'Person',
                    'Debtor'            => 'Debitor',
                    'DebtorNumber'      => 'Debitoren-Nr',
                    'PaymentType'       => 'Zahlungsart',
                    'TotalPrice'        => 'Gesamtpreis',
                    'Option'            => 'Option'
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Data
     *
     * @return Stage
     */
    public function frontendInvoiceEdit( $Id, $Data )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Bearbeiten' );
        $Stage->setMessage(
            'Hier können Sie die Rechnung bearbeiten und freigeben. <br>
            <b>Hinweis:</b> Freigegebene Rechnung sind nicht mehr bearbeitbar.'
        );
        $Stage->addButton( new Primary( 'Geprüft und Freigeben', '/Billing/Bookkeeping/Invoice/Confirm',
            new Ok(), array(
                'Id' => $Id
            )
        ) );
        $Stage->addButton( new Danger( 'Stornieren', '/Billing/Bookkeeping/Invoice/Cancel',
            new Remove(), array(
                'Id' => $Id
            )
        ) );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Bookkeeping/Invoice/IsNotConfirmed', new ChevronLeft() ) );


        $tblInvoice = Invoice::useService()->entityInvoiceById( $Id );
        if ($tblInvoice->getIsConfirmed()) {
            $Stage->setContent( new Warning( 'Die Rechnung wurde bereits bestätigt und freigegeben und kann nicht mehr bearbeitet werden' )
                .new Redirect( '/Billing/Bookkeeping/Invoice', 2 ) );
        } else {
            $tblInvoiceItemAll = Invoice::useService()->entityInvoiceItemAllByInvoice( $tblInvoice );
            if (!empty( $tblInvoiceItemAll )) {
                array_walk( $tblInvoiceItemAll,
                    function ( TblInvoiceItem &$tblInvoiceItem, $index, TblInvoice $tblInvoice ) {

                        if ($tblInvoice->getServiceBillingBankingPaymentType()->getId() == 1) //SEPA-Lastschrift
                        {
                            $tblCommodity = Commodity::useService()->entityCommodityByName( $tblInvoiceItem->getCommodityName() );
                            if ($tblCommodity) {

                                $tblDebtor = Banking::useService()->entityDebtorByDebtorNumber( $tblInvoice->getDebtorNumber() );
                                if ($tblDebtor) {
                                    if (Banking::useService()->entityReferenceByDebtorAndCommodity( $tblDebtor,
                                        $tblCommodity )
                                    ) {
                                        $tblInvoiceItem->Status = new Success(
                                            'Mandatsreferenz', new Ok()
                                        );
                                    } else {
                                        $tblInvoiceItem->Status = new Warning(
                                            'keine Mandatsreferenz', new Disable()
                                        );
                                    }
                                } else {
                                    $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Message\Repository\Danger(
                                        'Debitor nicht gefunden', new Disable()
                                    );
                                }
                            } else {
                                $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Message\Repository\Danger(
                                    'Leistung nicht gefunden', new Disable()
                                );
                            }
                        } else {
                            $tblInvoiceItem->Status = "";
                        }

                        $tblInvoiceItem->TotalPriceString = $tblInvoiceItem->getTotalPriceString();
                        $tblInvoiceItem->QuantityString = str_replace( '.', ',', $tblInvoiceItem->getItemQuantity() );
                        $tblInvoiceItem->PriceString = $tblInvoiceItem->getPriceString();
                        $tblInvoiceItem->Option =
                            ( new Primary( 'Bearbeiten', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Item/Edit',
                                new Edit(), array(
                                    'Id' => $tblInvoiceItem->getId()
                                ) ) )->__toString().
                            ( new Danger( 'Entfernen',
                                '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Item/Remove',
                                new Minus(), array(
                                    'Id' => $tblInvoiceItem->getId()
                                ) ) )->__toString();
                    }, $tblInvoice );
            }

            $Stage->setContent(
                new Layout( array(
                    new LayoutGroup( array(
                        new LayoutRow( array(
                            new LayoutColumn(
                                new Panel( 'Rechnungsnummer', $tblInvoice->getNumber(), Panel::PANEL_TYPE_PRIMARY ), 3
                            ),
                            new LayoutColumn(
                                new Panel( 'Warenkorb', $tblInvoice->getBasketName(), Panel::PANEL_TYPE_DEFAULT ), 3
                            ),
                            new LayoutColumn(
                                new Panel( 'Rechnungsdatum', $tblInvoice->getInvoiceDate(),
                                    $tblInvoice->getIsPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT ),
                                3
                            ),
                            new LayoutColumn(
                                new Panel( 'Zahlungsdatum', $tblInvoice->getPaymentDate(),
                                    $tblInvoice->getIsPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT ),
                                3
                            ),
                        ) ),
                        new LayoutRow(
                            new LayoutColumn(
                                new Aspect( 'Empfänger' )
                            )
                        ),
                        new LayoutRow( array(
                            new LayoutColumn(
                                new Panel( 'Debitor', $tblInvoice->getDebtorFullName() ), 3
                            ),
                            new LayoutColumn(
                                new Panel( 'Debitorennummer', $tblInvoice->getDebtorNumber() ), 3
                            ),
                            new LayoutColumn(
                                new Panel( 'Person', $tblInvoice->getServiceManagementPerson()->getFullName() ), 3
                            )
                        ) ),
                        new LayoutRow( array(
                            new LayoutColumn(
                                ( $tblInvoice->getServiceManagementAddress()
                                    ? new Panel(
                                        new MapMarker().' Rechnungsadresse',
                                        new Address( $tblInvoice->getServiceManagementAddress() ),
                                        Panel::PANEL_TYPE_DEFAULT,
                                        ( ( $tblDebtor = Banking::useService()->entityDebtorByDebtorNumber(
                                            $tblInvoice->getDebtorNumber() ) )
                                        && count( Management::servicePerson()->entityAddressAllByPerson(
                                            $tblDebtor->getServiceManagementPerson() ) ) > 1
                                            ? new Primary( 'Bearbeiten',
                                                '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Address/Select',
                                                new Edit(),
                                                array(
                                                    'Id'        => $tblInvoice->getId(),
                                                    'AddressId' => $tblInvoice->getServiceManagementAddress()->getId()
                                                )
                                            )
                                            : null
                                        )
                                    )
                                    : new Warning(
                                        'Keine Rechnungsadresse verfügbar', new Disable()
                                    )
                                ), 3
                            ),
                            new LayoutColumn(
                                new Panel(
                                    'Zahlungsart',
                                    $tblInvoice->getServiceBillingBankingPaymentType()->getName(),
                                    Panel::PANEL_TYPE_DEFAULT,
                                    new Primary( 'Bearbeiten',
                                        '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Payment/Type/Select',
                                        new Edit(),
                                        array(
                                            'Id' => $tblInvoice->getId()
                                        )
                                    )
                                ), 3
                            ),
                        ) ),
                        new LayoutRow(
                            new LayoutColumn(
                                new Aspect( 'Betrag' )
                            )
                        ),
                        new LayoutRow( array(
                            new LayoutColumn(
                                new Panel( 'Rechnungsbetrag',
                                    Invoice::useService()->sumPriceItemAllStringByInvoice( $tblInvoice ) ), 3
                            )
                        ) )
                    ), new Title( 'Kopf' ) ),
                    new LayoutGroup( array(
                        new LayoutRow( array(
                            new LayoutColumn( array(
                                    new TableData( $tblInvoiceItemAll, null,
                                        array(
                                            'CommodityName'    => 'Leistung',
                                            'ItemName'         => 'Artikel',
                                            'PriceString'      => 'Preis',
                                            'QuantityString'   => 'Menge',
                                            'TotalPriceString' => 'Gesamtpreis',
                                            'Status'           => 'Status',
                                            'Option'           => 'Option'
                                        )
                                    )
                                )
                            )
                        ) ),
                    ), new Title( 'Positionen' ) )
                ) )
            );
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoiceShow( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Anzeigen' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Bookkeeping/Invoice', new ChevronLeft() ) );

        $tblInvoice = Invoice::useService()->entityInvoiceById( $Id );

        if ($tblInvoice->getIsVoid()) {
            $Stage->setMessage( new \SPHERE\Common\Frontend\Message\Repository\Danger( "Diese Rechnung wurde storniert" ) );
        }

        $tblInvoiceItemAll = Invoice::useService()->entityInvoiceItemAllByInvoice( $tblInvoice );
        if (!empty( $tblInvoiceItemAll )) {
            array_walk( $tblInvoiceItemAll,
                function ( TblInvoiceItem &$tblInvoiceItem, $index, TblInvoice $tblInvoice ) {

                    if ($tblInvoice->getServiceBillingBankingPaymentType()->getId() == 1) //SEPA-Lastschrift
                    {
                        $tblCommodity = Commodity::useService()->entityCommodityByName( $tblInvoiceItem->getCommodityName() );
                        if ($tblCommodity) {

                            $tblDebtor = Banking::useService()->entityDebtorByDebtorNumber( $tblInvoice->getDebtorNumber() );
                            if ($tblDebtor) {
                                if (Banking::useService()->entityReferenceByDebtorAndCommodity( $tblDebtor,
                                    $tblCommodity )
                                ) {
                                    $tblInvoiceItem->Status = new Success(
                                        'Mandatsreferenz', new Ok()
                                    );
                                } else {
                                    $tblInvoiceItem->Status = new Warning(
                                        'keine Mandatsreferenz', new Disable()
                                    );
                                }
                            } else {
                                $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Message\Repository\Danger(
                                    'Debitor nicht gefunden', new Disable()
                                );
                            }
                        } else {
                            $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Message\Repository\Danger(
                                'Leistung nicht gefunden', new Disable()
                            );
                        }
                    } else {
                        $tblInvoiceItem->Status = "";
                    }

                    $tblInvoiceItem->TotalPriceString = $tblInvoiceItem->getTotalPriceString();
                    $tblInvoiceItem->QuantityString = str_replace( '.', ',', $tblInvoiceItem->getItemQuantity() );
                    $tblInvoiceItem->PriceString = $tblInvoiceItem->getPriceString();
                }, $tblInvoice );
        }


        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn(
                            new Panel( 'Rechnungsnummer', $tblInvoice->getNumber(), Panel::PANEL_TYPE_PRIMARY ), 3
                        ),
                        new LayoutColumn(
                            new Panel( 'Warenkorb', $tblInvoice->getBasketName(), Panel::PANEL_TYPE_DEFAULT ), 3
                        ),
                        new LayoutColumn(
                            new Panel( 'Rechnungsdatum', $tblInvoice->getInvoiceDate(),
                                $tblInvoice->getIsPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT ),
                            3
                        ),
                        new LayoutColumn(
                            new Panel( 'Zahlungsdatum', $tblInvoice->getPaymentDate(),
                                $tblInvoice->getIsPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT ),
                            3
                        ),
                    ) ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Aspect( 'Empfänger' )
                        )
                    ),
                    new LayoutRow( array(
                        new LayoutColumn(
                            new Panel( 'Debitor', $tblInvoice->getDebtorFullName() ), 3
                        ),
                        new LayoutColumn(
                            new Panel( 'Debitorennummer', $tblInvoice->getDebtorNumber() ), 3
                        ),
                        new LayoutColumn(
                            new Panel( 'Person', $tblInvoice->getServiceManagementPerson()->getFullName() ), 3
                        )
                    ) ),
                    new LayoutRow( array(
                        new LayoutColumn(
                            ( $tblInvoice->getServiceManagementAddress()
                                ? new Panel(
                                    new MapMarker().' Rechnungsadresse',
                                    new Address( $tblInvoice->getServiceManagementAddress() ),
                                    Panel::PANEL_TYPE_DEFAULT
                                )
                                : new Warning(
                                    'Keine Rechnungsadresse verfügbar', new Disable()
                                )
                            ), 3
                        ),
                        new LayoutColumn(
                            new Panel(
                                'Zahlungsart',
                                $tblInvoice->getServiceBillingBankingPaymentType()->getName(),
                                Panel::PANEL_TYPE_DEFAULT
                            ), 3
                        ),
                    ) ),
                    new LayoutRow(
                        new LayoutColumn(
                            new Aspect( 'Betrag' )
                        )
                    ),
                    new LayoutRow( array(
                        new LayoutColumn(
                            new Panel( 'Rechnungsbetrag',
                                Invoice::useService()->sumPriceItemAllStringByInvoice( $tblInvoice ) ), 3
                        ),
                        new LayoutColumn(
                            $tblInvoice->getIsConfirmed() ?
                                ( $tblInvoice->getIsPaid()
                                    ? new Success( "Bezahlt" )
                                    : ( round( Balance::useService()->sumPriceItemByBalance( Balance::useService()->entityBalanceByInvoice( $tblInvoice ) ),
                                        2 )
                                    >= round( Invoice::useService()->sumPriceItemAllByInvoice( $tblInvoice ), 2 )
                                        ? new Panel( 'Bezahlbetrag', Balance::useService()->sumPriceItemStringByBalance(
                                            Balance::useService()->entityBalanceByInvoice( $tblInvoice ) ),
                                            Panel::PANEL_TYPE_SUCCESS )
                                        : new Panel( 'Bezahlbetrag', Balance::useService()->sumPriceItemStringByBalance(
                                            Balance::useService()->entityBalanceByInvoice( $tblInvoice ) ),
                                            Panel::PANEL_TYPE_DANGER ) ) )
                                : new \SPHERE\Common\Frontend\Text\Repository\Success( "" )
                            , 3 )
                    ) ),
                ), new Title( 'Kopf' ) ),
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn( array(
                                new TableData( $tblInvoiceItemAll, null,
                                    array(
                                        'CommodityName'    => 'Leistung',
                                        'ItemName'         => 'Artikel',
                                        'PriceString'      => 'Preis',
                                        'QuantityString'   => 'Menge',
                                        'TotalPriceString' => 'Gesamtpreis',
                                        'Status'           => 'Status'
                                    )
                                )
                            )
                        )
                    ) ),
                ), new Title( 'Positionen' ) )
            ) )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Data
     *
     * @return Stage
     */
    public function frontendInvoiceConfirm( $Id, $Data )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Freigeben' );

        $tblInvoice = Invoice::useService()->entityInvoiceById( $Id );
        $Stage->setContent( Invoice::useService()->executeConfirmInvoice( $tblInvoice, $Data ) );

        return $Stage;
    }


    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoiceCancel( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Stornieren' );

        $tblInvoice = Invoice::useService()->entityInvoiceById( $Id );
        $Stage->setContent( Invoice::useService()->executeCancelInvoice( $tblInvoice ) );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoiceAddressSelect( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Rechnungsadresse Auswählen' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', new ChevronLeft(),
            array( 'Id' => $Id )
        ) );

        $tblInvoice = Invoice::useService()->entityInvoiceById( $Id );
        $tblAddressAll = Management::servicePerson()->entityAddressAllByPerson(
            Banking::useService()->entityDebtorByDebtorNumber( $tblInvoice->getDebtorNumber() )->getServiceManagementPerson() );

        $layoutGroup = self::layoutAddress( $tblAddressAll, $tblInvoice->getServiceManagementAddress(), $tblInvoice );

        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn( array(
                            new Panel( 'Rechnungsnummer', $tblInvoice->getNumber(), Panel::PANEL_TYPE_SUCCESS )
                        ), 3 ),
                        new LayoutColumn( array(
                            new Panel( 'Empfänger', $tblInvoice->getDebtorFullName(), Panel::PANEL_TYPE_SUCCESS )
                        ), 3 )
                    ) )
                ) )
            ) )
            .$layoutGroup
        );

        return $Stage;
    }

    /**
     * @param $tblAddressList
     * @param TblAddress $invoiceAddress
     * @param TblInvoice $tblInvoice
     *
     * @return Layout
     */
    private static function layoutAddress( $tblAddressList, TblAddress $invoiceAddress, TblInvoice $tblInvoice )
    {

        if (!empty( $tblAddressList )) {
            /** @var TblAddress[] $tblAddressList */
            foreach ($tblAddressList as &$tblAddress) {
                if ($invoiceAddress != null && $invoiceAddress->getId() === $tblAddress->getId()) {
                    $AddressType = new MapMarker().' Rechnungsadresse';
                    $PanelType = Panel::PANEL_TYPE_SUCCESS;
                } else {
                    $AddressType = new MapMarker().' Adresse';
                    $PanelType = Panel::PANEL_TYPE_DEFAULT;
                }

                $tblAddress = new LayoutColumn(
                    new Panel(
                        $AddressType, new Address( $tblAddress ), $PanelType,
                        new Primary( 'Auswählen', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Address/Change',
                            new Ok(),
                            array(
                                'Id'        => $tblInvoice->getId(),
                                'AddressId' => $tblAddress->getId()
                            )
                        )
                    ), 3
                );
            }
        } else {
            $tblAddressList = array(
                new LayoutColumn(
                    new Warning( 'Keine Adressen hinterlegt', new \SPHERE\Common\Frontend\Icon\Repository\Warning() )
                )
            );
        }

        return new Layout(
            new LayoutGroup( new LayoutRow( $tblAddressList ), new Title( 'Adressen' ) )
        );
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoicePaymentTypeSelect( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Zahlungsart Auswählen' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', new ChevronLeft(),
            array( 'Id' => $Id )
        ) );

        $tblInvoice = Invoice::useService()->entityInvoiceById( $Id );
        $tblPaymentTypeList = Banking::useService()->entityPaymentTypeAll();

        if ($tblPaymentTypeList) {
            foreach ($tblPaymentTypeList as &$tblPaymentType) {
                $tblPaymentType = new LayoutColumn(
                    new Panel(
                        'Zahlungsart',
                        $tblPaymentType->getName(),
                        $tblPaymentType->getId() === $tblInvoice->getServiceBillingBankingPaymentType()->getId() ?
                            Panel::PANEL_TYPE_SUCCESS :
                            Panel::PANEL_TYPE_DEFAULT,
                        new Primary( 'Auswählen', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Payment/Type/Change',
                            new Ok(),
                            array(
                                'Id'            => $tblInvoice->getId(),
                                'PaymentTypeId' => $tblPaymentType->getId()
                            )
                        )
                    ), 3
                );
            }
        }
        $Stage->setContent(
            new Layout( array(
                new LayoutGroup( array(
                    new LayoutRow( array(
                        new LayoutColumn( array(
                            new Panel( 'Rechnungsnummer', $tblInvoice->getNumber(), Panel::PANEL_TYPE_SUCCESS )
                        ), 3 ),
                        new LayoutColumn( array(
                            new Panel( 'Empfänger', $tblInvoice->getDebtorFullName(), Panel::PANEL_TYPE_SUCCESS )
                        ), 3 )
                    ) )
                ) ),
                new LayoutGroup(
                    new LayoutRow(
                        $tblPaymentTypeList
                    ), new Title( 'Zahlungsarten' )
                )
            ) )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $AddressId
     *
     * @return Stage
     */
    public function frontendInvoiceAddressChange( $Id, $AddressId )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Rechnungsadresse Ändern' );

        $tblInvoice = Invoice::useService()->entityInvoiceById( $Id );
        $tblAddress = Management::serviceAddress()->entityAddressById( $AddressId );
        $Stage->setContent( Invoice::useService()->executeChangeInvoiceAddress( $tblInvoice, $tblAddress ) );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $PaymentTypeId
     *
     * @return Stage
     */
    public function frontendInvoicePaymentTypeChange( $Id, $PaymentTypeId )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Zahlungsart Ändern' );

        $tblInvoice = Invoice::useService()->entityInvoiceById( $Id );
        $tblPaymentType = Banking::useService()->entityPaymentTypeById( $PaymentTypeId );
        $Stage->setContent( Invoice::useService()->executeChangeInvoicePaymentType( $tblInvoice, $tblPaymentType ) );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoicePay( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Bezahlen' );

        $tblInvoice = Invoice::useService()->entityInvoiceById( $Id );
        $Stage->setContent( Invoice::useService()->executePayInvoice( $tblInvoice ) );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $InvoiceItem
     *
     * @return Stage
     */
    public function frontendInvoiceItemEdit( $Id, $InvoiceItem )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Artikel Bearbeiten' );
        $Stage->addButton( new Primary( 'Zurück', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', new ChevronLeft(),
            array( 'Id' => $Id )
        ) );

        if (empty( $Id )) {
            $Stage->setContent( new Warning( 'Die Daten konnten nicht abgerufen werden' ) );
        } else {
            $tblInvoiceItem = Invoice::useService()->entityInvoiceItemById( $Id );
            if (empty( $tblInvoiceItem )) {
                $Stage->setContent( new Warning( 'Der Artikel konnte nicht abgerufen werden' ) );
            } else {

                $Global = $this->getGlobal();
                if (!isset( $Global->POST['InvoiceItems'] )) {
                    $Global->POST['InvoiceItem']['Price'] = str_replace( '.', ',', $tblInvoiceItem->getItemPrice() );
                    $Global->POST['InvoiceItem']['Quantity'] = str_replace( '.', ',',
                        $tblInvoiceItem->getItemQuantity() );
                    $Global->savePost();
                }

                $Stage->setContent(
                    new Layout( array(
                        new LayoutGroup( array(
                            new LayoutRow( array(
                                new LayoutColumn(
                                    new Panel( 'Leistung-Name', $tblInvoiceItem->getCommodityName()
                                        , Panel::PANEL_TYPE_SUCCESS ), 3
                                ),
                                new LayoutColumn(
                                    new Panel( 'Artikel-Name', $tblInvoiceItem->getItemName()
                                        , Panel::PANEL_TYPE_SUCCESS ), 3
                                ),
                                new LayoutColumn(
                                    new Panel( 'Artikel-Beschreibung', $tblInvoiceItem->getItemDescription()
                                        , Panel::PANEL_TYPE_SUCCESS ), 6
                                )
                            ) ),
                        ) ),
                        new LayoutGroup( array(
                            new LayoutRow( array(
                                new LayoutColumn( array(
                                        Invoice::useService()->executeEditInvoiceItem(
                                            new Form( array(
                                                new FormGroup( array(
                                                    new FormRow( array(
                                                        new FormColumn(
                                                            new TextField( 'InvoiceItem[Price]', 'Preis in €', 'Preis',
                                                                new MoneyEuro()
                                                            ), 6 ),
                                                        new FormColumn(
                                                            new TextField( 'InvoiceItem[Quantity]', 'Menge', 'Menge',
                                                                new Quantity()
                                                            ), 6 )
                                                    ) )
                                                ) )
                                            ),
                                                new \SPHERE\Common\Frontend\Form\Repository\Button\Primary( 'Änderungen speichern' )
                                            ), $tblInvoiceItem, $InvoiceItem
                                        )
                                    )
                                )
                            ) )
                        ) )
                    ) )
                );
            }
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoiceItemRemove( $Id )
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Rechnung' );
        $Stage->setDescription( 'Artikel Entfernen' );

        $tblInvoiceItem = Invoice::useService()->entityInvoiceItemById( $Id );
        $Stage->setContent( Invoice::useService()->executeRemoveInvoiceItem( $tblInvoiceItem ) );

        return $Stage;
    }
}