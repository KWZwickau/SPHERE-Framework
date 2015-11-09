<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
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
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\MoneyEuro;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Quantity;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
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

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendInvoiceStatus()
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnungen');
        $Stage->setDescription('Übersicht');

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendInvoiceList()
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnungen');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage('Zeigt alle vorhandenen Rechnungen an');
        $Stage->addButton(new Primary('Freigeben', '/Billing/Bookkeeping/Invoice/IsNotConfirmed', new Ok()));

        $tblInvoiceAll = Invoice::useService()->getInvoiceAll();

        if (!empty( $tblInvoiceAll )) {
            array_walk($tblInvoiceAll, function (TblInvoice &$tblInvoice) {

                $paymentType = $tblInvoice->getServiceBillingBankingPaymentType();
                if ($paymentType) {
                    $tblInvoice->PaymentType = $paymentType->getName();
                } else {
                    $tblInvoice->PaymentType = "";
                }

                $tblInvoice->Person = $tblInvoice->getServiceManagementPerson()->getFullName();
                $tblInvoice->Debtor = $tblInvoice->getDebtorFullName();
                $tblInvoice->TotalPrice = Invoice::useService()->sumPriceItemAllStringByInvoice($tblInvoice);
                $tblInvoice->Option = (new Standard('Anzeige', '/Billing/Bookkeeping/Invoice/Show',
                    new EyeOpen(), array('Id' => $tblInvoice->getId())))->__toString();;
                if ($tblInvoice->getIsPaid()) {
                    $tblInvoice->IsPaidString = "Bezahlt (manuell)";
                } else {
                    if (Balance::useService()->getBalanceByInvoice($tblInvoice)
                        && ( Balance::useService()->sumPriceItemByBalance(Balance::useService()->getBalanceByInvoice($tblInvoice))
                            >= Invoice::useService()->sumPriceItemAllByInvoice($tblInvoice) )
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
            });
        }

        $Stage->setContent(
            new TableData($tblInvoiceAll, null,
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
        $Stage->setTitle('Rechnungen');
        $Stage->setDescription('Freigeben');
        $Stage->setMessage('Zeigt alle noch nicht freigegebenen Rechnungen an');

        $tblInvoiceAllByIsConfirmedState = Invoice::useService()->getInvoiceAllByIsConfirmedState(false);
        $tblInvoiceAllByIsVoid = Invoice::useService()->getInvoiceAllByIsVoidState(true);

        if ($tblInvoiceAllByIsConfirmedState && $tblInvoiceAllByIsVoid) {
            $tblInvoiceAllByIsConfirmedState = array_udiff($tblInvoiceAllByIsConfirmedState, $tblInvoiceAllByIsVoid,
                function (TblInvoice $invoiceA, TblInvoice $invoiceB) {

                    return $invoiceA->getId() - $invoiceB->getId();
                });
        }

        if (!empty( $tblInvoiceAllByIsConfirmedState )) {
            array_walk($tblInvoiceAllByIsConfirmedState, function (TblInvoice &$tblInvoice) {

                $paymentType = $tblInvoice->getServiceBillingBankingPaymentType();
                if ($paymentType) {
                    $tblInvoice->PaymentType = $paymentType->getName();
                } else {
                    $tblInvoice->PaymentType = "";
                }

                if ($tblInvoice->getIsPaymentDateModified()) {
                    $tblInvoice->InvoiceDateString = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Info().' '.$tblInvoice->getInvoiceDate());
                    $tblInvoice->PaymentDateString = new \SPHERE\Common\Frontend\Text\Repository\Warning(new Info().' '.$tblInvoice->getPaymentDate());
                } else {
                    $tblInvoice->InvoiceDateString = $tblInvoice->getInvoiceDate();
                    $tblInvoice->PaymentDateString = $tblInvoice->getPaymentDate();
                }

                $tblInvoice->Person = $tblInvoice->getServiceManagementPerson()->getFullName();
                $tblInvoice->Debtor = $tblInvoice->getDebtorFullName();
                $tblInvoice->TotalPrice = Invoice::useService()->sumPriceItemAllStringByInvoice($tblInvoice);
                $tblInvoice->Option =
                    (new Primary('Bearbeiten und Freigeben', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit',
                        new Edit(), array(
                            'Id' => $tblInvoice->getId()
                        )))->__toString();
            });
        }

        $Stage->setContent(
            new TableData($tblInvoiceAllByIsConfirmedState, null,
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
     *
     * @return Stage
     */
    public function frontendInvoiceEdit($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Bearbeiten');
        $Stage->setMessage(
            'Hier können Sie die Rechnung bearbeiten und freigeben. <br>
            <b>Hinweis:</b> Freigegebene Rechnung sind nicht mehr bearbeitbar.'
        );
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice/IsNotConfirmed', new ChevronLeft()));

        $tblInvoice = Invoice::useService()->getInvoiceById($Id);
        $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber());

        if ($tblInvoice->getIsConfirmed()) {
            $Stage->setContent(new Warning('Die Rechnung wurde bereits bestätigt und freigegeben und kann nicht mehr bearbeitet werden')
                .new Redirect('/Billing/Bookkeeping/Invoice', 2));
        } else {
            $tblInvoiceItemAll = Invoice::useService()->getInvoiceItemAllByInvoice($tblInvoice);
            if (!empty( $tblInvoiceItemAll )) {
                array_walk($tblInvoiceItemAll,
                    function (TblInvoiceItem &$tblInvoiceItem, $index, TblInvoice $tblInvoice) {

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
//                                    if (Banking::useService()->getReferenceByDebtorAndCommodity($tblDebtor,
//                                        $tblCommodity)) {
                                        if (Banking::useService()->getReferenceByAccountAndCommodity($tblAccount, $tblCommodity)) {
                                            $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Text\Repository\Success(
                                                'Mandatsreferenz'.' '.new Ok()
                                            );
                                        } else {
                                            $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Text\Repository\Warning(
                                                'keine Mandatsreferenz'.' '.new Disable()
                                            );
                                        }
                                    } else {
                                        $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Message\Repository\Danger(
                                            'Debitor nicht gefunden'.' '.new Disable()
                                        );
                                    }
                                } else {
                                    $tblInvoiceItem->Status = new \SPHERE\Common\Frontend\Message\Repository\Danger(
                                        'Leistung nicht gefunden'.' '.new Disable()
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
                        $tblInvoiceItem->Option =
                            (new Standard('', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Item/Change',
                                new Edit(), array(
                                    'Id'     => $tblInvoice->getId(),
                                    'IdItem' => $tblInvoiceItem->getId()
                                )))->__toString().
                            (new Danger('Entfernen',
                                '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Item/Remove',
                                new Minus(), array(
                                    'Id' => $tblInvoiceItem->getId()
                                )))->__toString();
                    }, $tblInvoice);
            }

            if ($tblInvoice->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift') {

                $ReferenceOk = true;
                foreach ($tblInvoiceItemAll as $tblInvoiceItem) {
                    if ($tblInvoiceItem->Status != new \SPHERE\Common\Frontend\Text\Repository\Success('Mandatsreferenz'.' '.new Ok())) {
                        $ReferenceOk = false;
                    }
                }

                $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber());
                if ($tblDebtor) {
                    if (Banking::useService()->getActiveAccountByDebtor($tblDebtor)) {
                        if ($ReferenceOk) {
                            $Stage->addButton(new Primary('Geprüft und Freigeben', '/Billing/Bookkeeping/Invoice/Confirm',
                                new Ok(), array(
                                    'Id' => $Id
                                )
                            ));
                        } else {
                            $Stage->addButton(new Standard('Geprüft und Freigeben', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit',
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

            $Stage->addButton(new Danger('Stornieren', '/Billing/Bookkeeping/Invoice/Cancel',
                new Remove(), array(
                    'Id' => $Id
                )
            ));


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
                                    $tblInvoice->getIsPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT),
                                3
                            ),
                            new LayoutColumn(
                                new Panel('Zahlungsdatum', $tblInvoice->getPaymentDate(),
                                    $tblInvoice->getIsPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT),
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
                                        $tblInvoice->getServiceManagementAddress()->getTblCity()->getCode().' '.$tblInvoice->getServiceManagementAddress()->getTblCity()->getName()),
                                        Panel::PANEL_TYPE_DEFAULT,
                                        ( ( $tblDebtor = Banking::useService()->getDebtorByDebtorNumber(
                                            $tblInvoice->getDebtorNumber()) )
                                        && count(Address::useService()->getAddressAllByPerson(
                                            $tblDebtor->getServiceManagementPerson())) > 1
                                            ? new Standard('',
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
                                    new Standard('',
                                        '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Payment/Type/Select',
                                        new Edit(),
                                        array(
                                            'Id' => $tblInvoice->getId()
                                        )
                                    )
                                ), 3
                            ),
                        )),
                        ( ( $tblInvoice->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift' ) ?
                            ( $tblDebtor ) ?
                                new LayoutRow(
                                    new LayoutColumn(
                                        self::layoutAccount($tblDebtor, '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', $tblInvoice->getId())
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
                                    Invoice::useService()->sumPriceItemAllStringByInvoice($tblInvoice)), 3
                            )
                        ))
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
                        ), null, ( $tblAccount->getActive() === false ?
                            new Standard('', '/Billing/Accounting/Banking/Account/Activate', new Ok(),
                                array('Id'      => $tblDebtor->getId(),
                                      'Account' => $tblAccount->getId(),
                                      'Path'    => $Path,
                                      'IdBack'  => $IdBack)) : null )
                        )), ( $tblAccount->getActive() ?
                        Panel::PANEL_TYPE_SUCCESS
                        : Panel::PANEL_TYPE_DEFAULT ))
                    , 4);
            }
        } else {
            $tblAccountList = new LayoutColumn(new Warning('Es ist kein Konto für diesen Debitor angelegt'));
        }
        if (!isset( $Warning ))
            $Warning = null;

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

        if ($tblInvoice->getIsVoid()) {
            $Stage->setMessage(new \SPHERE\Common\Frontend\Message\Repository\Danger("Diese Rechnung wurde storniert"));
        }

        $tblInvoiceItemAll = Invoice::useService()->getInvoiceItemAllByInvoice($tblInvoice);
        if (!empty( $tblInvoiceItemAll )) {
            array_walk($tblInvoiceItemAll,
                function (TblInvoiceItem &$tblInvoiceItem, $index, TblInvoice $tblInvoice) {

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
                }, $tblInvoice);
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
                                $tblInvoice->getIsPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT),
                            3
                        ),
                        new LayoutColumn(
                            new Panel('Zahlungsdatum', $tblInvoice->getPaymentDate(),
                                $tblInvoice->getIsPaymentDateModified() ? Panel::PANEL_TYPE_WARNING : Panel::PANEL_TYPE_DEFAULT),
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
                                    $tblInvoice->getServiceManagementAddress()->getTblCity()->getCode().' '.$tblInvoice->getServiceManagementAddress()->getTblCity()->getName()),
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
                            $tblInvoice->getIsConfirmed() ?
                                ( $tblInvoice->getIsPaid()
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
     * @param $Data
     *
     * @return Stage
     */
    public function frontendInvoiceConfirm($Id, $Data)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Freigeben');
        $Account = false;

        $tblInvoice = Invoice::useService()->getInvoiceById($Id);
        if ($tblInvoice->getServiceBillingBankingPaymentType()->getName() === 'SEPA-Lastschrift') {
            $tblDebtor = Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber());
            if ($tblDebtor) {
                $Account = Banking::useService()->getActiveAccountByDebtor($tblDebtor)->getId();
            }
        }
        $Stage->setContent(Invoice::useService()->confirmInvoice($tblInvoice, $Data, $Account));

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
    public function frontendInvoiceAddressSelect($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Rechnungsadresse Auswählen');
        $Stage->addButton(new Primary('Zurück', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', new ChevronLeft(),
            array('Id' => $Id)
        ));

        $tblInvoice = Invoice::useService()->getInvoiceById($Id);
        $tblAddressAll = Address::useService()->getAddressAllByPerson(
            Banking::useService()->getDebtorByDebtorNumber($tblInvoice->getDebtorNumber())->getServiceManagementPerson());

        $layoutGroup = self::layoutAddress($tblAddressAll, $tblInvoice->getServiceManagementAddress(), $tblInvoice);

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Rechnungsnummer', $tblInvoice->getNumber(), Panel::PANEL_TYPE_SUCCESS)
                        ), 3),
                        new LayoutColumn(array(
                            new Panel('Empfänger', $tblInvoice->getDebtorFullName(), Panel::PANEL_TYPE_SUCCESS)
                        ), 3)
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
     * @param TblInvoice $tblInvoice
     *
     * @return Layout
     */
    private static function layoutAddress($tblAddressList, TblAddress $invoiceAddress, TblInvoice $tblInvoice)
    {

        if (!empty( $tblAddressList )) {
            /** @var TblToPerson[] $tblAddressList */
            foreach ($tblAddressList as &$tblToPerson) {
                if ($invoiceAddress != null && $invoiceAddress->getId() === $tblToPerson->getTblAddress()->getId()) {
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

                        new Standard('Auswählen', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Address/Change',
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
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', new ChevronLeft(),
            array('Id' => $Id)
        ));

        $tblInvoice = Invoice::useService()->getInvoiceById($Id);
        $tblPaymentTypeList = Banking::useService()->getPaymentTypeAll();
        foreach ($tblPaymentTypeList as &$tblPaymentType) {
            $tblPaymentType = new LayoutColumn(
                new Panel(
                    'Zahlungsart: '.new PullRight($tblPaymentType->getName()),
                    array(),
                    ( $tblInvoice->getServiceBillingBankingPaymentType()->getId() === $tblPaymentType->getId() ) ?
                        Panel::PANEL_TYPE_SUCCESS :
                        Panel::PANEL_TYPE_DEFAULT,
                    new Standard('Auswählen', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/PaymentType/Change',
                        new Ok(),
                        array(
                            'Id'          => $tblInvoice->getId(),
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
                        new LayoutColumn(array(
                            new Panel('Rechnungsnummer', $tblInvoice->getNumber(), Panel::PANEL_TYPE_SUCCESS)
                        ), 3),
                        new LayoutColumn(array(
                            new Panel('Empfänger', $tblInvoice->getDebtorFullName(), Panel::PANEL_TYPE_SUCCESS)
                        ), 3)
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

        $tblInvoice = Invoice::useService()->getInvoiceById($Id);
        $tblAddress = Address::useService()->getAddressById($AddressId);
        $Stage->setContent(Invoice::useService()->changeInvoiceAddress($tblInvoice, $tblAddress));

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
     * @param $Id
     * @param $IdItem
     * @param $InvoiceItem
     *
     * @return Stage
     */
    public function frontendInvoiceItemChange($Id, $IdItem, $InvoiceItem)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Artikel Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Bookkeeping/Invoice/IsNotConfirmed/Edit', new ChevronLeft(),
            array('Id' => $Id)
        ));

        if (empty( $IdItem )) {
            $Stage->setContent(new Warning('Die Daten konnten nicht abgerufen werden'));
        } else {
            $tblInvoiceItem = Invoice::useService()->getInvoiceItemById($IdItem);
            if (empty( $tblInvoiceItem )) {
                $Stage->setContent(new Warning('Der Artikel konnte nicht abgerufen werden'));
            } else {

                $Global = $this->getGlobal();
                if (!isset( $Global->POST['InvoiceItem'] )) {
                    $Global->POST['InvoiceItem']['Price'] = str_replace('.', ',', $tblInvoiceItem->getItemPrice());
                    $Global->POST['InvoiceItem']['Quantity'] = str_replace('.', ',',
                        $tblInvoiceItem->getItemQuantity());
                    $Global->savePost();
                }

                $Form = new Form(array(
                    new FormGroup(array(
                        new FormRow(array(
                            new FormColumn(
                                new TextField('InvoiceItem[Price]', 'Preis in €', 'Preis',
                                    new MoneyEuro()
                                ), 6),
                            new FormColumn(
                                new TextField('InvoiceItem[Quantity]', 'Menge', 'Menge',
                                    new Quantity()
                                ), 6)
                        ))
                    ))
                ));
                $Form->appendFormButton(new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Änderungen speichern'));
                $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    new Panel('Leistung-Name', $tblInvoiceItem->getCommodityName()
                                        , Panel::PANEL_TYPE_SUCCESS), 3
                                ),
                                new LayoutColumn(
                                    new Panel('Artikel-Name', $tblInvoiceItem->getItemName()
                                        , Panel::PANEL_TYPE_SUCCESS), 3
                                ),
                                new LayoutColumn(
                                    new Panel('Artikel-Beschreibung', $tblInvoiceItem->getItemDescription()
                                        , Panel::PANEL_TYPE_SUCCESS), 6
                                )
                            )),
                        )),
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    Invoice::useService()->changeInvoiceItem(
                                        $Form, $tblInvoiceItem, $InvoiceItem
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
    public function frontendInvoiceItemRemove($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnung');
        $Stage->setDescription('Artikel Entfernen');

        $tblInvoiceItem = Invoice::useService()->getInvoiceItemById($Id);
        $Stage->setContent(Invoice::useService()->removeInvoiceItem($tblInvoiceItem));

        return $Stage;
    }
}
