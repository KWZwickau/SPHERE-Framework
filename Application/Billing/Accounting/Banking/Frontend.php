<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblAccount;
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
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Conversation;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Money;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
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

                $tblPerson = $tblDebtor->getServiceManagementPerson();
                if (!empty( $tblPerson )) {
                    $tblDebtor->FullName = $tblPerson->getFullName();
                } else {
                    $tblDebtor->FullName = 'Person nicht vorhanden';
                }

                $tblDebtor->Edit =
                    (new Standard('', '/Billing/Accounting/Banking/Debtor/View',
                        new Pencil(), array(
                            'Id' => $tblDebtor->getId()
                        ), 'Debitor bearbeiten'))->__toString().
                    (new Standard('', '/Billing/Accounting/Banking/Destroy',
                        new Remove(), array(
                            'Id' => $tblDebtor->getId()
                        ), 'Debitor löschen'))->__toString();

//                $BankName = $tblDebtor->getBankName();
//                $IBAN = $tblDebtor->getIBAN();
//                $BIC = $tblDebtor->getBIC();
//                $Owner = $tblDebtor->getOwner();
//                if (!empty( $BankName ) && !empty( $IBAN ) && !empty( $BIC ) && !empty( $Owner )) {
//                    $tblDebtor->BankInformation = new Success(new Enable().' OK');
//                } else {
//                    $tblDebtor->BankInformation = new Warning(new Disable().' fehlt');
//                }

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
                                    'FullName'           => 'Name',
                                    'ReferenceCommodity' => 'Mandatsreferenzen',
                                    'DebtorCommodity'    => 'Leistungszuordnung',
//                                    'BankInformation'    => 'Bankdaten',
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
        $tblPaymentTypeList = Banking::useService()->getPaymentTypeAll();

        $PersonGroups = Group::useService()->getGroupAllByPerson($tblPerson);
        $Group = array();
        foreach ($PersonGroups as $PersonGroup) {
            $Group[] = $PersonGroup->getName().' ';
        }
        $tblPerson->PersonGroup = implode(',', $Group);

//        $tblPaymentType = Banking::useService()->getPaymentTypeAll();
//        $tblCommodity = Commodity::useService()->getCommodityAll();
//        $tblPerson = Person::useService()->getPersonById($Id);

        //        $tblStudent = Person::useService()->get; //ToDO
        $tblStudent = false; //todo
        //        if ($tblStudent) {
        //            if ($tblStudent->getStudentNumber() === 0) {
        //                $tblStudent->setStudentNumber('Nicht vergeben');
        //            }
        //        }

        $Global = $this->getGlobal();
        $Global->POST['Debtor']['Owner'] = $PersonName;
