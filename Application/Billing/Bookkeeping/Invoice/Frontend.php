<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblOrder;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblOrderItem;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
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
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\MoneyEuro;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendInvoiceList()
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnungen');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage('Zeigt alle vorhandenen Rechnungen an');
        $Stage->addButton(new Primary('Aufträge', '/Billing/Bookkeeping/Invoice/Order', new Ok(), null, 'Freigeben von Rechnungen'));

        $tblInvoiceAll = Invoice::useService()->getInvoiceAll();

        $TableContent = array();
        if (!empty( $tblInvoiceAll )) {
            array_walk($tblInvoiceAll, function (TblInvoice &$tblInvoice) use (&$TableContent) {

                $Temp['Number'] = $tblInvoice->getNumber();
                $Temp['InvoiceDate'] = $tblInvoice->getInvoiceDate();
                $Temp['BasketName'] = $tblInvoice->getBasketName();
                $Temp['DebtorNumber'] = $tblInvoice->getDebtorNumber();

                $paymentType = $tblInvoice->getServiceBillingBankingPaymentType();
                $Temp['PaymentType'] = "";
                if ($paymentType) {
                    $Temp['PaymentType'] = $paymentType->getName();
                }
                $Temp['Person'] = $tblInvoice->getServiceManagementPerson()->getFullName();
                $Temp['Debtor'] = $tblInvoice->getDebtorFullName();
                $Temp['TotalPrice'] = Invoice::useService()->sumPriceItemAllStringByInvoice($tblInvoice);
                $Temp['Option'] = (new Standard('Anzeige', '/Billing/Bookkeeping/Invoice/Show',
                    new EyeOpen(), array('Id' => $tblInvoice->getId())))->__toString();;
                if ($tblInvoice->isPaid()) {
                    $Temp['IsPaidString'] = "Bezahlt (manuell)";
                } else {
                    if (Balance::useService()->getBalanceByInvoice($tblInvoice)
                        && ( Balance::useService()->sumPriceItemByBalance(Balance::useService()->getBalanceByInvoice($tblInvoice))
                            >= Invoice::useService()->sumPriceItemAllByInvoice($tblInvoice) )
                    ) {
                        $Temp['IsPaidString'] = "Bezahlt";
                    } else {
                        $Temp['IsPaidString'] = "";
                    }
                }
                if ($tblInvoice->isVoid()) {
                    $Temp['IsVoidString'] = "Storniert";
                } else {
                    $Temp['IsVoidString'] = "";
//                    $tblInvoice->Option .= (new Danger( 'Stornieren', '/Billing/Bookkeeping/Invoice/Destroy',
//                        new Remove(), array('Id' => $tblInvoice->getId())))->__toString();
                }
//                if ($tblInvoice->isConfirmed()) {
//                    $Temp['IsConfirmedString'] = "Bestätigt";
//                } else {
//                    $Temp['IsConfirmedString'] = "";
//                }

                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Number'       => 'Nummer',
                                    'InvoiceDate'  => 'Rechnungsdatum',
                                    'BasketName'   => 'Warenkorb',
                                    'Person'       => 'Person',
                                    'Debtor'       => 'Debitor',
                                    'DebtorNumber' => 'Debitoren-Nr',
                                    'PaymentType'  => 'Zahlungsart',
                                    'TotalPrice'   => 'Gesamtpreis',
//                                    'IsConfirmedString' => 'Bestätigt',
                                    'IsPaidString' => 'Bezahlt',
                                    'IsVoidString' => 'Storniert',
                                    'Option'       => 'Option'
                                )
                            )
                        )
                    ), new Title(new Listing().' Übersicht')
                )
            )

        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendOrderOrderList()
    {

        $Stage = new Stage();
        $Stage->setTitle('Auftrag');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage('Zeigt alle noch nicht freigegebenen Rechnungen an');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice', new ChevronLeft()));

        $tblOrderAll = Invoice::useService()->getOrderAll();

        $TableContent = array();
        if (!empty( $tblOrderAll )) {
            array_walk($tblOrderAll, function (TblOrder &$tblOrder) use (&$TableContent) {

                $Temp['BasketName'] = $tblOrder->getBasketName();
                $Temp['DebtorNumber'] = $tblOrder->getDebtorNumber();
                $Temp['PaymentType'] = "";
                $paymentType = $tblOrder->getServiceBillingBankingPaymentType();
                if ($paymentType) {
                    $Temp['PaymentType'] = $paymentType->getName();
                }

                if ($tblOrder->isPaymentDateModified()) {
                    $Temp['InvoiceDateString'] = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Info().' '.$tblOrder->getInvoiceDate());
                    $Temp['PaymentDateString'] = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Info().' '.$tblOrder->getPaymentDate());
                } else {
                    $Temp['InvoiceDateString'] = $tblOrder->getInvoiceDate();
                    $Temp['PaymentDateString'] = $tblOrder->getPaymentDate();
                }

                $Temp['Person'] = $tblOrder->getServiceManagementPerson()->getFullName();
                $Temp['Debtor'] = $tblOrder->getDebtorFullName();
                $Temp['TotalPrice'] = Invoice::useService()->sumPriceItemAllStringByOrder($tblOrder);
                $Temp['Option'] =
                    (new Primary('Bearbeiten und Freigeben', '/Billing/Bookkeeping/Invoice/Order/Edit',
                        new Edit(), array(
                            'Id' => $tblOrder->getId()
                        )))->__toString();
                array_push($TableContent, $Temp);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'BasketName'        => 'Warenkorb',
                                    'InvoiceDateString' => 'Rechnungsdatum',
                                    'PaymentDateString' => 'Zahlungsdatum',
                                    'Person'            => 'Person',
                                    'Debtor'            => 'Debitor',
                                    'DebtorNumber'      => 'Debitoren-Nr',
                                    'PaymentType'       => 'Zahlungsart',
                                    'TotalPrice'        => 'Gesamtpreis',
                                    'Option'            => 'Option'
                                )
                            )
                        )
                    ), new Title(new Listing().' Übersicht')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendOrderEdit($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Erstellen');
        $Stage->setMessage(
            'Hier können Sie die Rechnung bearbeiten und freigeben. <br>
            <b>Hinweis:</b> Freigegebene Rechnung sind nicht änderbar.'
        );
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice/Order', new ChevronLeft()));

        $tblOrder = Invoice::useService()->getOrderById($Id);
        $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblOrder->getDebtorNumber());


        $tblOrderItemAll = Invoice::useService()->getOrderItemAllByOrder($tblOrder);
        if (!empty( $tblOrderItemAll )) {
            array_walk($tblOrderItemAll, function (TblOrderItem &$tblOrderItem) use ($tblOrder) {

                $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblOrder->getDebtorNumber());
                if ($tblDebtor) {
                    $tblAccount = Banking::useService()->getActiveAccountByDebtor($tblDebtor);
                } else {
                    $tblAccount = false;
                }

                if ($tblOrder->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift') {
                    if ($tblAccount) {
                        $tblCommodity = Commodity::useService()->getCommodityByName($tblOrderItem->getCommodityName());
                        if ($tblCommodity) {

                            $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblOrder->getDebtorNumber());
                            if ($tblDebtor) {
//                                    if (Banking::useService()->getReferenceByDebtorAndCommodity($tblDebtor,
//                                        $tblCommodity)) {
                                if (Banking::useService()->getReferenceByAccountAndCommodity($tblAccount,
                                    $tblCommodity)
                                ) {
                                    $tblOrderItem->Status = new \SPHERE\Common\Frontend\Text\Repository\Success(
                                        'Mandatsreferenz'.' '.new Ok()
                                    );
                                } else {
                                    $tblOrderItem->Status = new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                        'keine Mandatsreferenz'.' '.new Disable()
                                    );
                                }
                            } else {
                                $tblOrderItem->Status = new \SPHERE\Common\Frontend\Message\Repository\Danger(
                                    'Debitor nicht gefunden'.' '.new Disable()
                                );
                            }
                        } else {
                            $tblOrderItem->Status = new \SPHERE\Common\Frontend\Message\Repository\Danger(
                                'Leistung nicht gefunden'.' '.new Disable()
                            );
                        }
                    } else {
                        $tblOrderItem->Status = "";
                    }
                } else {
                    $tblOrderItem->Status = "";
                }

                $tblOrderItem->TotalPriceString = $tblOrderItem->getTotalPriceString();
                $tblOrderItem->QuantityString = str_replace('.', ',', $tblOrderItem->getItemQuantity());
                $tblOrderItem->PriceString = $tblOrderItem->getPriceString();
                $tblOrderItem->Option =
                    (new Standard('', '/Billing/Bookkeeping/Invoice/Order/Item/Change',
                        new Edit(), array(
                            'Id'     => $tblOrder->getId(),
                            'IdItem' => $tblOrderItem->getId()
                        )))->__toString().
                    (new Danger('Entfernen',
                        '/Billing/Bookkeeping/Invoice/Order/Item/Remove',
                        new Minus(), array(
                            'Id'        => $tblOrderItem->getId(),
                            'invoiceId' => $tblOrder->getId()
                        )))->__toString();
            });

            if ($tblOrder->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift') {

                $ReferenceOk = true;
                foreach ($tblOrderItemAll as $tblOrderItem) {
                    if ($tblOrderItem->Status != new \SPHERE\Common\Frontend\Text\Repository\Success('Mandatsreferenz'.' '.new Ok())) {
                        $ReferenceOk = false;
                    }
                }

                $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblOrder->getDebtorNumber());
                if ($tblDebtor) {
                    if (Banking::useService()->getActiveAccountByDebtor($tblDebtor)) {
                        if ($ReferenceOk) {
                            $Stage->addButton(new Primary('Geprüft und Freigeben',
                                '/Billing/Bookkeeping/Invoice/Confirm',
                                new Ok(), array(
                                    'Id' => $Id
                                )
                            ));
                        } else {
                            $Stage->addButton(new Standard('Geprüft und Freigeben',
                                '/Billing/Bookkeeping/Invoice/Order/Edit',
                                new Ok(), array(
                                    'Id' => $Id
                                ), 'Fehlende Mandatsreferenz'
                            ));
                        }
                    }
                }
            } else {
                $Stage->addButton(new Primary('Geprüft und Freigeben', '/Billing/Bookkeeping/Invoice/Confirm',
                    new Ok(), array(
                        'Id' => $Id
                    )
                ));
            }

            $Stage->addButton(new Danger('Löschen', '/Billing/Bookkeeping/Invoice/Destroy',
                new Remove(), array(
                    'Id' => $Id
                )
            ));

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
//                            new LayoutColumn(
//                                new Panel('Rechnungsnummer', $tblInvoice->getNumber(), Panel::PANEL_TYPE_PRIMARY), 3
//                            ),
                            new LayoutColumn(
                                new Panel('Warenkorb', $tblOrder->getBasketName(), Panel::PANEL_TYPE_DEFAULT), 3
                            ),
                            new LayoutColumn(
                                new Panel('Rechnungsdatum', $tblOrder->getInvoiceDate(),
                                    $tblOrder->isPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT),
                                3
                            ),
                            new LayoutColumn(
                                new Panel('Zahlungsdatum', $tblOrder->getPaymentDate(),
                                    $tblOrder->isPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT),
                                3
                            ),
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                new Aspect('Empfänger')
                            )
                        ),
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Debitor', $tblOrder->getDebtorFullName()), 3
                            ),
                            new LayoutColumn(
                                new Panel('Debitorennummer', $tblOrder->getDebtorNumber()), 3
                            ),
                            new LayoutColumn(
                                new Panel('Person', $tblOrder->getServiceManagementPerson()->getFullName()), 3
                            )
                        )),
                        new LayoutRow(array(
                            new LayoutColumn(
                                ( $tblOrder->getServiceManagementAddress()
                                    ? new Panel(
                                        new MapMarker().' Rechnungsadresse', array(
                                        $tblOrder->getServiceManagementAddress()->getStreetName().' '.$tblOrder->getServiceManagementAddress()->getStreetNumber().'<br/>'.
                                        $tblOrder->getServiceManagementAddress()->getTblCity()->getCode().' '.$tblOrder->getServiceManagementAddress()->getTblCity()->getName()
                                    ),
                                        Panel::PANEL_TYPE_DEFAULT,
                                        ( ( $tblDebtor = Banking::useService()->getDebtorByDebtorNumber(
                                            $tblOrder->getDebtorNumber()) )
                                        && count(Address::useService()->getAddressAllByPerson(
                                            $tblDebtor->getServiceManagementPerson())) > 1
                                            ? new Standard('',
                                                '/Billing/Bookkeeping/Invoice/Order/Address/Select',
                                                new Edit(),
                                                array(
                                                    'Id'        => $tblOrder->getId(),
                                                    'AddressId' => $tblOrder->getServiceManagementAddress()->getId()
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
                                    $tblOrder->getServiceBillingBankingPaymentType()->getName(),
                                    Panel::PANEL_TYPE_DEFAULT,
                                    new Standard('',
                                        '/Billing/Bookkeeping/Invoice/Order/Payment/Type/Select',
                                        new Edit(),
                                        array(
                                            'Id' => $tblOrder->getId()
                                        )
                                    )
                                ), 3
                            ),
                        )),
                        ( ( $tblOrder->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift' ) ?
                            ( $tblDebtor ) ?
                                new LayoutRow(
                                    new LayoutColumn(
                                        self::layoutAccount($tblDebtor,
                                            '/Billing/Bookkeeping/Invoice/Order/Edit', $tblOrder->getId())
                                    )
                                ) : null
                            : null
                        ),
                        new LayoutRow(
                            new LayoutColumn(
                                new Aspect('Betrag')
                            )
                        ),
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Rechnungsbetrag',
                                    Invoice::useService()->sumPriceItemAllStringByOrder($tblOrder)), 3
                            )
                        ))
                    ), new Title('Kopf')),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                    new TableData($tblOrderItemAll, null,
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
                        )),
                    ), new Title('Positionen'))
                ))
            );
        }

        return $Stage;
    }

    /**
     * @param TblDebtor $tblDebtor
     * @param           $Path
     * @param           $IdBack
     *
     * @return Layout
     */
    public function layoutAccount(TblDebtor $tblDebtor, $Path, $IdBack)
    {

        $tblAccountList = Banking::useService()->getAccountAllByDebtor($tblDebtor);
        if (!empty( $tblAccountList )) {
            $mainAccount = false;
            foreach ($tblAccountList as $Account) {
                if ($Account->getActive()) {
                    $mainAccount = true;
                }
            }
            if ($mainAccount === false) {
                $Warning = new LayoutRow(new LayoutColumn(
                    new Warning('Bitte legen sie ein aktives Konto fest (mit '.new Ok().')')));
            } else {
                $Warning = null;
            }

            /** @var TblAccount $tblAccount */
            foreach ($tblAccountList as $Key => &$tblAccount) {
                $tblAccountList[$Key] = new LayoutColumn(
                    new Panel(( $tblAccount->getActive() ?
                            'aktives Konto '
                            : null ).'&nbsp', array(
                        new Panel('', array(
                            'Besitzer'.new PullRight($tblAccount->getOwner()),
                            'IBAN'.new PullRight($tblAccount->getIBAN()),
                            'BIC'.new PullRight($tblAccount->getBIC()),
                            'Kassenzeichen'.new PullRight($tblAccount->getCashSign()),
                            'Bankname'.new PullRight($tblAccount->getBankName()),
                            Banking::useFrontend()->layoutReference($tblAccount),
                        ), null, ( $tblAccount->getActive() === false ?
                            new Standard('', '/Billing/Accounting/Banking/Account/Activate', new Ok(),
                                array(
                                    'Id'      => $tblDebtor->getId(),
                                    'Account' => $tblAccount->getId(),
                                    'Path'    => $Path,
                                    'IdBack'  => $IdBack
                                )) : null )
                        )
                    ), ( $tblAccount->getActive() ?
                        Panel::PANEL_TYPE_SUCCESS
                        : Panel::PANEL_TYPE_DEFAULT ))
                    , 4);
            }
        } else {
            $tblAccountList = new LayoutColumn(new Warning('Es ist kein Konto für diesen Debitor angelegt'));
        }
        if (!isset( $Warning )) {
            $Warning = null;
        }

        return new Layout(
            new LayoutGroup(array(new LayoutRow($tblAccountList), $Warning), new Title('Kontodaten'))
        );
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoiceShow($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Anzeigen');
        $Stage->addButton(new Primary('Zurück', '/Billing/Bookkeeping/Invoice', new ChevronLeft()));


        $tblInvoice = Invoice::useService()->getInvoiceById($Id);

        if ($tblInvoice->isVoid()) {
            $Stage->setMessage(new \SPHERE\Common\Frontend\Message\Repository\Danger("Diese Rechnung wurde storniert"));
        } else {
            $Stage->addButton(new Danger('Stornieren', '/Billing/Bookkeeping/Invoice/Cancel', new Remove(), array('Id' => $Id)));
        }

        $tblInvoiceItemAll = Invoice::useService()->getInvoiceItemAllByInvoice($tblInvoice);
        if (!empty( $tblInvoiceItemAll )) {
            array_walk($tblInvoiceItemAll,
                function (TblInvoiceItem &$tblInvoiceItem) use ($tblInvoice) {

                    $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber());
                    if ($tblDebtor) {
                        $tblAccount = Banking::useService()->getActiveAccountByDebtor($tblDebtor);
                    } else {
                        $tblAccount = false;
                    }

                    if ($tblInvoice->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift') {
                        if ($tblAccount) {
                            $tblCommodity = Commodity::useService()->getCommodityByName($tblInvoiceItem->getCommodityName());
                            if ($tblCommodity) {

                                $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber());
                                if ($tblDebtor) {
                                    if (Banking::useService()->getReferenceByDebtorAndCommodity($tblDebtor,
                                        $tblCommodity)
                                    ) {
                                        $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Text\Repository\Success(
                                            'Mandatsreferenz '.new Ok()
                                        );
                                    } else {
                                        $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                            'keine Mandatsreferenz '.new Disable()
                                        );
                                    }
                                } else {
                                    $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Text\Repository\Danger(
                                        'Debitor nicht gefunden '.new Disable()
                                    );
                                }
                            } else {
                                $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Text\Repository\Danger(
                                    'Leistung nicht gefunden '.new Disable()
                                );
                            }
                        } else {
                            $tblInvoiceItem->Status = "";
                        }
                    } else {
                        $tblInvoiceItem->Status = "";
                    }

                    $tblInvoiceItem->TotalPriceString = $tblInvoiceItem->getTotalPriceString();
                    $tblInvoiceItem->QuantityString = str_replace('.', ',', $tblInvoiceItem->getItemQuantity());
                    $tblInvoiceItem->PriceString = $tblInvoiceItem->getPriceString();
                });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Rechnungsnummer', $tblInvoice->getNumber(), Panel::PANEL_TYPE_PRIMARY), 3
                        ),
                        new LayoutColumn(
                            new Panel('Warenkorb', $tblInvoice->getBasketName(), Panel::PANEL_TYPE_DEFAULT), 3
                        ),
                        new LayoutColumn(
                            new Panel('Rechnungsdatum', $tblInvoice->getInvoiceDate(),
                                $tblInvoice->isPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT),
                            3
                        ),
                        new LayoutColumn(
                            new Panel('Zahlungsdatum', $tblInvoice->getPaymentDate(),
                                $tblInvoice->isPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT),
                            3
                        ),
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Aspect('Empfänger')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Debitor', $tblInvoice->getDebtorFullName()), 3
                        ),
                        new LayoutColumn(
                            new Panel('Debitorennummer', $tblInvoice->getDebtorNumber()), 3
                        ),
                        new LayoutColumn(
                            new Panel('Person', $tblInvoice->getServiceManagementPerson()->getFullName()), 3
                        )
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            ( $tblInvoice->getServiceManagementAddress()
                                ? new Panel(
                                    new MapMarker().' Rechnungsadresse', array(
                                    $tblInvoice->getServiceManagementAddress()->getStreetName().' '.$tblInvoice->getServiceManagementAddress()->getStreetNumber().'<br/>'.
                                    $tblInvoice->getServiceManagementAddress()->getTblCity()->getCode().' '.$tblInvoice->getServiceManagementAddress()->getTblCity()->getName()
                                ),
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
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            new Aspect('Betrag')
                        )
                    ),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Rechnungsbetrag',
                                Invoice::useService()->sumPriceItemAllStringByInvoice($tblInvoice)), 3
                        ),
                        new LayoutColumn(
                            $tblInvoice->isConfirmed() ?
                                ( $tblInvoice->isPaid()
                                    ? new Success("Bezahlt")
                                    : ( round(Balance::useService()->sumPriceItemByBalance(Balance::useService()->getBalanceByInvoice($tblInvoice)),
                                        2)
                                    >= round(Invoice::useService()->sumPriceItemAllByInvoice($tblInvoice), 2)
                                        ? new Panel('Bezahlbetrag', Balance::useService()->sumPriceItemStringByBalance(
                                            Balance::useService()->getBalanceByInvoice($tblInvoice)),
                                            Panel::PANEL_TYPE_SUCCESS)
                                        : new Panel('Bezahlbetrag', Balance::useService()->sumPriceItemStringByBalance(
                                            Balance::useService()->getBalanceByInvoice($tblInvoice)),
                                            Panel::PANEL_TYPE_DANGER) ) )
                                : new \SPHERE\Common\Frontend\Text\Repository\Success("")
                            , 3)
                    )),
                ), new Title('Kopf')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                                new TableData($tblInvoiceItemAll, null,
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
                    )),
                ), new Title('Positionen'))
            ))
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoiceConfirm($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Auftrag');
        $Stage->setDescription('Bestätigen');
        $Account = false;
        $tblOrder = Invoice::useService()->getOrderById($Id);
        if (!$tblOrder) {
            return new Warning('Auftrag nicht gefunden')
            .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_ERROR);
        }

        if ($tblOrder->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift') {
            $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblOrder->getDebtorNumber());
            if ($tblDebtor) {
                $Account = Banking::useService()->getActiveAccountByDebtor($tblDebtor)->getId();
            }
        }
        $Stage->setContent(Invoice::useService()->createInvoice($tblOrder, $Account));

        return $Stage;
    }


    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoiceCancel($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Stornieren');
        $tblInvoice = Invoice::useService()->getInvoiceById($Id);

        $Stage->setContent(Invoice::useService()->cancelInvoice($tblInvoice));

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendOrderDestroy($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Auftrag');
        $Stage->setDescription('Entfernen');

        $tblOrder = Invoice::useService()->getOrderById($Id);

        if ($tblOrder) {
            $Stage->setContent(Invoice::useService()->destroyOrder($tblOrder));
        } else {
            $Stage->setContent(new Warning('Der Auftrag wurde nicht gefunden.')
                .new Redirect('/Billing/Bookkeeping/Invoice/Order', Redirect::TIMEOUT_ERROR));
        }

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoiceAddressSelect($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Rechnungsadresse Auswählen');
        $Stage->addButton(new Primary('Zurück', '/Billing/Bookkeeping/Invoice/Order/Edit', new ChevronLeft(),
            array('Id' => $Id)
        ));

        $tblOrder = Invoice::useService()->getOrderById($Id);
        $tblAddressAll = Address::useService()->getAddressAllByPerson(
            Banking::useService()->getDebtorByDebtorNumber($tblOrder->getDebtorNumber())->getServiceManagementPerson());

        $layoutGroup = self::layoutAddress($tblAddressAll, $tblOrder->getServiceManagementAddress(), $tblOrder);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
//                        new LayoutColumn(array(
//                            new Panel('Rechnungsnummer', $tblOrder->getNumber(), Panel::PANEL_TYPE_SUCCESS)
//                        ), 3),
                        new LayoutColumn(array(
                            new Panel('Empfänger', $tblOrder->getDebtorFullName(), Panel::PANEL_TYPE_SUCCESS)
                        ), 6)
                    ))
                ))
            ))
            .$layoutGroup
        );

        return $Stage;
    }

    /**
     * @param            $tblAddressList
     * @param TblAddress $invoiceAddress
     * @param TblOrder   $tblOrder
     *
     * @return Layout
     */
    private static function layoutAddress($tblAddressList, TblAddress $invoiceAddress, TblOrder $tblOrder)
    {

        if (!empty( $tblAddressList )) {
            /** @var TblToPerson[] $tblAddressList */
            foreach ($tblAddressList as &$tblToPerson) {
                if ($invoiceAddress !== null && $invoiceAddress->getId() === $tblToPerson->getTblAddress()->getId()) {
                    $AddressType = new MapMarker().' Rechnungsadresse';
                    $PanelType = Panel::PANEL_TYPE_SUCCESS;
                } else {
                    $AddressType = new MapMarker().' Adresse';
                    $PanelType = Panel::PANEL_TYPE_DEFAULT;
                }
                $tblAddress = $tblToPerson->getTblAddress();

                $tblToPerson = new LayoutColumn(
                    new Panel(
                        $AddressType,
                        $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber().'<br/>'.
                        $tblAddress->getTblCity()->getCode().' '.$tblAddress->getTblCity()->getCode(),
                        $PanelType,

                        new Standard('Auswählen', '/Billing/Bookkeeping/Invoice/Order/Address/Change',
                            new Ok(),
                            array(
                                'Id'        => $tblOrder->getId(),
                                'AddressId' => $tblAddress->getId()
                            )
                        )
                    ), 3
                );
            }
        } else {
            $tblAddressList = array(
                new LayoutColumn(
                    new Warning('Keine Adressen hinterlegt', new \SPHERE\Common\Frontend\Icon\Repository\Warning())
                )
            );
        }
        /** @var LayoutColumn $tblAddressList */
        return new Layout(
            new LayoutGroup(new LayoutRow($tblAddressList), new Title('Adressen'))
        );
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoicePaymentTypeSelect($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Zahlungsart Auswählen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice/Order/Edit', new ChevronLeft(),
            array('Id' => $Id)
        ));

        $tblOrder = Invoice::useService()->getOrderById($Id);
        $tblPaymentTypeList = Banking::useService()->getPaymentTypeAll();
        foreach ($tblPaymentTypeList as &$tblPaymentType) {
            $tblPaymentType = new LayoutColumn(
                new Panel(
                    'Zahlungsart: '.new PullRight($tblPaymentType->getName()),
                    array(),
                    ( $tblOrder->getServiceBillingBankingPaymentType()->getId() === $tblPaymentType->getId() ) ?
                        Panel::PANEL_TYPE_SUCCESS :
                        Panel::PANEL_TYPE_DEFAULT,
                    new Standard('Auswählen', '/Billing/Bookkeeping/Invoice/Order/PaymentType/Change',
                        new Ok(),
                        array(
                            'Id'          => $tblOrder->getId(),
                            'PaymentType' => $tblPaymentType->getId(),
                        )
                    )
                ), 4
            );
        }
        /** @var LayoutColumn $tblPaymentTypeList */
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
//                        new LayoutColumn(array(
//                            new Panel('Rechnungsnummer', $tblOrder->getNumber(), Panel::PANEL_TYPE_SUCCESS)
//                        ), 3),
                        new LayoutColumn(array(
                            new Panel('Empfänger', $tblOrder->getDebtorFullName(), Panel::PANEL_TYPE_SUCCESS)
                        ), 6)
                    ))
                )),
                new LayoutGroup(
                    new LayoutRow(
                        $tblPaymentTypeList
                    ), new Title('Zahlungsarten')
                )
            ))
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $AddressId
     *
     * @return Stage
     */
    public function frontendInvoiceAddressChange($Id, $AddressId)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Rechnungsadresse Ändern');

        $tblOrder = Invoice::useService()->getOrderById($Id);
        $tblAddress = Address::useService()->getAddressById($AddressId);
        $Stage->setContent(Invoice::useService()->changeOrderAddress($tblOrder, $tblAddress));

        return $Stage;
    }

    /**
     * @param $Id
     * @param $PaymentType
     *
     * @return Stage
     */
    public function frontendInvoicePaymentTypeChange($Id, $PaymentType)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Zahlungsart Ändern');

        $tblInvoice = Invoice::useService()->getInvoiceById($Id);
        $tblPaymentType = Banking::useService()->getPaymentTypeById($PaymentType);
        $Stage->setContent(Invoice::useService()->changeInvoicePaymentType($tblInvoice, $tblPaymentType));

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendInvoicePay($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Bezahlen');

        $tblInvoice = Invoice::useService()->getInvoiceById($Id);
        $Stage->setContent(Invoice::useService()->createPayInvoice($tblInvoice));

        return $Stage;
    }

    /**
     * @param      $Id
     * @param      $IdItem
     * @param null $OrderItem
     *
     * @return Stage
     */
    public function frontendOrderItemChange($Id, $IdItem, $OrderItem = null)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Artikel Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice/Order/Edit', new ChevronLeft(),
            array('Id' => $Id)
        ));

        if (empty( $IdItem )) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblOrderItem = Invoice::useService()->getOrderItemById($IdItem);
            if (empty( $tblOrderItem )) {
                $Stage->setContent(new Warning('Der Artikel konnte nicht abgerufen werden'));
            } else {

                $Global = $this->getGlobal();
                if (!isset( $Global->POST['OrderItem'] )) {
                    $Global->POST['OrderItem']['Price'] = str_replace('.', ',', $tblOrderItem->getItemPrice());
                    $Global->POST['OrderItem']['Quantity'] = str_replace('.', ',',
                        $tblOrderItem->getItemQuantity());
                    $Global->savePost();
                }

                $Form = new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new TextField('OrderItem[Price]', 'Preis in €', 'Preis',
                                    new MoneyEuro()
                                ), 6),
                            new FormColumn(
                                new TextField('OrderItem[Quantity]', 'Menge', 'Menge',
                                    new Quantity()
                                ), 6)
                        ))
                    ))
                ));
                $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Speichern', new Save()));
                $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Leistung-Name', $tblOrderItem->getCommodityName()
                                        , Panel::PANEL_TYPE_SUCCESS), 3
                                ),
                                new LayoutColumn(
                                    new Panel('Artikel-Name', $tblOrderItem->getItemName()
                                        , Panel::PANEL_TYPE_SUCCESS), 3
                                ),
                                new LayoutColumn(
                                    new Panel('Artikel-Beschreibung', $tblOrderItem->getItemDescription()
                                        , Panel::PANEL_TYPE_SUCCESS), 6
                                )
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    Invoice::useService()->changeOrderItem(
                                        $Form, $tblOrderItem, $OrderItem
                                    )
                                ))
                            ))
                        ))
                    ))
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
    public function frontendOrderItemRemove($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Artikel Entfernen');

        $tblOrderItem = Invoice::useService()->getOrderItemById($Id);
        $Stage->setContent(Invoice::useService()->removeOrderItem($tblOrderItem));

        return $Stage;
    }
}
