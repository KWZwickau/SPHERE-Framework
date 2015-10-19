<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorCommodity;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblPaymentType;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblReference;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\Application\Billing\Inventory\Commodity\Service\Entity\TblCommodity;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\BarCode;
use SPHERE\Common\Frontend\Icon\Repository\Building;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendBanking()
    {

        $Stage = new Stage();
        $Stage->setTitle('Debitoren');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage('Zeigt die verfügbaren Debitoren an');
        $Stage->addButton(
            new Standard('Debitor anlegen', '/Billing/Accounting/Banking/Person', new Plus())
        );

        $tblDebtorAll = Banking::useService()->getDebtorAll();

        if (!empty( $tblDebtorAll )) {
            array_walk($tblDebtorAll, function (TblDebtor &$tblDebtor) {

                $referenceCommodityList = Banking::useService()->getReferenceByDebtor($tblDebtor);
                $referenceCommodity = '';
                if ($referenceCommodityList) {
                    /** @var TblReference[] $referenceCommodityList $ */
                    for ($i = 0; $i < count($referenceCommodityList); $i++) {
                        $tblCommodity = $referenceCommodityList[$i]->getServiceBillingCommodity();
                        if ($tblCommodity) {
                            if ($i === 0) {
                                $referenceCommodity .= $tblCommodity->getName();
                            } else {
                                $referenceCommodity .= ', '.$tblCommodity->getName();
                            }
                        }
                    }
                }
                $tblDebtor->ReferenceCommodity = $referenceCommodity;

                $debtorCommodityList = Banking::useService()->getCommodityDebtorAllByDebtor($tblDebtor);
                $debtorCommodity = '';
                if ($debtorCommodityList) {
                    /** @var TblReference[] $debtorCommodityList $ */
                    for ($i = 0; $i < count($debtorCommodityList); $i++) {
                        $tblCommodity = $debtorCommodityList[$i]->getServiceBillingCommodity();
                        if ($tblCommodity) {
                            if ($i === 0) {
                                $debtorCommodity .= $tblCommodity->getName();
                            } else {
                                $debtorCommodity .= ', '.$tblCommodity->getName();
                            }
                        }
                    }
                }
                $tblDebtor->DebtorCommodity = $debtorCommodity;

                $tblPerson = $tblDebtor->getServiceManagementPerson(); //todo
                if (!empty( $tblPerson )) {
                    $tblDebtor->FirstName = $tblPerson->getFirstName();
                    $tblDebtor->LastName = $tblPerson->getLastName();
                } else {
                    $tblDebtor->FirstName = 'Person nicht vorhanden';
                    $tblDebtor->LastName = 'Person nicht vorhanden';
                }

                $tblDebtor->Edit =
                    (new Standard('', '/Billing/Accounting/Banking/Debtor/View',
                        new Pencil(), array(
                            'Id' => $tblDebtor->getId()
                        )))->__toString().
                    (new Standard('', '/Billing/Accounting/Banking/Destroy',
                        new Remove(), array(
                            'Id' => $tblDebtor->getId()
                        )))->__toString();

                $BankName = $tblDebtor->getBankName();
                $IBAN = $tblDebtor->getIBAN();
                $BIC = $tblDebtor->getBIC();
                $Owner = $tblDebtor->getOwner();
                if (!empty( $BankName ) && !empty( $IBAN ) && !empty( $BIC ) && !empty( $Owner )) {
                    $tblDebtor->BankInformation = new Success(new Enable().' OK');
                } else {
                    $tblDebtor->BankInformation = new Warning(new Disable().' fehlt');
                }

            });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblDebtorAll, null,
                                array(
                                    'DebtorNumber'       => 'Debitoren-Nr',
                                    'FirstName'          => 'Vorname',
                                    'LastName'           => 'Nachname',
                                    'ReferenceCommodity' => 'Mandatsreferenzen',
                                    'DebtorCommodity'    => 'Leistungszuordnung',
                                    'BankInformation'    => 'Bankdaten',
                                    'Edit'               => 'Verwaltung'
                                ))
                        ))
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendBankingPerson()
    {

        $Stage = new Stage();
        $Stage->setTitle('Debitorensuche');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking', new ChevronLeft()));

        //        $tblGroup = Group::useService()->getGroupByMetaTable( 'STUDENT' );
        //        $tblPersonStudent = \SPHERE\Application\People\Search\Group\Group::useService()->getPersonAllByGroup( $tblGroup );
        $tblPerson = Person::useService()->getPersonAll();
        if (!empty( $tblPerson )) {
            foreach ($tblPerson as $Person) {
                $PersonGroups = Group::useService()->getGroupAllByPerson($Person);
                $Group = array();
                foreach ($PersonGroups as $PersonGroup) {
                    $Group[] = $PersonGroup->getName().' ';
                }
                $Person->MiddleName = $Person->getSecondName();
                $Person->Option =
                    ( (new Standard('Debitor erstellen', '/Billing/Accounting/Banking/Person/Select',
                        new Pencil(), array(
                            'Id' => $Person->getId()
                        )))->__toString() );
                $Person->PersonGroup = implode(',', $Group);
            }
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblPerson, null,
                                array(
                                    'FirstName'   => 'Vorname',
                                    'MiddleName'  => 'Zweitname',
                                    'LastName'    => 'Nachname',
                                    'PersonGroup' => 'PersonenGruppe(n)',
                                    'Option'      => 'Debitor hinzufügen'
                                )),
                        ))
                    ))
                ))
            ))
        );
        return $Stage;
    }

    /**
     * @param $Debtor
     * @param $Id
     *
     * @return Stage
     */
    public function frontendBankingPersonSelect($Debtor, $Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Debitoreninformationen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/Person', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($Id);

        $PersonName = $tblPerson->getFullName();
        $PersonGroups = Group::useService()->getGroupAllByPerson($tblPerson);
        $Group = null;
        foreach ($PersonGroups as $PersonGroup) {
            $Group .= $PersonGroup->getName().' ';
        }

        $tblPaymentType = Banking::useService()->getPaymentTypeAll();
        $tblCommodity = Commodity::useService()->getCommodityAll();
        $tblPerson = Person::useService()->getPersonById($Id);

        //        $tblStudent = Person::useService()->get; //ToDO
        $tblStudent = false; //todo
        //        if ($tblStudent) {
        //            if ($tblStudent->getStudentNumber() === 0) {
        //                $tblStudent->setStudentNumber('Nicht vergeben');
        //            }
        //        }

        $Global = $this->getGlobal();
        $Global->POST['Debtor']['Owner'] = $PersonName;

        if (!isset( $Global->POST['Debtor']['PaymentType'] )) {
            $Global->POST['Debtor']['PaymentType'] = Banking::useService()->getPaymentTypeByName('SEPA-Lastschrift')->getId();
        }
        if (Banking::useService()->getDebtorByServiceManagementPerson($Id) == true) {
            $tblDebtor = Banking::useService()->getDebtorByServiceManagementPerson($Id);
        }

        $Global->savePost();
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    ( empty( $tblStudent ) ) ?
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(' Debitor', $PersonName, Panel::PANEL_TYPE_SUCCESS
                                )
                            ), 6),
                            //                            new LayoutColumn(array(       //ToDO
                            //                                new Panel('. Personengruppe', $PersonType/*->getName()*/,
                            //                                    Panel::PANEL_TYPE_SUCCESS
                            //                                )
                            //                            ), 6),
                        )) : null,
                    ( !empty( $tblStudent ) ) ?
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(' Debitor', $PersonName, Panel::PANEL_TYPE_WARNING
                                )
                            ), 4),
                            new LayoutColumn(array(
                                new Panel('. Schülernummer', $tblStudent/*->getStudentNumber()*/,
                                    Panel::PANEL_TYPE_PRIMARY
                                )
                            ), 4),
                            //                            new LayoutColumn(array(       //ToDO
                            //                                new Panel('. Personengruppe', $PersonType/*->getName()*/,
                            //                                    Panel::PANEL_TYPE_WARNING
                            //                                )
                            //                            ), 4),
                        )) : null,
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Banking::useService()->createDebtor(
                                new Form(array(
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new TextField('Debtor[DebtorNumber]', 'Debitornummer', 'Debitornummer',
                                                    new BarCode()
                                                ), 12),
                                            new FormColumn(
                                                new SelectBox('Debtor[PaymentType]', 'Bezahlmethode',
                                                    array(TblPaymentType::ATTR_NAME => $tblPaymentType), new Money()
                                                ), 4),
                                            new FormColumn(
                                                new TextField('Debtor[LeadTimeFirst]', 'Vorlaufzeit in Tagen',
                                                    'Ersteinzug', new Time()
                                                ), 4),
                                            new FormColumn(
                                                new TextField('Debtor[LeadTimeFollow]', 'Vorlaufzeit in Tagen',
                                                    'Folgeeinzug', new Time()
                                                ), 4),
                                            new FormColumn(
                                                new TextField('Debtor[Description]', 'Beschreibung', 'Beschreibung',
                                                    new Conversation()
                                                ), 12),
                                        ))
                                    ), new \SPHERE\Common\Frontend\Form\Repository\Title('Debitor')),
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new TextField('Debtor[Owner]', 'Vorname Nachname', 'Inhaber'
                                                ), 6),
                                            new FormColumn(
                                                new TextField('Debtor[BankName]', 'Name der Bank', 'Name der Bank',
                                                    new Building()
                                                ), 6),
                                            new FormColumn(
                                                new TextField('Debtor[IBAN]', 'XXXX XXXX XXXX XXXX XXXX XX', 'IBAN',
                                                    new BarCode()
                                                ), 4),
                                            new FormColumn(
                                                new TextField('Debtor[BIC]', 'XXXX XX XX XXX', 'BIC', new BarCode()
                                                ), 4),
                                            new FormColumn(
                                                new TextField('Debtor[CashSign]', 'Kassenzeichen', 'Kassenzeichen',
                                                    new Nameplate()
                                                ), 4),
                                        ))
                                    ), new \SPHERE\Common\Frontend\Form\Repository\Title('Bankdaten')),
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new TextField('Debtor[Reference]', 'Referenz', 'Mandatsreferenz',
                                                    new BarCode()
                                                ), 4),
                                            new FormColumn(
                                                new DatePicker('Debtor[ReferenceDate]', 'Datum', 'Erstellungsdatum',
                                                    new Time()
                                                ), 4),
                                            new FormColumn(
                                                new SelectBox('Debtor[Commodity]', 'Leistung',
                                                    array('Name' => $tblCommodity), new Time()
                                                ), 4),
                                        )),
                                    ), new \SPHERE\Common\Frontend\Form\Repository\Title('Mandatsreferenz'))
                                ), new Primary('Hinzufügen')), $Debtor,
                                $Id)
                        ))
                    ))
                )),
                ( !empty( $tblDebtor ) ) ?
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Form(array(
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(array(
                                                new TableData($tblDebtor, null, array(
                                                    'DebtorNumber' => 'Debitorennummer',
                                                    'BankName'     => 'Name der Bank',
                                                    'IBAN'         => 'IBAN',
                                                    'BIC'          => 'BIC',
                                                    'Owner'        => 'Inhaber'
                                                ))
                                            ))
                                        ))
                                    ))
                                ))
                            ), 12)
                        ))
                    ), new Title('Vorhandene Debitorennummer(n)')) : null,
            ))
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendBankingDebtorView($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Debitor');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking', new ChevronLeft()));
        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $tblPerson = $tblDebtor->getServiceManagementPerson();

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Name Debitor', $tblPerson->getFullName(), Panel::PANEL_TYPE_INFO)
                        ), 4),
                        new LayoutColumn(array(
                            new Panel('Debitornummer', $tblDebtor->getDebtorNumber(), Panel::PANEL_TYPE_INFO)
                        ), 4),
                        new LayoutColumn(array(
                            new Panel('Bezahlart', $tblDebtor->getPaymentType()->getName(), Panel::PANEL_TYPE_WARNING)
                        ), 4),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Kontoinhaber', $tblDebtor->getOwner(), Panel::PANEL_TYPE_WARNING)
                        ), 4),
                        new LayoutColumn(array(
                            new Panel('IBAN', $tblDebtor->getIBAN(), Panel::PANEL_TYPE_WARNING)
                        ), 4),
                        new LayoutColumn(array(
                            new Panel('BIC', $tblDebtor->getBIC(), Panel::PANEL_TYPE_WARNING)
                        ), 4),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Bank', $tblDebtor->getBankName(), Panel::PANEL_TYPE_DEFAULT)
                        ), 4),
                        new LayoutColumn(array(
                            new Panel('Bankzeichen', $tblDebtor->getCashSign(), Panel::PANEL_TYPE_DEFAULT)
                        ), 4),
                        new LayoutColumn(array(
                            new Panel('Ersteinzug', $tblDebtor->getLeadTimeFirst(), Panel::PANEL_TYPE_DEFAULT)
                        ), 2),
                        new LayoutColumn(array(
                            new Panel('Folgeeinzug', $tblDebtor->getLeadTimeFollow(), Panel::PANEL_TYPE_DEFAULT)
                        ), 2),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Beschreibung', $tblDebtor->getDescription(), Panel::PANEL_TYPE_DEFAULT)
                        ), 12),
                    ))
                ), new Title('Bankdaten'))
            ))
            .new Standard('Bearbeiten', '/Billing/Accounting/Banking/Debtor/Change', null, array('Id' => $Id))
            .self::layoutCommodityDebtor($tblDebtor)
            .new Standard('Bearbeiten', '/Billing/Accounting/Banking/Commodity/Select', null, array('Id' => $Id))
            .self::layoutReference($tblDebtor)
            .new Standard('Bearbeiten', '/Billing/Accounting/Banking/Debtor/Reference', null, array('Id' => $Id))
        );

        return $Stage;
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return Layout
     */
    public function layoutCommodityDebtor(TblDebtor $tblDebtor)
    {

        $tblCommodityList = Banking::useService()->getCommodityAllByDebtor($tblDebtor);
        if (!empty( $tblCommodityList )) {
            /** @var TblCommodity $tblCommodity */
            foreach ($tblCommodityList as $Key => &$tblCommodity) {

                $tblReference = Banking::useService()->getReferenceByDebtorAndCommodity($tblDebtor, $tblCommodity);

                if ($tblReference) {
                    $tblCommodity = new LayoutColumn(array(
                        new Panel($tblCommodity->getName(), null, Panel::PANEL_TYPE_SUCCESS)
                    ), 3);
                } else {
                    $tblCommodity = new LayoutColumn(array(
                        new Panel($tblCommodity->getName(), null, Panel::PANEL_TYPE_DANGER)
                    ), 3);
                }
            }
        } else {
            $tblCommodityList = new LayoutColumn('');
        }
        return new Layout(
            new LayoutGroup(new LayoutRow($tblCommodityList), new Title('Leistungen'))
        );
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return Layout
     */
    public function layoutReference(TblDebtor $tblDebtor)
    {

        $tblReferenceList = Banking::useService()->getReferenceByDebtor($tblDebtor);
        if (!empty( $tblReferenceList )) {
            /** @var TblReference $tblReference */
            foreach ($tblReferenceList as $Key => &$tblReference) {
                $Reference = $tblReference->getServiceBillingCommodity()->getName();

                $tblReference = new LayoutColumn(array(
                    new Panel($Reference, array($tblReference->getReference()), Panel::PANEL_TYPE_SUCCESS)
                    //                    new TextField($Reference, $tblReference->getReference(), $Reference)
                ), 3);
            }
        } else {
            $tblReferenceList = new LayoutColumn('');
        }
        return new Layout(
            new LayoutGroup(new LayoutRow($tblReferenceList), new Title('Referenzen'))
        );
    }

    //    /**
    //     * @param $Id
    //     *
    //     * @return Stage
    //     */
    //    public function frontendBankingDelete($Id)
    //    {
    //
    //        $Stage = new Stage();
    //        $Stage->setTitle('Debitor');
    //        $Stage->setDescription('Entfernen');
    //
    //        $tblDebtor = Banking::useService()->getDebtorById($Id);
    //        $Stage->setContent(Banking::useService()->destroyBanking($tblDebtor));
    //
    //        return $Stage;
    //    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendBankingDestroy($Id, $Confirm = false)
    {

        $Stage = new Stage('Debitor', 'Löschen');
        if ($Id) {
            $tblDebtor = Banking::useService()->getDebtorById($Id);
            if (!$Confirm) {

                $Commodity = array();
                $tblCommodityAll = Banking::useService()->getCommodityAllByDebtor($tblDebtor);
                if ($tblCommodityAll) {
                    foreach ($tblCommodityAll as $tblCommodity) {
                        $Commodity[] = $tblCommodity->getName();
                    }
                }
                if (empty( $Commodity )) {
                    $Commodity[] = new Warning('Keine Leistungen erfasst');
                }

                $Bankinfo = null;
                if (!empty( $tblDebtor->getBankName() ) &&
                    !empty( $tblDebtor->getIBAN() ) &&
                    !empty( $tblDebtor->getBIC() ) &&
                    !empty( $tblDebtor->getOwner() )
                ) {
                    $Bankinfo = 'Bankdaten: '.new Success(new Enable().' OK');
                } elseif (empty( $tblDebtor->getBankName() ) &&
                    empty( $tblDebtor->getIBAN() ) &&
                    empty( $tblDebtor->getBIC() ) &&
                    empty( $tblDebtor->getOwner() )
                ) {
                    $Bankinfo = 'Bankdaten: '.new Danger(new Disable().' fehlt');
                } else {
                    $Bankinfo = 'Bankdaten: '.new Warning(new Disable().' unvollständig');
                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diesen Debitor wirklich löschen?',
                            array(
                                'Debitor: '.$tblDebtor->getServiceManagementPerson()->getFullName(),
                                'DebitorNr: '.$tblDebtor->getDebtorNumber(),
                                $Bankinfo,
                                new Panel('Eingetragene Leistungen:', array(
                                    ( isset( $Commodity[0] ) ? new Muted($Commodity[0]) : false ),
                                    ( isset( $Commodity[1] ) ? new Muted($Commodity[1]) : false ),
                                    ( isset( $Commodity[2] ) ? new Muted($Commodity[2]) : false ),
                                    ( isset( $Commodity[3] ) ? new Muted($Commodity[3]) : false ),
                                    ( isset( $Commodity[4] ) ? new Muted($Commodity[4]) : false ),
                                ))

                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Billing/Accounting/Banking/Destroy', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Billing/Accounting/Banking', new Disable()
                            )
                        )
                    ))))
                );
            } else {

                // Destroy Group
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Banking::useService()->destroyBanking($tblDebtor)
                                ? new Success('Der Debitor wurde gelöscht')
                                .new Redirect('/Billing/Accounting/Banking', 0)
                                : new Danger('Der Debitor konnte nicht gelöscht werden')
                                .new Redirect('/Billing/Accounting/Banking', 10)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Der Debitor konnte nicht gefunden werden'),
                        new Redirect('/Billing/Accounting/Banking', 10)
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Debtor
     *
     * @return Stage
     */
    public function frontendBankingDebtorChange($Id, $Debtor)
    {

        $Stage = new Stage();
        $Stage->setTitle('Bankdaten');
        $Stage->setDescription('bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/Debtor/View', new ChevronLeft(),
            array('Id' => $Id)));
        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $Person = $tblDebtor->getServiceManagementPerson();
        if (!empty( $Person )) {
            $Name = $Person->getFullName();
        } else {
            $Name = 'Person nicht vorhanden';
        }

        $DebtorNumber = $tblDebtor->getDebtorNumber();

        $tblPaymentType = Banking::useService()->getPaymentTypeAll();
        $PaymentType = Banking::useService()->getPaymentTypeById(Banking::useService()->getDebtorById($Id)->getPaymentType());

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Debtor'] )) {
            $Global->POST['Debtor']['Description'] = $tblDebtor->getDescription();
            $Global->POST['Debtor']['PaymentType'] = $PaymentType->getId();     //todo Selectbox doesn't match
            $Global->POST['Debtor']['Owner'] = $tblDebtor->getOwner();
            $Global->POST['Debtor']['IBAN'] = $tblDebtor->getIBAN();
            $Global->POST['Debtor']['BIC'] = $tblDebtor->getBIC();
            $Global->POST['Debtor']['CashSign'] = $tblDebtor->getCashSign();
            $Global->POST['Debtor']['BankName'] = $tblDebtor->getBankName();
            $Global->POST['Debtor']['LeadTimeFirst'] = $tblDebtor->getLeadTimeFirst();
            $Global->POST['Debtor']['LeadTimeFollow'] = $tblDebtor->getLeadTimeFollow();
            $Global->savePost();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(' Person', $Name, Panel::PANEL_TYPE_SUCCESS)
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(new BarCode().' Debitornummer', $DebtorNumber, Panel::PANEL_TYPE_SUCCESS)
                        ), 6),
                        new LayoutColumn(array(
                            Banking::useService()->changeDebtor(
                                new Form(array(
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new TextField('Debtor[Description]', 'Beschreibung', 'Beschreibung',
                                                    new Conversation()
                                                ), 12),
                                            new FormColumn(
                                                new SelectBox('Debtor[PaymentType]', 'Bezahlmethode',
                                                    array(TblPaymentType::ATTR_NAME => $tblPaymentType),
                                                    new Conversation()
                                                ), 4),
                                            new FormColumn(
                                                new TextField('Debtor[LeadTimeFirst]', 'In Tagen', 'Ersteinzug',
                                                    new Time()
                                                ), 4),
                                            new FormColumn(
                                                new TextField('Debtor[LeadTimeFollow]', 'In Tagen', 'Folgeeinzug',
                                                    new Time()
                                                ), 4),
                                        ))
                                    ), new \SPHERE\Common\Frontend\Form\Repository\Title('Debitor')),
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(
                                                new TextField('Debtor[Owner]', 'Vorname Nachname', 'Inhaber'
                                                ), 6),
                                            new FormColumn(
                                                new TextField('Debtor[BankName]', 'Bank', 'Name der Bank',
                                                    new Building()
                                                ), 6),
                                            new FormColumn(
                                                new TextField('Debtor[IBAN]', 'DEXX XXXX XXXX XXXX XXXX XX', 'IBAN',
                                                    new BarCode(), 'aa99 9999 9999 9999 9999 99?99 9999 9999 99'
                                                ), 4),
                                            new FormColumn(
                                                new TextField('Debtor[BIC]', 'XXXX XX XX XXX', 'BIC', new BarCode()
                                                ), 4),
                                            new FormColumn(
                                                new TextField('Debtor[CashSign]', ' ', 'Kassenzeichen', new Nameplate()
                                                ), 4),
                                        ))
                                    ), new \SPHERE\Common\Frontend\Form\Repository\Title('Bankdaten'))
                                ), new Primary('Änderungen speichern')),
                                $tblDebtor, $Debtor)
                        )),
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendBankingCommoditySelect($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistungen');
        $Stage->setDescription('Hinzufügen');
        $Stage->setMessage('Gibt es mehrere Debitoren für eine Person, kann über die Leistung bestimmt werden, welcher Debitor welche Leistung bezahlen soll.<br />
                            Ist die Vorauswahl nicht getroffen, wird bei unklarem Debitor an entsprechender Stelle gefragt.');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/Debtor/View', new ChevronLeft(),
            array('Id' => $Id)));

        $tblPerson = Banking::useService()->getDebtorById($Id)->getServiceManagementPerson();  //todo
        //        $tblPerson = Person::useService()->getPersonById($IdPerson);
        if (!empty( $tblPerson )) {
            $Person = $tblPerson->getFullName();
        } else {
            $Person = 'Person nicht vorhanden';
        }
        $DebtorNumber = Banking::useService()->getDebtorById($Id)->getDebtorNumber();

        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $tblCommodityAll = Commodity::useService()->getCommodityAll();

        $tblDebtorCommodityList = Banking::useService()->getCommodityDebtorAllByDebtor($tblDebtor);
        $tblCommodityByDebtorList = Banking::useService()->getCommodityAllByDebtor($tblDebtor);

        if (!empty( $tblCommodityByDebtorList )) {
            $tblCommodityAll = array_udiff($tblCommodityAll, $tblCommodityByDebtorList,
                function (TblCommodity $ObjectA, TblCommodity $ObjectB) {

                    return $ObjectA->getId() - $ObjectB->getId();
                }
            );
        }

        if (!empty( $tblDebtorCommodityList )) {
            array_walk($tblDebtorCommodityList, function (TblDebtorCommodity &$tblDebtorCommodity) {

                $tblReference = Banking::useService()->getReferenceByDebtorAndCommodity($tblDebtorCommodity->getTblDebtor(),
                    $tblDebtorCommodity->getServiceBillingCommodity());
                if ($tblReference) {
                    $tblDebtorCommodity->Ready = new Success(new Ok());
                } else {
                    $tblDebtorCommodity->Ready = new Danger(new Remove());
                }

                $tblCommodity = $tblDebtorCommodity->getServiceBillingCommodity();
                $tblDebtorCommodity->Name = $tblCommodity->getName();
                $tblDebtorCommodity->Description = $tblCommodity->getDescription();
                $tblDebtorCommodity->Type = $tblCommodity->getTblCommodityType()->getName();

                $tblDebtorCommodity->Option =
                    (new Standard('Entfernen', '/Billing/Accounting/Banking/Commodity/Remove',
                        new Minus(), array(
                            'Id' => $tblDebtorCommodity->getId()
                        )))->__toString();
            });
        }

        if (!empty( $tblCommodityAll )) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblCommodityAll, function (TblCommodity &$tblCommodity, $Index, TblDebtor $tblDebtor) {

                $tblReference = Banking::useService()->getReferenceByDebtorAndCommodity($tblDebtor, $tblCommodity);

                if ($tblReference) {
                    $tblCommodity->Ready = new Success(new Ok());
                } else {
                    $tblCommodity->Ready = new Danger(new Remove());
                }

                $tblCommodity->Type = $tblCommodity->getTblCommodityType()->getName();
                $tblCommodity->Option =
                    (new Standard('Hinzufügen', '/Billing/Accounting/Banking/Commodity/Add',
                        new Plus(), array(
                            'Id'          => $tblDebtor->getId(),
                            'CommodityId' => $tblCommodity->getId()
                        )))->__toString();
            }, $tblDebtor);
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(' Debitor', $Person, Panel::PANEL_TYPE_SUCCESS
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(new BarCode().' Debitornummer', $DebtorNumber, Panel::PANEL_TYPE_SUCCESS
                            )
                        ), 6)
                    ))
                ), new Title('Debitor')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblDebtorCommodityList, null,
                                array(
                                    'Name'        => 'Name',
                                    'Description' => 'Beschreibung',
                                    'Type'        => 'Leistungsart',
                                    'Ready'       => 'Referenz',
                                    'Option'      => 'Option'
                                ))
                        ))
                    )),
                ), new Title('zugewiesene Leistungen')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblCommodityAll, null,
                                array(
                                    'Name'        => 'Name',
                                    'Description' => 'Beschreibung',
                                    'Type'        => 'Leistungsart',
                                    'Ready'       => 'Referenz',
                                    'Option'      => 'Option'
                                ))
                        ))
                    )),
                ), new Title('mögliche Leistungen'))
            ))
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $CommodityId
     *
     * @return Stage
     */
    public function frontendBankingCommodityAdd($Id, $CommodityId)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistung');
        $Stage->setDescription('Hinzufügen');

        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $tblCommodity = Commodity::useService()->getCommodityById($CommodityId);
        $Stage->setContent(Banking::useService()->addCommodityToDebtor($tblDebtor, $tblCommodity));

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendBankingCommodityRemove($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Leistung');
        $Stage->setDescription('Entfernen');

        $tblDebtorCommodity = Banking::useService()->getDebtorCommodityById($Id);
        $Stage->setContent(Banking::useService()->removeCommodityToDebtor($tblDebtorCommodity));

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Reference
     *
     * @return Stage
     */
    public function frontendBankingDebtorReference($Id, $Reference)
    {

        $Stage = new Stage();

        $Stage->setTitle('Referenzen');
        $Stage->setDescription('bearbeiten');
        $Stage->setMessage('Die Referenzen sind eine vom Auftraggeber der Zahlung vergebene Kennzeichnung.<br />
                            Hier kann z.B. eine Vertrags- oder Rechnungsnummer eingetragen werden.<br />
                            Referenzen müssen eindeutig sein!');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/Debtor/View', new ChevronLeft(),
            array('Id' => $Id)));
        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $Person = $tblDebtor->getServiceManagementPerson();
        if (!empty( $Person )) {
            $Name = $Person->getFullName();
            $tblDebtorList = Banking::useService()->getDebtorAllByPerson($Person);
        } else {
            $Name = 'Person nicht vorhanden';
            $tblDebtorList = false;
        }

        $DebtorNumber = $tblDebtor->getDebtorNumber();
        $ReferenceEntityList = Banking::useService()->getReferenceByDebtor($tblDebtor);

        $DebtorArray = array();
        $DebtorArray[] = $tblDebtor;
        if ($tblDebtorList && $DebtorArray) {
            $tblDebtorList = array_udiff($tblDebtorList, $DebtorArray,
                function (TblDebtor $invoiceA, TblDebtor $invoiceB) {

                    return $invoiceA->getId() - $invoiceB->getId();
                });

            /** @var TblDebtor $DebtorOne */
            foreach ($tblDebtorList as $DebtorOne) {
                $DebtorOne->IBANfrontend = $DebtorOne->getIBAN();
            }

        }

        if ($ReferenceEntityList) {
            /**@var TblReference $ReferenceEntity */
            foreach ($ReferenceEntityList as $ReferenceEntity) {
                $ReferenceReal = Commodity::useService()->getCommodityById($ReferenceEntity->getServiceBillingCommodity());
                if ($ReferenceReal !== false) {
                    $ReferenceEntity->Commodity = $ReferenceReal->getName();
                } else {
                    $ReferenceEntity->Commodity = 'Leistung nicht vorhanden';
                }
                $tblComparList = Banking::useService()->getDebtorCommodityAllByDebtorAndCommodity($ReferenceEntity->getServiceBillingBanking(), $ReferenceEntity->getServiceBillingCommodity());

                if ($tblComparList) {
                    $ReferenceEntity->Usage = new Success(new Enable().' In Verwendung');
                } else {
                    $ReferenceEntity->Usage = '';
                }

                $ReferenceEntity->Option =
                    (new Standard('Deaktivieren', '/Billing/Accounting/Banking/Reference/Deactivate',
                        new Remove(), array(
                            'Id' => $ReferenceEntity->getId()
                        )))->__toString();
            }
        }

        $tblCommoditySelectBox = Commodity::useService()->getCommodityAll();
        $tblReferenceList = Banking::useService()->getReferenceByDebtor($tblDebtor);
        $tblCommodityUsed = array();
        /**@var TblReference $tblReference */
        foreach ($tblReferenceList as $tblReference) {
            $tblCommodityUsedReal = Commodity::useService()->getCommodityById($tblReference->getServiceBillingCommodity());
            if ($tblCommodityUsedReal !== false) {
                $tblCommodityUsed[] = $tblCommodityUsedReal;
            }
        }
        if ($tblCommoditySelectBox && $tblCommodityUsed) {
            $tblCommoditySelectBox = array_udiff($tblCommoditySelectBox, $tblCommodityUsed,
                function (TblCommodity $invoiceA, TblCommodity $invoiceB) {

                    return $invoiceA->getId() - $invoiceB->getId();
                });
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(' Person', $Name, Panel::PANEL_TYPE_SUCCESS)
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(new BarCode().' Debitornummer', $DebtorNumber, Panel::PANEL_TYPE_SUCCESS)
                        ), 6),
                    ))
                )),
                ( !empty( $tblCommoditySelectBox ) ) ?
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                Banking::useService()->createReference(
                                    new Form(array(
                                        new FormGroup(array(
                                            new FormRow(array(
                                                new FormColumn(
                                                    new TextField('Reference[Reference]', 'Referenz', 'Mandatsreferenz',
                                                        new BarCode()
                                                    ), 4),
                                                new FormColumn(
                                                    new DatePicker('Reference[ReferenceDate]', 'Datum',
                                                        'Erstellungsdatum', new Time()
                                                    ), 4),
                                                new FormColumn(
                                                    new SelectBox('Reference[Commodity]', 'Leistung',
                                                        array('Name' => $tblCommoditySelectBox), new Time()
                                                    ), 4),
                                            )),
                                        ))
                                    ), new Primary('Hinzufügen'))
                                    , $tblDebtor, $Reference)
                            ))
                        ))
                    ), new Title('Referenz hinzufügen')) : null,
                ( !empty( $ReferenceEntityList ) ) ?
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new TableData($ReferenceEntityList, null,
                                    array(
                                        'Reference'     => 'Mandatsreferenz',
                                        'ReferenceDate' => 'Datum',
                                        'Commodity'     => 'Leistung',
                                        'Usage'         => 'Benutzung',
                                        'Option'        => 'Deaktivieren'
                                    ))
                            ))
                        ))
                    ), new Title('Mandatsreferenz')) : null,
                ( count($tblDebtorList) >= 1 ) ?
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(

                                new Form(array(
                                    new FormGroup(array(
                                        new FormRow(array(
                                            new FormColumn(array(
                                                new TableData($tblDebtorList, null, array(
                                                    'DebtorNumber' => 'Debitorennummer',
                                                    'BankName'     => 'Name der Bank',
                                                    'IBANfrontend' => 'IBAN',
                                                    'BIC'          => 'BIC',
                                                    'Owner'        => 'Inhaber'
                                                ))
                                            ))
                                        ))
                                    ))
                                ))
                            ), 12)
                        ))
                    ), new Title('Weitere Debitorennummer(n)'))
                    : null,
            ))
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendBankingReferenceDeactivate($Id)
    {

        $Stage = new Stage();
        $Stage->setTitle('Referenz');
        $Stage->setDescription('deaktiviert');

        $tblReference = Banking::useService()->getReferenceById($Id);
        $Stage->setContent(Banking::useService()->deactivateBankingReference($tblReference));

        return $Stage;
    }
}