//        $Global->POST['Debtor']['PaymentType'] = Banking::useService()->getPaymentTypeByName('SEPA-Lastschirft') ;

        if (!isset( $Global->POST['Debtor']['PaymentType'] )) {
            $Global->POST['Debtor']['PaymentType'] = Banking::useService()->getPaymentTypeByName('SEPA-Lastschrift')->getId();
        }
        if (Banking::useService()->getDebtorByServiceManagementPerson($Id) == true) {
            $tblDebtor = Banking::useService()->getDebtorByServiceManagementPerson($Id);
            foreach ($tblDebtor as &$singleDebtor) {
                $tblAccount = Banking::useService()->getActiveAccountByDebtor($singleDebtor);
                if ($tblAccount) {
                    $singleDebtor->Owner = $tblAccount->getOwner();
                    $singleDebtor->BankName = $tblAccount->getBankName();
                    $singleDebtor->IBAN = $tblAccount->getIBAN();
                    $singleDebtor->BIC = $tblAccount->getBIC();
                } else {
                    $singleDebtor->Owner = new Warning(new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Kein aktives Konto');
                    $singleDebtor->BankName = '';
                    $singleDebtor->IBAN = '';
                    $singleDebtor->BIC = '';
                }
            }
        }

        $Global->savePost();

        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Debtor[DebtorNumber]', 'Debitornummer', 'Debitornummer',
                            new BarCode()
                        ), 6),
                    new FormColumn(
                        new SelectBox('Debtor[PaymentType]', 'Zahlungsart', array(TblPaymentType::ATTR_NAME => $tblPaymentTypeList),
                            new Money()
                        ), 6),
                    new FormColumn(
                        new TextField('Debtor[Description]', 'Beschreibung', 'Beschreibung',
                            new Conversation()
                        ), 12),
                ))
            ), new \SPHERE\Common\Frontend\Form\Repository\Title('Debitor')),
        ));
        $Form->appendFormButton(new Primary('Hinzufügen'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    ( empty( $tblStudent ) ) ?
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                new Panel(' Debitor', $PersonName, Panel::PANEL_TYPE_SUCCESS
                                )
                            ), 6),
                            new LayoutColumn(array(
                                new Panel(' Personengruppe(n)', $Group,
                                    Panel::PANEL_TYPE_SUCCESS
                                )
                            ), 6),
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
                            new LayoutColumn(array(
                                new Panel(' Personengruppe(n)', $Group,
                                    Panel::PANEL_TYPE_SUCCESS
                                )
                            ), 4),
                        )) : null,
                )),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            Banking::useService()->createDebtor(
                                $Form, $Debtor, $Id)
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
                                                    'Owner'        => 'Inhaber',
                                                    'BankName'     => 'Name der Bank',
                                                    'IBAN'         => 'IBAN',
                                                    'BIC'          => 'BIC',
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
                        ), 6),
                        new LayoutColumn(array(
                            new Panel('Debitornummer', $tblDebtor->getDebtorNumber(), Panel::PANEL_TYPE_INFO)
                        ), 6),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Beschreibung', $tblDebtor->getDescription(), Panel::PANEL_TYPE_DEFAULT)
                        ), 12),
                    ))
                ))
            ))
            .new Standard('Ändern', '/Billing/Accounting/Banking/Debtor/Change', null, array('Id' => $Id), 'Beschreibung ändern')
            .self::layoutPaymentType($tblDebtor)
            .new Standard('Ändern', '/Billing/Accounting/Banking/Debtor/Payment/View', null, array('Id' => $Id), 'Zahlungsart ändern')
            .self::layoutAccount($tblDebtor, '/Billing/Accounting/Banking/Debtor/View', $Id)
            .new Standard('Anlegen', '/Billing/Accounting/Banking/Account/Create', null, array('Id' => $Id), 'Kontodaten anlegen')
            .self::layoutCommodityDebtor($tblDebtor)
            .new Standard('Bearbeiten', '/Billing/Accounting/Banking/Commodity/Select', null, array('Id' => $Id), 'Leistungen bearbeiten')
//            .self::layoutReference($tblDebtor)
//            .new Standard('Bearbeiten', '/Billing/Accounting/Banking/Debtor/Reference', null, array('Id' => $Id))
        );

        return $Stage;
    }

    /**
     * @param TblDebtor $tblDebtor
     *
     * @return Layout
     */
    public function layoutPaymentType(TblDebtor $tblDebtor)
    {

        $tblPayment = $tblDebtor->getPaymentType();
        return new Layout(
            new LayoutGroup(array(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel($tblPayment->getName(), '', Panel::PANEL_TYPE_PRIMARY)
                        , 3)
                )), new Title('Zahlungsart'))
        );
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
                    new \SPHERE\Common\Frontend\Message\Repository\Warning('Bitte legen sie ein aktives Konto fest (mit '.new Ok().')')));
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
                            $this->layoutReference($tblAccount)
                        ), null, ( $tblAccount->getActive() === false ?
                                new Standard('', '/Billing/Accounting/Banking/Account/Activate', new Ok(),
                                    array('Id'      => $tblDebtor->getId(),
                                          'Account' => $tblAccount->getId(),
                                          'Path'    => $Path,
                                          'IdBack'  => $IdBack), 'Konto aktiv setzen') : null )
                            .new Standard('', '/Billing/Accounting/Banking/Account/Change', new Pencil(),
                                array('Id'        => $tblDebtor->getId(),
                                      'AccountId' => $tblAccount->getId()), 'Konto bearbeiten')
                            .new Standard('', '/Billing/Accounting/Banking/Debtor/Reference', new Listing(),
                                array('DebtorId'  => $IdBack,
                                      'AccountId' => $tblAccount->getId()), 'Referenz bearbeiten')
                            .new Standard('', '/Billing/Accounting/Banking/Account/Destroy', new Remove(),
                                array('Id'      => $tblDebtor->getId(),
                                      'Account' => $tblAccount->getId()), 'Konto löschen')
                        )), ( $tblAccount->getActive() ?
                        Panel::PANEL_TYPE_SUCCESS
                        : Panel::PANEL_TYPE_DEFAULT )), 4
                );
            }
        } else {
            if ($tblDebtor->getPaymentType()->getName() === "SEPA-Lastschrift") {
                $tblAccountList = new LayoutColumn(
                    new \SPHERE\Common\Frontend\Message\Repository\Danger('Für SEPA-Lastschrift werden Kontodaten benötigt'));
            } else {
                $tblAccountList = new LayoutColumn('');
            }
        }
        if (!isset( $Warning ))
            $Warning = null;

        return new Layout(
            new LayoutGroup(array(new LayoutRow($tblAccountList), $Warning), new Title('Kontodaten'))
        );
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|TblReference
     */
    private function layoutReference(TblAccount $tblAccount)
    {

        $tblReferenceList = Banking::useService()->getReferenceActiveByAccount($tblAccount);
        if ($tblReferenceList) {
            $String = null;
            /** @var TblReference $tblReference */
            foreach ($tblReferenceList as $Key => $tblReference) {
                $String .= new Panel($tblReference->getServiceBillingCommodity()->getName(), '',
                    Panel::PANEL_TYPE_SUCCESS);
            }

        } else {
            $String = false;
        }
        return $String;


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

                if ($tblCommodity) {
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
            }
        } else {
            $tblCommodityList = new LayoutColumn('');
        }
        return new Layout(
            new LayoutGroup(new LayoutRow($tblCommodityList), new Title('Leistungen'))
        );
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendDebtorPaymentView($Id)
    {

        $Stage = new Stage('Zahlungsart', 'ändern');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/Debtor/View', new ChevronLeft(),
            array('Id' => $Id)));
        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $tblActivePaymentType = $tblDebtor->getPaymentType();
        $tblPaymentTypeList = Banking::useService()->getPaymentTypeAll();
        foreach ($tblPaymentTypeList as &$tblPaymentType) {
            $tblPaymentType = new LayoutColumn(
                new Panel($tblPaymentType->getName(),
                    '',
                    ( ( $tblActivePaymentType->getId() === $tblPaymentType->getId() ) ?
                        Panel::PANEL_TYPE_SUCCESS :
                        Panel::PANEL_TYPE_DEFAULT ),
                    ( ( $tblActivePaymentType->getId() === $tblPaymentType->getId() ) ?
                        '' :
                        new Standard('Auswählen', '/Billing/Accounting/Banking/Debtor/Payment/Change', new Ok(),
                            array(
                                'Id'          => $tblDebtor->getId(),
                                'PaymentType' => $tblPaymentType->getId(),
                            )) ))
                , 4);
        }

        /** @var LayoutColumn $tblPaymentTypeList */
        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Debitor', $tblDebtor->getServiceManagementPerson()->getFullName(),
                                Panel::PANEL_TYPE_INFO),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel('Debitornummer', $tblDebtor->getDebtorNumber(),
                                Panel::PANEL_TYPE_INFO),

                        ), 6),
                    ))
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        $tblPaymentTypeList
                    )
                )
            )
        );

        return $Stage;
    }

//    /**
//     * @param TblDebtor $tblDebtor
//     *
//     * @return Layout
//     */
//    public function layoutReference(TblDebtor $tblDebtor)
//    {
//
//        $tblReferenceList = Banking::useService()->getReferenceByDebtor($tblDebtor);
//        if (!empty( $tblReferenceList )) {
//            /** @var TblReference $tblReference */
//            foreach ($tblReferenceList as $Key => &$tblReference) {
//                $Reference = $tblReference->getServiceBillingCommodity()->getName();
//
//                $tblReference = new LayoutColumn(array(
//                    new Panel($Reference, array($tblReference->getReference()), Panel::PANEL_TYPE_SUCCESS)
//                ), 3);
//            }
//        } else {
//            $tblReferenceList = new LayoutColumn('');
//        }
//        return new Layout(
//            new LayoutGroup(new LayoutRow($tblReferenceList), new Title('Referenzen'))
//        );
//    }
    /**
     * @param $Id
     * @param $PaymentType
     *
     * @return Stage
     */
    public function frontendDebtorPaymentTypeChange($Id, $PaymentType)
    {

        $Stage = new Stage('Zahlungsart', 'ändern');
        $Stage->setContent(Banking::useService()->changeDebtorPaymentType($Id, $PaymentType));
        return $Stage;
    }

    /**
     * @param $Id
     * @param $Account
     * @param $Path
     * @param $IdBack
     *
     * @return Stage
     */
    public function frontendAccountActivate($Id, $Account, $Path, $IdBack)
    {

        $Stage = new Stage('Aktivierung');
        $Stage->setContent(Banking::useService()->changeActiveAccount($Id, $Account, $Path, $IdBack));
        return $Stage;
    }

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
                        if ($tblCommodity) {
                            $Commodity[] = $tblCommodity->getName();
                        }
                    }
                }
                if (empty( $Commodity )) {
                    $Commodity[] = new Warning('Keine Leistungen erfasst');
                }

//                $Bankinfo = null;
//                if (!empty( $tblDebtor->getBankName() ) &&
//                    !empty( $tblDebtor->getIBAN() ) &&
//                    !empty( $tblDebtor->getBIC() ) &&
//                    !empty( $tblDebtor->getOwner() )
//                ) {
//                    $Bankinfo = 'Bankdaten: '.new Success(new Enable().' OK');
//                } elseif (empty( $tblDebtor->getBankName() ) &&
//                    empty( $tblDebtor->getIBAN() ) &&
//                    empty( $tblDebtor->getBIC() ) &&
//                    empty( $tblDebtor->getOwner() )
//                ) {
//                    $Bankinfo = 'Bankdaten: '.new Danger(new Disable().' fehlt');
//                } else {
//                    $Bankinfo = 'Bankdaten: '.new Warning(new Disable().' unvollständig');
//                }
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Diesen Debitor wirklich löschen?',
                            array(
                                'Debitor: '.$tblDebtor->getServiceManagementPerson()->getFullName(),
                                'DebitorNr: '.$tblDebtor->getDebtorNumber(),
//                                $Bankinfo,
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
                            Banking::useService()->destroyBanking($tblDebtor)
//                                ? new \SPHERE\Common\Frontend\Message\Repository\Success('Der Debitor wurde gelöscht')
//                                .new Redirect('/Billing/Accounting/Banking', 0)
//                                : new \SPHERE\Common\Frontend\Message\Repository\Danger('Der Debitor konnte nicht gelöscht werden')
//                                .new Redirect('/Billing/Accounting/Banking', 10)
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new \SPHERE\Common\Frontend\Message\Repository\Danger('Der Debitor konnte nicht gefunden werden'),
                        new Redirect('/Billing/Accounting/Banking', 10)
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param $Id
     * @param $Account
     *
     * @return Stage
     */
    public function frontendAccountCreate($Id, $Account)
    {

        $Stage = new Stage('Konto', 'anlegen');
        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/Debtor/View', new ChevronLeft(),
            array('Id' => $Id)));

        $Global = $this->getGlobal();
        $Global->POST['Account']['Active'] = true;
        $Global->savePost();

        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new TextField('Account[Owner]', 'Besitzer', 'Besitzer')
                    ), 3),
                    new FormColumn(array(
                        new TextField('Account[IBAN]', 'IBAN', 'IBAN')
                    ), 3),
                    new FormColumn(array(
                        new TextField('Account[BIC]', 'BIC', 'BIC')
                    ), 3),
                    new FormColumn(array(
                        new TextField('Account[CashSign]', 'Kassenzeichen', 'Kassenzeichen')
                    ), 3),
                    new FormColumn(array(
                        new TextField('Account[BankName]', 'Bankname', 'Bankname')
                    ), 3),
                ))
            ))
        ));
        $Form->appendFormButton(new Primary('Hinzufügen'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Debitor', $tblDebtor->getServiceManagementPerson()->getFullName(),
                                Panel::PANEL_TYPE_INFO),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel('Debitornummer', $tblDebtor->getDebtorNumber(),
                                Panel::PANEL_TYPE_INFO),
                        ), 6),
                    ))
                )
            )
            .Banking::useService()->createAccount(
                $Form, $Account, $tblDebtor
            )
        );

        return $Stage;
    }

    /**
     * @param $Id
     * @param $AccountId
     * @param $Account
     *
     * @return Stage
     */
    public function frontendAccountChange($Id, $AccountId, $Account)
    {

        $Stage = new Stage('Konto', 'Bearbeiten');
        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $tblAccount = Banking::useService()->getAccountById($AccountId);
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/Debtor/View', new ChevronLeft(),
            array('Id' => $Id)));

        $Global = $this->getGlobal();
        if (empty( $Global->POST['Account'] )) {
            $Global->POST['Account']['Owner'] = $tblAccount->getOwner();
            $Global->POST['Account']['IBAN'] = $tblAccount->getIBAN();
            $Global->POST['Account']['BIC'] = $tblAccount->getBIC();
            $Global->POST['Account']['CashSign'] = $tblAccount->getCashSign();
            $Global->POST['Account']['BankName'] = $tblAccount->getBankName();
            $Global->POST['Account']['Active'] = $tblAccount->getActive();
            $Global->savePost();
        }

        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new TextField('Account[Owner]', 'Besitzer', 'Besitzer')
                    ), 4),
                    new FormColumn(array(
                        new TextField('Account[IBAN]', 'IBAN', 'IBAN')
                    ), 4),
                    new FormColumn(array(
                        new TextField('Account[BIC]', 'BIC', 'BIC')
                    ), 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new TextField('Account[BankName]', 'Bankname', 'Bankname')
                    ), 6),
                    new FormColumn(array(
                        new TextField('Account[CashSign]', 'Kassenzeichen', 'Kassenzeichen')
                    ), 3),
                ))
            ))
        ));
        $Form->appendFormButton(new Primary('Änderungen speichern'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel('Debitor', $tblDebtor->getServiceManagementPerson()->getFullName(),
                                Panel::PANEL_TYPE_INFO),
                        ), 6),
                        new LayoutColumn(array(
                            new Panel('Debitornummer', $tblDebtor->getDebtorNumber(),
                                Panel::PANEL_TYPE_INFO),

                        ), 6),
                    ))
                )
            )
            .Banking::useService()->changeAccount(
                $Form, $Id, $AccountId, $Account
            )
        );

        return $Stage;
    }

    /**
     * @param            $Id
     * @param            $Account
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendAccountDestroy($Id, $Account, $Confirm = false)
    {

        $Stage = new Stage('Debitor', 'Löschen');
        if ($Account) {
            $tblDebtor = Banking::useService()->getDebtorById($Id);
            $tblAccount = Banking::useService()->getAccountById($Account);
            if (!$Confirm) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Dieses Konto vom Debitor: "'.$tblDebtor->getServiceManagementPerson()->getFullName().
                            '" mit der DebitorNr:"'.$tblDebtor->getDebtorNumber().'" wirklich löschen?',
                            array(
                                'Besitzer: '.$tblAccount->getOwner(),
                                'Bank: '.$tblAccount->getBankName(),
                            ),
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Billing/Accounting/Banking/Account/Destroy', new Ok(),
                                array('Id' => $Id, 'Account' => $Account, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Billing/Accounting/Banking/Debtor/View', new Disable(),
                                array('Id' => $Id)
                            )
                        )
                    ))))
                );
            } else {

                // Destroy Group
                $tblAccount = Banking::useService()->getAccountById($Account);
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Banking::useService()->destroyAccount($tblAccount)
                                ? new \SPHERE\Common\Frontend\Message\Repository\Success('Das Konto wurde gelöscht')
                                .new Redirect('/Billing/Accounting/Banking/Debtor/View', 0, array('Id' => $Id))
                                : new \SPHERE\Common\Frontend\Message\Repository\Danger('Das Konto konnte nicht gelöscht werden')
                                .new Redirect('/Billing/Accounting/Banking/Debtor/View', 10, array('Id' => $Id))
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new \SPHERE\Common\Frontend\Message\Repository\Danger('Das Konto konnte nicht gefunden werden'),
                        new Redirect('/Billing/Accounting/Banking/Debtor/View', 10, array('Id' => $Id))
                    )))
                )))
            );
        }

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

        $tblPerson = Banking::useService()->getDebtorById($Id)->getServiceManagementPerson();
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
                ), new Title('Debitor'))
            ))
//            .$this->layoutAccount($tblDebtor, '/Billing/Accounting/Banking/Commodity/Select', $Id)
            .new Layout(array(
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
     * @param      $Id
     * @param      $CommodityId
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
     * @param $Debtor
     *
     * @return Stage
     */
    public function frontendBankingDebtorChange($Id, $Debtor)
    {

        $Stage = new Stage();
        $Stage->setTitle('Debitor');
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

//        $tblPaymentType = Banking::useService()->getPaymentTypeAll();
//        $PaymentType = Banking::useService()->getPaymentTypeById(Banking::useService()->getDebtorById($Id)->getPaymentType());

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Debtor'] )) {
            $Global->POST['Debtor']['Description'] = $tblDebtor->getDescription();
//            $Global->POST['Debtor']['PaymentType'] = $PaymentType->getId();
//            $Global->POST['Debtor']['Owner'] = $tblDebtor->getOwner();
//            $Global->POST['Debtor']['IBAN'] = $tblDebtor->getIBAN();
//            $Global->POST['Debtor']['BIC'] = $tblDebtor->getBIC();
//            $Global->POST['Debtor']['CashSign'] = $tblDebtor->getCashSign();
//            $Global->POST['Debtor']['BankName'] = $tblDebtor->getBankName();
//            $Global->POST['Debtor']['LeadTimeFirst'] = $tblDebtor->getLeadTimeFirst();
//            $Global->POST['Debtor']['LeadTimeFollow'] = $tblDebtor->getLeadTimeFollow();
            $Global->savePost();
        }

        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Debtor[Description]', 'Beschreibung', 'Beschreibung',
                            new Conversation()
                        ), 12),
                ))
            ), new \SPHERE\Common\Frontend\Form\Repository\Title('Debitor')),
        ));
        $Form->appendFormButton(new Primary('Änderungen speichern'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Panel(' Person', $Name, Panel::PANEL_TYPE_INFO)
                        ), 6),
                        new LayoutColumn(array(
                            new Panel(' Debitornummer', $DebtorNumber, Panel::PANEL_TYPE_INFO)
                        ), 6),
                        new LayoutColumn(array(
                            Banking::useService()->changeDebtor(
                                $Form, $tblDebtor, $Debtor)
                        )),
                    ))
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param $DebtorId
     * @param $AccountId
     * @param $Reference
     *
     * @return Stage
     */
    public function frontendBankingDebtorReference($DebtorId, $AccountId, $Reference)
    {

        $Stage = new Stage();

        $Stage->setTitle('Referenzen');
        $Stage->setDescription('bearbeiten');
        $Stage->setMessage('Die Referenzen sind eine vom Auftraggeber der Zahlung vergebene Kennzeichnung.<br />
                            Hier kann z.B. eine Vertrags- oder Rechnungsnummer eingetragen werden.<br />
                            Referenzen müssen eindeutig sein!');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/Debtor/View', new ChevronLeft(),
            array('Id' => $DebtorId)));
        $tblDebtor = Banking::useService()->getDebtorById($DebtorId);
        $tblAccount = Banking::useService()->getAccountById($AccountId);
        $Person = $tblDebtor->getServiceManagementPerson();
        if (!empty( $Person )) {
            $Name = $Person->getFullName();
            $tblDebtorList = Banking::useService()->getDebtorAllByPerson($Person);
        } else {
            $Name = 'Person nicht vorhanden';
            $tblDebtorList = false;
        }

        $DebtorNumber = $tblDebtor->getDebtorNumber();
        $ReferenceEntityList = Banking::useService()->getReferenceActiveByAccount($tblAccount);

        $DebtorArray = array();
        $DebtorArray[] = $tblDebtor;
        if ($tblDebtorList && $DebtorArray) {
            $tblDebtorList = array_udiff($tblDebtorList, $DebtorArray,
                function (TblDebtor $invoiceA, TblDebtor $invoiceB) {

                    return $invoiceA->getId() - $invoiceB->getId();
                });

//            /** @var TblDebtor $DebtorOne */
//            foreach ($tblDebtorList as $DebtorOne) {
//                $DebtorOne->IBANfrontend = $DebtorOne->getIBAN();
//            }

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
                $tblComparList = Banking::useService()->getDebtorCommodityAllByDebtorAndCommodity(
                    $ReferenceEntity->getServiceTblDebtor(), $ReferenceEntity->getServiceBillingCommodity());

                if ($tblComparList) {
                    $ReferenceEntity->Usage = new Success(new Enable().' In Verwendung');
                } else {
                    $ReferenceEntity->Usage = '';
                }

                $ReferenceEntity->Option =
                    new Standard('', '/Billing/Accounting/Banking/Debtor/Reference/Change',
                        new Pencil(), array(
                            'DebtorId'    => $tblDebtor->getId(),
                            'ReferenceId' => $ReferenceEntity->getId(),
                            'AccountId'   => $tblAccount->getId(),
                        ), 'Datum bearbeiten')
                    .(new Standard('Deaktivieren', '/Billing/Accounting/Banking/Debtor/Reference/Deactivate',
                        new Remove(), array(
                            'ReferenceId' => $ReferenceEntity->getId(),
                            'AccountId'   => $tblAccount->getId(),
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

        $Form = new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('Reference[Reference]', 'Referenz', 'Mandatsreferenz',
                            new BarCode()
                        ), 4),
                    new FormColumn(
                        new DatePicker('Reference[ReferenceDate]', 'Signaturdatum',
                            'Signaturdatum', new Time()
                        ), 4),
                    new FormColumn(
                        new SelectBox('Reference[Commodity]', 'Leistung',
                            array('Name' => $tblCommoditySelectBox), new Time()
                        ), 4),
                )),
            ))
        ));
        $Form->appendFormButton(new Primary('Hinzufügen'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

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
            ))
            .$this->layoutSingleAccount($tblAccount)
            .new Layout(array(
                ( !empty( $tblCommoditySelectBox ) ) ?
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(array(
                                Banking::useService()->createReference(
                                    $Form, $tblDebtor, $tblAccount, $Reference)
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
                                        'ReferenceDate' => 'Signaturdatum',
                                        'Commodity'     => 'Leistung',
                                        'Usage'         => 'Benutzung',
                                        'Option'        => 'Option'
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
     * @param TblAccount $tblAccount
     *
     * @return Layout
     */
    private function layoutSingleAccount(TblAccount $tblAccount)
    {

        $Account = new LayoutColumn(new Panel('Besitzer'.new PullRight($tblAccount->getOwner()), array(
            'BankName'.new PullRight($tblAccount->getBankName()),
            'Iban'.new PullRight($tblAccount->getIBAN()),
            'BIC'.new PullRight($tblAccount->getBIC()),
            'Kassenzeichen'.new PullRight($tblAccount->getCashSign()),

        )), 6);
        return new Layout(new LayoutGroup(new LayoutRow($Account)));
    }

    /**
     * @param $DebtorId
     * @param $ReferenceId
     * @param $AccountId
     * @param $Reference
     *
     * @return Stage
     */
    public function frontendBankingDebtorReferenceChange($DebtorId, $ReferenceId, $AccountId, $Reference)
    {

        $tblReference = Banking::useService()->getReferenceById($ReferenceId);
        $Stage = new Stage('Reference', 'bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/Debtor/Reference',
            new ChevronLeft(), array('DebtorId'  => $DebtorId,
                                     'AccountId' => $AccountId)));

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Reference'] )) {
            $Global->POST['Reference']['Date'] = $tblReference->getReferenceDate();
            $Global->savePost();
        }

        $Form = new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new DatePicker('Reference[Date]', 'Signaturdatum', 'Signaturdatum')
                    )
                )
            )
        );
        $Form->appendFormButton(new Primary('Ändern'));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('Referenznummer: '.$tblReference->getReference(), '', Panel::PANEL_TYPE_INFO)
                            , 6)
                    )
                )
            )
            .Banking::useService()->changeReference(
                $Form, $tblReference, $DebtorId, $AccountId, $Reference
            )
        );

        return $Stage;

    }

    /**
     * @param $ReferenceId
     * @param $AccountId
     *
     * @return Stage
     */
    public function frontendBankingDebtorReferenceDeactivate($ReferenceId, $AccountId)
    {

        $Stage = new Stage();
        $Stage->setTitle('Referenz');
        $Stage->setDescription('deaktiviert');

        $tblReference = Banking::useService()->getReferenceById($ReferenceId);
        $Stage->setContent(Banking::useService()->deactivateBankingReference($tblReference, $AccountId));

        return $Stage;
    }

    /**
     * @param array $Missing
     *
     * @return Layout
     */
    public function layoutMissingAccount(array $Missing)
    {

        /** @var  $Mis */
        foreach ($Missing as &$Mis) {
            $Mis = new LayoutColumn(
                new Panel($Mis, '', Panel::PANEL_TYPE_DANGER)
                , 4);
        }
        return new Layout(
            new LayoutGroup(
                new LayoutRow($Missing
                )
                , new Title('Hauptkonten fehlen bei folgenden Debitoren:'))
        );
    }
}
