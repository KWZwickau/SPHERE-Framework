<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtorSelection;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\Application\Billing\Accounting\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\RadioBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;


/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\Banking
 */
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
//        $Stage->setMessage('Zeigt die verfügbaren Debitoren an');
//        $Stage->addButton(
//            new Standard('Debitor anlegen', '/Billing/Accounting/Banking/Person', new Plus())
//        );
        new Backward();
        $TableContent = array();
        $tblPersonAll = Person::useService()->getPersonAll();
        if ($tblPersonAll) {
            array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$TableContent) {


                $Item['Person'] = $tblPerson->getFullName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $Item['Address'] = new WarningText('Nicht hinterlegt');
                if ($tblAddress) {
                    $Item['Address'] = $tblAddress->getGuiString();
                }
                $tblDebtorList = Banking::useService()->getDebtorByPerson($tblPerson);
                $DebtorArray = array();
                if ($tblDebtorList) {
                    foreach ($tblDebtorList as $tblDebtor) {
                        $DebtorArray[] = $tblDebtor->getDebtorNumber();
                    }
                }
                $Item['DebtorNumber'] = implode(', ', $DebtorArray);

                $Item['Option'] =
                    (new Standard('', '/Billing/Accounting/Banking/Add',
                        new Plus(), array(
                            'Id' => $tblPerson->getId()
                        ), 'Hinzufügen'))->__toString();

                if (!empty( $DebtorArray )) {
                    $Item['Option'] .=
                        (new Standard('', '/Billing/Accounting/Banking/View',
                            new Edit(), array(
                                'Id' => $tblPerson->getId()
                            ), 'Bearbeiten'))->__toString();
                }

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Person'       => 'Person',
                                    'Address'      => 'Adresse',
                                    'DebtorNumber' => 'Debitor-Nummer',
                                    'Option'       => ''
                                ))
                        )
                    ), new Title(new PlusSign().' Hinzufügen / '.new Edit().' Bearbeiten')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendBankingView($Id = null)
    {

        $Stage = new Stage('Personenbezogene', 'Debitornummern');
        $Stage->addButton(new Backward());

        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }
        $tblDebtorList = Banking::useService()->getDebtorByPerson($tblPerson);
        if (!$tblDebtorList) {
            $Stage->setContent(new Warning(
                'Keine Debitornummer mit der Person '.$tblPerson->getFullName().' gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }
        $RowContent = array();
        array_walk($tblDebtorList, function (TblDebtor $tblDebtor) use (&$RowContent) {

            $RowContent[] = new LayoutColumn(
                new Panel($tblDebtor->getDebtorNumber(), null, Panel::PANEL_TYPE_INFO,
                    new Standard('', '/Billing/Accounting/Banking/Change', new Edit(), array('Id' => $tblDebtor->getId())))
                , 3);
        });

        $Stage->setContent(
            $this->layoutPersonPanel($tblPerson)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        $RowContent
                    ), new Title(new Edit().' Debitornummer(n) bearbeiten')
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Debtor
     *
     * @return Stage
     */
    public function frontendAddBanking($Id = null, $Debtor = null)
    {

        $Stage = new Stage('Debitor', 'Anlegen');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking', new ChevronLeft()));
        $Stage->addButton(new Backward());
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Auf die Person konnte nicht zugegriffen werden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }


        $Form = $this->formDebtor()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            $this->layoutPersonPanel($tblPerson)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Banking::useService()->createDebtor(
                                    $Form, $Debtor, $Id
                                ))
                            , 6)
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return Layout
     */
    public function layoutPersonPanel(TblPerson $tblPerson)
    {

        $DebtorArray = array();
        $tblDebtorList = Banking::useService()->getDebtorByPerson($tblPerson);
        if ($tblDebtorList) {
            foreach ($tblDebtorList as $tblDebtor) {
                $DebtorArray[] = $tblDebtor->getDebtorNumber();

            }
        }
        $Content = new \SPHERE\Common\Frontend\Layout\Repository\Listing($DebtorArray);

        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
        $PersonPanel = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Name', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(
                            new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                                new WarningText('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(
                            ( !empty( $Content ) ) ?
                                new Panel('Debitor-Nummer(n) der Person', array($Content)
                                    , Panel::PANEL_TYPE_SUCCESS)
                                : null
                            , 4)
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
            )
        );
        return $PersonPanel;
    }

    /**
     * @param null $Id
     * @param null $Debtor
     *
     * @return Stage|string
     */
    public function frontendChangeBanking($Id = null, $Debtor = null)
    {

        $Stage = new Stage('Debitor', 'Bearbeiten');
        $tblDebtor = $Id === null ? false : Banking::useService()->getDebtorById($Id);
        if (!$tblDebtor) {
            $Stage->setContent(new Warning('Auf den Debitor konnte nicht zugegriffen werden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }
        $tblPerson = $tblDebtor->getServiceTblPerson();
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person konnte nicht aufgerufen werden.'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking/View', new ChevronLeft(), array('Id' => $tblPerson->getId())));
//        $Stage->addButton(new Backward(true));

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Debtor'] )) {
            $Global->POST['Debtor']['DebtorNumber'] = $tblDebtor->getDebtorNumber();
            $Global->savePost();
        }

        $Form = $this->formDebtor()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            $this->layoutPersonPanel($tblPerson)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Banking::useService()->changeDebtor(
                                    $Form, $tblDebtor, $Debtor
                                ))
                            , 6)
                    ), new Title(new Edit().' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @return Form
     */
    public function formDebtor()
    {

        return new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new Panel('Debitor', array(new TextField('Debtor[DebtorNumber]', '', 'Debitor-Nummer')),
                            Panel::PANEL_TYPE_INFO)
                    )
                )
            )
        );
    }

    /**
     * @return Form
     */
    public function formAccount()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Informationen', array(
                            new TextField('Account[Owner]', '', 'Kontoinhaber'),
                            new TextField('Account[BankName]', '', 'Bankname')
                        ), Panel::PANEL_TYPE_INFO)
                        , 5),
                    new FormColumn(
                        new Panel('Zuordnung',
                            array(new TextField('Account[IBAN]', '', 'IBAN'),
                                new TextField('Account[BIC]', '', 'BIC')), Panel::PANEL_TYPE_INFO)
                        , 5),
                    new FormColumn(
                        new Panel('Sonstiges',
                            array(
                                new TextField('Account[CashSign]', '', 'Kassenzeichen')
                            ), Panel::PANEL_TYPE_INFO)
                        , 2)

                ))
            )
        );
    }

    /**
     * @return Stage
     */
    public function frontendBankReference()
    {

        $Stage = new Stage('Mandatsreferenz', 'Übersicht');
        new Backward();
        $TableContentPerson = array();
        $tblPersonAll = Person::useService()->getPersonAll();
        if ($tblPersonAll) {
            array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$TableContentPerson) {

                $Item['Person'] = $tblPerson->getFullName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $Item['Address'] = new WarningText('Nicht hinterlegt');
                if ($tblAddress) {
                    $Item['Address'] = $tblAddress->getGuiString();
                }
                $Item['Option'] =
                    (new Standard('', '/Billing/Accounting/BankReference/Add',
                        new Plus(), array(
                            'Id' => $tblPerson->getId()
                        ), 'Erstellen'))->__toString();
                $Item['BankRef'] = '';
                $tblReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
                if ($tblReferenceList) {
                    $Counting = count($tblReferenceList);
                    if ($Counting >= 1) {
                        $Item['Option'] .= (new Standard('', '/Billing/Accounting/BankReference/View',
                            new Edit(), array(
                                'Id' => $tblPerson->getId()
                            ), 'Bearbeiten'))->__toString();
                    }


                    if ($Counting === 1) {
                        $Item['BankRef'] = new SuccessText('Eine Mandatsreferenz');
                    } elseif ($Counting >= 2) {
                        $Item['BankRef'] = new SuccessText($Counting.' Mandatsreferenzen');
                    }
                }

                array_push($TableContentPerson, $Item);
            });
        }

        $Stage->setContent(new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContentPerson, null,
                                array(
                                    'Person'  => 'Person',
                                    'Address' => 'Adresse',
                                    'BankRef' => 'Kontodaten',
                                    'Option'  => ''
                                ))
                        )
                    ), new Title(new PlusSign().' Hinzufügen / '.new Edit().' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Reference
     *
     * @return Stage|string
     */
    public function frontendAddBankReference($Id = null, $Reference = null)
    {

        $Stage = new Stage('Mandatsreferenz', 'Anlegen');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankReference', new ChevronLeft()));
        $Stage->addButton(new Backward());
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        $ReferenceContent = array();
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden.'));
            return $Stage.new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR);
        }

        $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
        if ($tblBankReferenceList) {
            foreach ($tblBankReferenceList as $tblBankReference) {
                $ReferenceContent[] = $tblBankReference->getReference().new PullRight($tblBankReference->getReferenceDate());
            }
        }

        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
        $PersonPanel = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Name', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(
                            new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                                new WarningText('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(( !empty( $ReferenceContent ) ) ?
                            new Panel('Mandatsreferenz'.new PullRight('Gültig ab:'), $ReferenceContent, Panel::PANEL_TYPE_SUCCESS)
                            : null
                            , 4),
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
            )
        );

        $Form = new Form(array(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(
                            new Panel('Verweis', array(new TextField('Reference[Reference]', '', 'Mandatsreferenz-Nummer'),
                                    new TextField('Reference[CreditorId]', '', 'Gläubiger-Identifikationsnummer'))
                                , Panel::PANEL_TYPE_INFO)
                            , 6),
                        new FormColumn(
                            new Panel('Gültig ab', array(new DatePicker('Reference[ReferenceDate]', '', 'Mandatsreferenz Datum', new Time()))
                                , Panel::PANEL_TYPE_INFO)
                            , 6)
                    )), new \SPHERE\Common\Frontend\Form\Repository\Title(new Edit().' Mandatsreferenz')
                ),
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(
                            new Panel('Informationen', array(
                                new TextField('Reference[Owner]', '', 'Kontoinhaber'),
                                new TextField('Reference[BankName]', '', 'Bankname')
                            ), Panel::PANEL_TYPE_INFO)
                            , 5),
                        new FormColumn(
                            new Panel('Zuordnung',
                                array(new TextField('Reference[IBAN]', '', 'IBAN'),
                                    new TextField('Reference[BIC]', '', 'BIC')), Panel::PANEL_TYPE_INFO)
                            , 5),
                        new FormColumn(
                            new Panel('Sonstiges',
                                array(
                                    new TextField('Reference[CashSign]', '', 'Kassenzeichen')
                                ), Panel::PANEL_TYPE_INFO)
                            , 2)
                    )), new \SPHERE\Common\Frontend\Form\Repository\Title(new PlusSign().' Konto eintragen')
                )
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($PersonPanel
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Banking::useService()->createReference(
                                $Form, $tblPerson, $Reference)
                        ))
                    )
                )
            )
        );
        return $Stage;
    }

    public function frontendBankReferenceView($Id = null)
    {

        $Stage = new Stage('Mandatsreferenz', 'Personenbezogene Übersicht');
        $Stage->addButton(new Backward());
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR);
        }
        $tblReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
        if (!$tblReferenceList) {
            $Stage->setContent(new Warning(
                'Mandatsreferenzen für '.$tblPerson->getFullName().' nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR);
        }
        $TableContent = array();
        array_walk($tblReferenceList, function (TblBankReference $tblBankReference) use (&$TableContent) {

            $Item['Reference'] = $tblBankReference->getReference();
            $Item['CreditorId'] = $tblBankReference->getCreditorId();
            $Item['ReferenceDate'] = $tblBankReference->getReferenceDate();
            $Item['Owner'] = $tblBankReference->getOwner();
            $Item['BankName'] = $tblBankReference->getBankName();
            $Item['IBAN'] = $tblBankReference->getIBANFrontend();
            $Item['BIC'] = $tblBankReference->getBICFrontend();
            $Item['CashSign'] = $tblBankReference->getCashSign();
            $Item['Option'] = (new Standard('', '/Billing/Accounting/BankReference/Change',
                    new Edit(), array(
                        'Id' => $tblBankReference->getId()
                    ), 'Datum bearbeiten'))
                .(new Standard('', '/Billing/Accounting/BankReference/Remove',
                    new Disable(), array(
                        'Id' => $tblBankReference->getId()
                    ), 'Mandatsreferenz entfernen'))->__toString();

            array_push($TableContent, $Item);
        });

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array('Reference'     => 'Mandatsreferenz',
                                      'CreditorId'    => 'Gläubiger-ID',
                                      'ReferenceDate' => 'Gültig ab:',
                                      'Owner'         => 'Besitzer',
                                      'BankName'      => 'Name der Bank',
                                      'IBAN'          => 'IBAN',
                                      'BIC'           => 'BIC',
                                      'CashSign'      => 'Kassenzeichen',
                                      'Option'        => ''))
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Reference
     *
     * @return Stage
     */
    public function frontendChangeBankReference($Id = null, $Reference = null)
    {

        $Stage = new Stage('Mandatsreferenz', 'Bearbeiten');
        $tblBankReference = $Id === null ? false : Banking::useService()->getBankReferenceById($Id);
        if (!$tblBankReference) {
            $Stage->setContent(new Warning('Mandatsreferenz nicht gefunden.'));
            return $Stage.new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR);
        }
        $tblPerson = $tblBankReference->getServiceTblPerson();
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden.'));
            return $Stage.new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR);
        }

        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankReference/View', new ChevronLeft(),
            array('Id' => $tblPerson->getId())));
//        $Stage->addButton(new Backward());

        $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
        if ($tblBankReferenceList) {
            foreach ($tblBankReferenceList as $tblBankReferenceOne) {
                $ReferenceContent[] = $tblBankReferenceOne->getReference().new PullRight($tblBankReferenceOne->getReferenceDate());
            }
        }

        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
        $PersonPanel = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Name', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(
                            new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                                new WarningText('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(( !empty( $ReferenceContent ) ) ?
                            new Panel('Mandatsreferenz'.new PullRight('Gültig ab:'), $ReferenceContent, Panel::PANEL_TYPE_SUCCESS)
                            : null
                            , 4),
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
            )
        );

        $Global = $this->getGlobal();
        if ($Reference === null) {
            $Global->POST['Reference']['Reference'] = $tblBankReference->getReference();
            $Global->POST['Reference']['ReferenceDate'] = $tblBankReference->getReferenceDate();
            $Global->POST['Reference']['Owner'] = $tblBankReference->getOwner();
            $Global->POST['Reference']['BankName'] = $tblBankReference->getBankName();
            $Global->POST['Reference']['IBAN'] = $tblBankReference->getIBAN();
            $Global->POST['Reference']['BIC'] = $tblBankReference->getBIC();
            $Global->POST['Reference']['CashSign'] = $tblBankReference->getCashSign();
            $Global->savePost();
        }

        $Form = new Form(array(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Verweis', array(new TextField('Reference[Reference]', '', 'Mandatsreferenz-Nummer'),
                                new TextField('Reference[CreditorId]', '', 'Gläubiger-Identifikationsnummer'))
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Gültig ab', array(new DatePicker('Reference[ReferenceDate]', '', 'Mandatsreferenz Datum', new Time()))
                            , Panel::PANEL_TYPE_INFO)
                        , 6)
                )), new \SPHERE\Common\Frontend\Form\Repository\Title(new Edit().' Mandatsreferenz')
            ),
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Informationen', array(
                            new TextField('Reference[Owner]', '', 'Kontoinhaber'),
                            new TextField('Reference[BankName]', '', 'Bankname')
                        ), Panel::PANEL_TYPE_INFO)
                        , 5),
                    new FormColumn(
                        new Panel('Zuordnung',
                            array(new TextField('Reference[IBAN]', '', 'IBAN'),
                                new TextField('Reference[BIC]', '', 'BIC')), Panel::PANEL_TYPE_INFO)
                        , 5),
                    new FormColumn(
                        new Panel('Sonstiges',
                            array(
                                new TextField('Reference[CashSign]', '', 'Kassenzeichen')
                            ), Panel::PANEL_TYPE_INFO)
                        , 2)
                )), new \SPHERE\Common\Frontend\Form\Repository\Title(new PlusSign().' Konto eintragen')
            )
        ));
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($PersonPanel
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Banking::useService()->changeReference(
                                $Form, $tblBankReference, $Reference)
                        ), 12)
                    ), new Title(new Edit().' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendRemoveBankReference($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Mandatsreferenz', 'Entfernen');
        $tblBankReference = $Id === null ? false : Banking::useService()->getBankReferenceById($Id);
        if (!$tblBankReference) {
            $Stage->setContent(new Danger('Mandatsreferenz nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR);
        }

        $tblPerson = $tblBankReference->getServiceTblPerson();
        $PersonPanel = '';
        if ($tblPerson) {

            $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
            if ($tblBankReferenceList) {
                foreach ($tblBankReferenceList as $tblBankReferenceOne) {
                    $ReferenceContent[] = $tblBankReferenceOne->getReference().new PullRight($tblBankReferenceOne->getReferenceDate());
                }
            }

            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            $PersonPanel = new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Name', $tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                                , 4),
                            new LayoutColumn(
                                new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                                    new WarningText('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                                , 4),
                            new LayoutColumn(( !empty( $ReferenceContent ) ) ?
                                new Panel('Mandatsreferenzen', $ReferenceContent, Panel::PANEL_TYPE_SUCCESS)
                                : null
                                , 4),
                        )
                    ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
                )
            );
        }

        $Content = array();
        $Content[] = 'Mandatsreferenz: '.$tblBankReference->getReference();
        $Content[] = 'Datum: '.$tblBankReference->getReferenceDate();
        if (!$Confirm) {
            $Stage->addButton(new Backward());
            $Stage->setContent(
                $PersonPanel
                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Mandatsreferenz wirklich entfernen?',
                        $Content,
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Accounting/BankReference/Remove', new Ok(),
                            array('Id' => $Id, 'Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Accounting/BankReference', new Disable())
                    )
                ))))
            );
        } else {

            // Destroy Reference
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Banking::useService()->removeBankReference($tblBankReference)
                            ? new Success('Mandatsreferenz entfernt')
                            .new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_SUCCESS)
                            : new Danger('Mandatsreferenz konnte nicht entfernt werden')
                            .new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR)
                        )
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendPaySelection($Id = null, $Data = null)
    {

        $Stage = new Stage('Zuordnung', 'Bezahler');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new WarningText('Warenkorb nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        // Abbruch beim löschen der Zuordnungen
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerification) {
            $Stage->setContent(new Warning('Keine Daten zum fakturieren vorhanden.'));
            return $Stage.new Redirect('Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }

//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket/Verification', new ChevronLeft()
//            , array('Id' => $tblBasket->getId())));
        $Stage->addButton(new Backward());
        $Global = $this->getGlobal();

        $TableContent = array();

        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerificationList) {
            array_walk($tblBasketVerificationList, function (TblBasketVerification $tblBasketVerification) use (&$TableContent, &$Global, &$Data) {

                $tblPerson = $tblBasketVerification->getServiceTblPerson();
                $tblItem = $tblBasketVerification->getServiceTblItem();

                if (!Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem)) {
                    $Item['Person'] = $tblBasketVerification->getServiceTblPerson()->getFullName();
                    $Item['SiblingRank'] = '';
                    $Item['SchoolType'] = '';
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    if ($tblStudent) {
                        if (( $tblBilling = $tblStudent->getTblStudentBilling() )) {
                            if (( $tblSiblingRank = $tblBilling->getServiceTblSiblingRank() )) {
                                $Item['SiblingRank'] = $tblSiblingRank->getName();
                            }
                        }

                        $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                        if ($tblTransferType) {
                            $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                                $tblTransferType);
                            if ($tblStudentTransfer) {
                                $tblType = $tblStudentTransfer->getServiceTblType();
                                if ($tblType) {
                                    $Item['SchoolType'] = $tblType->getName();
                                }
                            }
                        }
                    }

                    $PaymentPerson = array();
                    $tblRelationShipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblRelationShipList) {
                        array_walk($tblRelationShipList, function (TblToPerson $tblRelationShip) use (&$PaymentPerson) {
                            /** filter Type of Relationship that is unable to pay */
                            if ($tblRelationShip->getTblType()->getName() !== 'Arzt' &&
                                $tblRelationShip->getTblType()->getName() !== 'Geschwisterkind'
                            ) {
                                $tblPerson = $tblRelationShip->getServiceTblPersonFrom();
                                if ($tblPerson) {
                                    $tblBankReference = Banking::useService()->getBankReferenceByPerson($tblPerson);
                                    if (!empty( $tblBankReference )) {
                                        $PaymentPerson[] = $tblPerson;
                                    }
                                }
                            }
                        });
                    }
                    $tblBankReference = Banking::useService()->getBankReferenceByPerson($tblPerson);
                    if (!empty( $tblBankReference )) {
                        $PaymentPerson[] = $tblPerson;
                    }

                    $Item['Item'] = $tblItem->getName();
                    $Item['Value'] = $tblBasketVerification->getSummaryPrice();
                    $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                    if (!empty( $PaymentPerson )) {
                        $Item['SelectPayers'] = new SelectBox('Data['.$tblBasketVerification->getId().'][PersonPayers]', '', array(
                            '{{ FullName }}' => $PaymentPerson));
                    } else {
                        $Item['SelectPayers'] = new WarningText('Bezahler anlegen!');
                    }

                    if (!isset( $Data )) {
                        $Global->POST['Data'][$tblBasketVerification->getId()]['Payment'] = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift')->getId();
                    }
                    $tblPaymentType = Balance::useService()->getPaymentTypeAll();
                    $Item['SelectPayType'] = new SelectBox('Data['.$tblBasketVerification->getId().'][Payment]', '', array(
                        '{{ Name }}'
                        => $tblPaymentType
                    ));
                    if ($Data !== null) {
                        $Data[$tblBasketVerification->getId()]['Person'] = $tblPerson->getId();
                        $Data[$tblBasketVerification->getId()]['Item'] = $tblBasketVerification->getServiceTblItem()->getId();
                    }
                    array_push($TableContent, $Item);
                }

            });
        }

        $Global->savePost();

        $Form = new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new TableData(
                            $TableContent, null, array(
                            'Person'        => 'Person',
                            'SiblingRank'   => 'Geschwister',
                            'SchoolType'    => 'Schulart',
                            'Item'          => 'Artikel',
                            'Value'         => 'Gesamtpreis',
                            'ItemType'      => 'Typ',
                            'SelectPayers'  => 'Bezahler',
                            'SelectPayType' => 'Typ'
                        ), false) // array("bPaginate" => false)
                    )
                )
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty( $TableContent ) ?
                                Banking::useService()->createDebtorSelection(
                                    $Form, $tblBasket, $Data
                                ) : new Success('Warenbezogene Bezahler sind bekannt.')
                                .new Redirect('/Billing/Accounting/Pay/Choose', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId())) )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendPayChoose($Id = null, $Data = null)
    {

        $Stage = new Stage('Zuordnung', 'Bezahler');
        $tblBasket = $Id === null ? false : Basket::useService()->getBasketById($Id);
        if (!$tblBasket) {
            $Stage->setContent(new WarningText('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        // Abbruch beim löschen der Zuordnungen
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerification) {
            $Stage->setContent(new Warning('Keine Daten zum fakturieren vorhanden.'));
            return $Stage.new Redirect('Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }

//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket/Verification', new ChevronLeft()
//            , array('Id' => $tblBasket->getId())));
        $Stage->addButton(new Backward(true));

        $TableContent = array();
        $tblDebtorSelectionList = array();
        $tblBasketVerificationList = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if ($tblBasketVerificationList) {
            foreach ($tblBasketVerificationList as $tblBasketVerification) {
                $tblPerson = $tblBasketVerification->getServiceTblPerson();
                $tblItem = $tblBasketVerification->getServiceTblItem();
                $tblDebtorSelectionList[] = Banking::useService()->getDebtorSelectionByPersonAndItem($tblPerson, $tblItem);
            }
        }

        if (!empty( $tblDebtorSelectionList )) {
            array_walk($tblDebtorSelectionList, function (TblDebtorSelection $tblDebtorSelection) use (&$TableContent, &$Global, &$Data) {

                $tblPaymentType = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift');
                if ($tblPaymentType->getId() === $tblDebtorSelection->getServiceTblPaymentType()->getId()) {
                    if (Banking::useService()->checkDebtorSelectionDebtor($tblDebtorSelection)) {   //Prüfung auf vorhandene Zuweisungen
                        $tblPerson = $tblDebtorSelection->getServiceTblPerson();
                        $tblPersonPayers = $tblDebtorSelection->getServiceTblPersonPayers();
                        $tblItem = $tblDebtorSelection->getServiceTblInventoryItem();
                        $Item['Person'] = $tblPerson->getFullName();
                        $Item['PersonPayers'] = $tblPersonPayers->getFullName();
                        $Item['Item'] = $tblItem->getName();

                        $Payment = array();
                        $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPersonPayers);
                        if ($tblBankReferenceList) {
                            foreach ($tblBankReferenceList as $tblBankReference) {
                                $Payment[] = new RadioBox('Data['.$tblDebtorSelection->getId().'][RadioBox]',
                                    'Mandatsreferenz: '.$tblBankReference->getReference().'<br/>'.
                                    new Muted(' Gültig ab: ').$tblBankReference->getReferenceDate()
                                    .new Label(( $tblBankReference->getOwner() !== '' ? new Muted('<br/>Besitzer: ')
                                            .new SuccessText($tblBankReference->getOwner()) : null )
                                        .( $tblBankReference->getBankName() !== '' ? new Muted('<br/>Bank: ')
                                            .new SuccessText($tblBankReference->getBankName()) : null )
                                        .( $tblBankReference->getIBAN() !== '' ? new Muted('<br/>IBAN: ')
                                            .new SuccessText($tblBankReference->getIBAN()) : null ), Label::LABEL_TYPE_NORMAL),
                                    $tblBankReference->getId());
                            }
                        }
                        $Debtor = array();
                        if (Banking::useService()->getDebtorByPerson($tblPerson)) {
                            $DebtorList = Banking::useService()->getDebtorByPerson($tblPerson);
                            $Debtor = array_merge($Debtor, $DebtorList);
                        }
                        if (Banking::useService()->getDebtorByPerson($tblPersonPayers)) {
                            $DebtorList = Banking::useService()->getDebtorByPerson($tblPersonPayers);
                            $Debtor = array_merge($Debtor, $DebtorList);
                        }

                        if (!empty( $Debtor )) {
                            $Item['SelectDebtor'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Debtor]', '',
                                array(
                                    '{{ DebtorNumber }} - {{ ServiceTblPerson.FullName }}' => $Debtor));
                        } else {
                            $Item['SelectDebtor'] = new \SPHERE\Common\Frontend\Text\Repository\Danger('Debitor benötigt!');
                        }
                        if (!empty( $Payment )) {
                            $Item['SelectBox'] = new Panel('Zahlung:', $Payment);
                        } else {
                            $Item['SelectBox'] = new WarningText('keine Kontoinformationen');
                        }

                        if ($Data !== null) {
                            $Data[$tblDebtorSelection->getId()]['Person'] = $tblPerson->getId();
                            $Data[$tblDebtorSelection->getId()]['Item'] = $tblItem->getId();
                        }
                        array_push($TableContent, $Item);
                    }
                }
            });
        }

        $Form = new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new TableData(
                            $TableContent, null, array(
                            'Person'       => 'Person',
                            'PersonPayers' => 'Bezahler',
                            'Item'         => 'Artikel',
                            'SelectDebtor' => 'Debitor',
                            'SelectBox'    => 'Zahlungsinformation',
                        ), array("bPaginate" => false))
                    )
                )
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty( $TableContent ) ?
                                Banking::useService()->updateDebtorSelection(
                                    $Form, $tblBasket, $Data)
                                : new Success('Debitoren der Bezahler sind bekannt.')
                                .new Redirect('/Billing/Accounting/Basket/Verification', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblBasket->getId())) )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendDebtorSelection()
    {

        $Stage = new Stage('Bezahler', 'Übersicht');
        $tblDebtorSelectionAll = Banking::useService()->getDebtorSelectionAll();
        $PersonIdList = array();
        $tblPersonList = array();
        $TableContent = array();
        new Backward();

        if ($tblDebtorSelectionAll) {
            foreach ($tblDebtorSelectionAll as $tblDebtorSelection) {
                $PersonIdList[] = $tblDebtorSelection->getServiceTblPerson()->getId();
            }
            $PersonIdList = array_unique($PersonIdList);
        }
        if (!empty( $PersonIdList )) {
            foreach ($PersonIdList as $PersonId) {
                $tblPersonList[] = Person::useService()->getPersonById($PersonId);
            }
        }
        if (!empty( $tblPersonList )) {
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent) {

                $Item['Name'] = $tblPerson->getLastFirstName().' '.new Muted(new Small('('.$tblPerson->getSalutation().')'));
                $Item['ItemPayer'] = '';
                $Item['Status'] = 'test';
                $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($tblPerson);
                if (!empty( $tblDebtorSelectionList )) {
                    $ItemPayer = array();
                    $Status = array();
                    foreach ($tblDebtorSelectionList as $tblDebtorSelection) {
                        $ItemPayer[] = $tblDebtorSelection->getServiceTblInventoryItem()->getName()
                            .' - '.$tblDebtorSelection->getServiceTblPersonPayers()->getLastFirstName();
                        if ($tblDebtorSelection->getTblDebtor() === false && $tblDebtorSelection->getTblBankReference() === false) {
                            if ($tblDebtorSelection->getServiceTblPaymentType()->getName() === 'Bar') {
                                $Status[] = new SuccessText(new Check().' Bar');
                            } elseif ($tblDebtorSelection->getServiceTblPaymentType()->getName() === 'SEPA-Überweisung') {
                                $Status[] = new SuccessText(new Check().' SEPA-Überweisung');
                            } else {
                                $Status[] = new WarningText(new Unchecked().' Offen');
                            }
                        } else {
                            $Status[] = new SuccessText(new Check().' OK');
                        }

                    }
                    $Item['ItemPayer'] = new Listing($ItemPayer);
                    $Item['Status'] = new Listing($Status);
                }
                $Item['Option'] = new Standard('', '/Billing/Accounting/DebtorSelection/PaySelection', new Edit(),
                        array('Id' => $tblPerson->getId()), 'Bearbeiten')
                    .new Standard('', '/Billing/Accounting/DebtorSelection/Person/Destroy', new Remove(),
                        array('Id' => $tblPerson->getId()), 'Zuweisungen entfernen');

                array_push($TableContent, $Item);
            });
        }


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array('Name'      => 'Name',
                                      'ItemPayer' => 'Item - Bezahler',
                                      'Status'    => 'Status',
                                      'Option'    => '',
                                ))
                        )
                    )
                )
            )
        );
        return $Stage;

    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendDebtorPaySelection($Id = null, $Data = null)
    {

        $Stage = new Stage('Zuordnung', 'Bezahler');
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new WarningText('Person nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/DebtorSelection', new ChevronLeft()));
        $Stage->addButton(new Backward());
        $Global = $this->getGlobal();
        $TableContent = array();
        $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($tblPerson);

        if ($tblDebtorSelectionList) {
            array_walk($tblDebtorSelectionList, function (TblDebtorSelection $tblDebtorSelection) use (&$TableContent, &$Global, &$Data) {

                $tblPerson = $tblDebtorSelection->getServiceTblPerson();
                $tblItem = $tblDebtorSelection->getServiceTblInventoryItem();

                $Item['Person'] = $tblDebtorSelection->getServiceTblPerson()->getFullName();
                $Item['SiblingRank'] = '';
                $Item['SchoolType'] = '';
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    if (( $tblBilling = $tblStudent->getTblStudentBilling() )) {
                        if (( $tblSiblingRank = $tblBilling->getServiceTblSiblingRank() )) {
                            $Item['SiblingRank'] = $tblSiblingRank->getName();
                        }
                    }

                    $tblTransferType = Student::useService()->getStudentTransferTypeByIdentifier('PROCESS');
                    if ($tblTransferType) {
                        $tblStudentTransfer = Student::useService()->getStudentTransferByType($tblStudent,
                            $tblTransferType);
                        if ($tblStudentTransfer) {
                            $tblType = $tblStudentTransfer->getServiceTblType();
                            if ($tblType) {
                                $Item['SchoolType'] = $tblType->getName();
                            }
                        }
                    }
                }

                $PaymentPerson = array();
                $tblRelationShipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($tblRelationShipList) {
                    array_walk($tblRelationShipList, function (TblToPerson $tblRelationShip) use (&$PaymentPerson) {

                        $tblPerson = $tblRelationShip->getServiceTblPersonFrom();
                        if ($tblPerson) {
                            $tblBankReference = Banking::useService()->getBankReferenceByPerson($tblPerson);
                            if (!empty( $tblBankReference )) {
                                $PaymentPerson[] = $tblPerson;
                            }
                        }
                    });
                }
                $tblDebtor = Banking::useService()->getDebtorByPerson($tblPerson);
                $tblBankReference = Banking::useService()->getBankReferenceByPerson($tblPerson);
                if (!empty( $tblDebtor ) || !empty( $tblBankReference )) {
                    $PaymentPerson[] = $tblPerson;
                }

                $Item['Item'] = $tblItem->getName();
                $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                if (!empty( $PaymentPerson )) {
                    $Item['SelectPayers'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][PersonPayers]', '', array(
                        '{{ FullName }}'
                        => $PaymentPerson
                    ));
                } else {
                    $Item['SelectPayers'] = new WarningText('Bezahler anlegen!');
                }

                if (!isset( $Data )) {
                    $Global->POST['Data'][$tblDebtorSelection->getId()]['Payment'] = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift')->getId();
                }
                $tblPaymentType = Balance::useService()->getPaymentTypeAll();
                $Item['SelectPayType'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Payment]', '', array(
                    '{{ Name }}'
                    => $tblPaymentType
                ));
                if ($Data !== null) {
                    $Data[$tblDebtorSelection->getId()]['Person'] = $tblPerson->getId();
                    $Data[$tblDebtorSelection->getId()]['Item'] = $tblDebtorSelection->getServiceTblInventoryItem()->getId();
                }

                $Item['Option'] = new Standard('', '/Billing/Accounting/DebtorSelection/Destroy', new Remove(),
                    array('Id'          => $tblPerson->getId(),
                          'SelectionId' => $tblDebtorSelection->getId())
                    , 'Zuweisung entfernen');

                $Global->POST['Data'][$tblDebtorSelection->getId()]['PersonPayers'] = $tblDebtorSelection->getServiceTblPersonPayers()->getId();
                $Global->POST['Data'][$tblDebtorSelection->getId()]['Payment'] = $tblDebtorSelection->getServiceTblPaymentType()->getId();

                array_push($TableContent, $Item);
            });
        }

        if (null === $Data) {
            $Global->savePost();
        }

        $Form = new Form(
            new FormGroup(
                new FormRow(
                    new FormColumn(
                        new TableData(
                            $TableContent, null, array(
                            'Person'        => 'Person',
                            'SiblingRank'   => 'Geschwister',
                            'SchoolType'    => 'Schulart',
                            'Item'          => 'Artikel',
                            'ItemType'      => 'Typ',
                            'SelectPayers'  => 'Bezahler',
                            'SelectPayType' => 'Typ',
                            'Option'        => ''
                        ), false) // array("bPaginate" => false)
                    )
                )
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            Banking::useService()->changeDebtorSelectionPayer(
                                $Form, $tblPerson, $Data
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Data
     *
     * @return Stage|string
     */
    public function frontendDebtorPayChoose($Id = null, $Data = null)
    {

        $Stage = new Stage('Zuordnung', 'Bezahler');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket/Verification', new ChevronLeft()
//            , array('Id' => $tblBasket->getId())));
        $Stage->addButton(new Backward());
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new WarningText('Person nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }

        $Global = $this->getGlobal();

        $TableContent = array();
        $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($tblPerson);

        if (!empty( $tblDebtorSelectionList )) {
            array_walk($tblDebtorSelectionList, function (TblDebtorSelection $tblDebtorSelection) use (&$TableContent, &$Global, &$Data) {

                $tblPaymentType = Balance::useService()->getPaymentTypeByName('SEPA-Lastschrift');
                if ($tblPaymentType->getId() === $tblDebtorSelection->getServiceTblPaymentType()->getId()) {
//                    if (Banking::useService()->checkDebtorSelectionDebtor($tblDebtorSelection)) { //Prüfung auf vorhandene Zuweisungen
                    $tblPerson = $tblDebtorSelection->getServiceTblPerson();
                    $tblPersonPayers = $tblDebtorSelection->getServiceTblPersonPayers();
                    $tblItem = $tblDebtorSelection->getServiceTblInventoryItem();
                    $Item['Person'] = $tblPerson->getFullName();
                    $Item['PersonPayers'] = $tblPersonPayers->getFullName();
                    $Item['Item'] = $tblItem->getName();

                    $Payment = array();
                    $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPersonPayers);
                    if ($tblBankReferenceList) {
                        foreach ($tblBankReferenceList as $tblBankReference) {
                            $Payment[] = new RadioBox('Data['.$tblDebtorSelection->getId().'][RadioBox]',
                                'Mandatsreferenz: '.$tblBankReference->getReference().'<br/>'.
                                new Muted(' Gültig ab: ').$tblBankReference->getReferenceDate()
                                .new Label(( $tblBankReference->getOwner() !== '' ? new Muted('<br/>Besitzer: ')
                                        .new SuccessText($tblBankReference->getOwner()) : null )
                                    .( $tblBankReference->getBankName() !== '' ? new Muted('<br/>Bank: ')
                                        .new SuccessText($tblBankReference->getBankName()) : null )
                                    .( $tblBankReference->getIBAN() !== '' ? new Muted('<br/>IBAN: ')
                                        .new SuccessText($tblBankReference->getIBAN()) : null ), Label::LABEL_TYPE_NORMAL),
                                $tblBankReference->getId());
                        }
                    }
                    $Debtor = array();
                    if (Banking::useService()->getDebtorByPerson($tblPerson)) {
                        $DebtorList = Banking::useService()->getDebtorByPerson($tblPerson);
                        $Debtor = array_merge($Debtor, $DebtorList);
                    }
                    if (Banking::useService()->getDebtorByPerson($tblPersonPayers)) {
                        $DebtorList = Banking::useService()->getDebtorByPerson($tblPersonPayers);
                        $Debtor = array_merge($Debtor, $DebtorList);
                    }

                    if (!empty( $Debtor )) {
                        $Item['SelectDebtor'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Debtor]', '',
                            array(
                                '{{ DebtorNumber }} - {{ ServiceTblPerson.FullName }}'
//                    .'{% if( ServiceBillingDebtor.Description is not empty) %} - {{ ServiceBillingDebtor.Description }}{% endif %}'
                                => $Debtor)
                        );
                    } else {
                        $Item['SelectDebtor'] = new \SPHERE\Common\Frontend\Text\Repository\Danger('Debitor benötigt!');
                    }
                    if (!empty( $Payment )) {
//                        foreach($Payment as $Pay)
//                        $Item['SelectBox'] = $Pay;
                        $Item['RadioBox'] = new Panel('Zahlung:', $Payment);
                    } else {
                        $Item['RadioBox'] = new WarningText('keine Kontoinformationen');
                    }

                    if (( $tblRef = $tblDebtorSelection->getTblBankReference() )) {         //ToDO RadioBox füllt sich nicht
                        $Global->POST['Data'][$tblDebtorSelection->getId()]['RadioBox'] = $tblRef->getId();
                    }
                    if (( $tblDeb = $tblDebtorSelection->getTblDebtor() )) {
                        $Global->POST['Data'][$tblDebtorSelection->getId()]['Debtor'] = $tblDeb->getId();
                    }

                    if ($Data !== null) {
                        $Data[$tblDebtorSelection->getId()]['Person'] = $tblPerson->getId();
                        $Data[$tblDebtorSelection->getId()]['Item'] = $tblItem->getId();
                    }
                    array_push($TableContent, $Item);
                }
//                }
            });
        }


//        if($Data === null){
        $Global->savePost();
//        }

        $Form = new Form(
            new FormGroup(array(
                new FormRow(
                    new FormColumn(
                        new TableData(
                            $TableContent, null, array(
                            'Person'       => 'Person',
                            'PersonPayers' => 'Bezahler',
                            'Item'         => 'Artikel',
                            'SelectDebtor' => 'Debitor',
                            'RadioBox'     => 'Zahlungsinformation',
                        ), array("bPaginate" => false))
                    )
                ),
            ))
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()));
        $Form->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty( $TableContent ) ?
                                Banking::useService()->changeDebtorSelectionInfo(
                                    $Form, $Data)
                                : new Success('Debitoren der Bezahler sind bekannt.')
                                .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_SUCCESS)
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $SelectionId
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyDebtorSelection($Id = null, $SelectionId = null, $Confirm = false)
    {

        $Stage = new Stage('Zahlungseinstellungen', 'Entfernen');
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }
        $tblDebtorSelection = Banking::useService()->getDebtorSelectionById($SelectionId);
        if (!$tblDebtorSelection) {
            $Stage->setContent(new Warning('Zuweisung nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }
        $PersonPanel = new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Information', 'Automatisierung für '.$tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                    )
                )
            )
        );

        $Content = array();
        if ($tblDebtorSelection->getServiceTblInventoryItem()) {
            $Content[] = 'Artikel: '.$tblDebtorSelection->getServiceTblInventoryItem()->getName();
        }
        if ($tblDebtorSelection->getServiceTblPersonPayers()) {
            $Content[] = 'Bezahler: '.$tblDebtorSelection->getServiceTblPersonPayers()->getFullName();
        }
        if (!$Confirm) {
            $Stage->addButton(new Backward());
            $Stage->setContent(
                $PersonPanel
                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Zuweisung wirklich entfernen?',
                        $Content,
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Accounting/DebtorSelection/Destroy', new Ok(),
                            array('Id'          => $tblPerson->getId(),
                                  'SelectionId' => $tblDebtorSelection->getId(),
                                  'Confirm'     => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Accounting/DebtorSelection/PaySelection', new Disable(),
                            array('Id' => $tblPerson->getId()))
                    )
                ))))
            );
        } else {

            // Destroy Reference
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Banking::useService()->destroyDebtorSelection($tblDebtorSelection)
                            ? new Success('Zuweisung entfernt')
                            .new Redirect('/Billing/Accounting/DebtorSelection/PaySelection', Redirect::TIMEOUT_SUCCESS,
                                array('Id' => $tblPerson->getId()))
                            : new Danger('Zuweisung konnte nicht entfernt werden')
                            .new Redirect('/Billing/Accounting/DebtorSelection/PaySelection', Redirect::TIMEOUT_ERROR,
                                array('Id' => $tblPerson->getId()))
                        )
                    )))
                )))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     * @param bool $Confirm
     *
     * @return Stage|string
     */
    public function frontendDestroyDebtorSelectionByPerson($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Zahlungseinstellungen', 'Entfernen');
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new Warning('Person nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR);
        }
        $PersonPanel = new Layout(
            new LayoutGroup(
                new LayoutRow(
                    new LayoutColumn(
                        new Panel('Information', 'Automatisierung für '.$tblPerson->getFullName(), Panel::PANEL_TYPE_SUCCESS)
                    )
                )
            )
        );

        $tblDebtorSelectionList = Banking::useService()->getDebtorSelectionByPerson($tblPerson);
        $Content = array();
        if ($tblDebtorSelectionList) {
            foreach ($tblDebtorSelectionList as $tblDebtorSelection) {
                if ($tblDebtorSelection->getServiceTblPersonPayers() && $tblDebtorSelection->getServiceTblInventoryItem()) {
                    $Content[] = $tblDebtorSelection->getServiceTblPersonPayers()->getFullName().' - '
                        .$tblDebtorSelection->getServiceTblInventoryItem()->getName();
                } elseif ($tblDebtorSelection->getServiceTblPersonPayers()) {
                    $Content[] = $tblDebtorSelection->getServiceTblPersonPayers();
                } elseif ($tblDebtorSelection->getServiceTblInventoryItem()) {
                    $Content[] = $tblDebtorSelection->getServiceTblInventoryItem()->getName();
                }
            }
        }
        if (!$Confirm) {
            $Stage->addButton(new Backward());
            $Stage->setContent(
                $PersonPanel
                .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new Panel(new Question().' Zuweisung wirklich entfernen?',
                        $Content,
                        Panel::PANEL_TYPE_DANGER,
                        new Standard(
                            'Ja', '/Billing/Accounting/DebtorSelection/Person/Destroy', new Ok(),
                            array('Id'      => $tblPerson->getId(),
                                  'Confirm' => true)
                        )
                        .new Standard(
                            'Nein', '/Billing/Accounting/DebtorSelection', new Disable())
                    )
                ))))
            );
        } else {

            // Destroy Reference
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        ( Banking::useService()->destroyDebtorSelectionByPerson($tblPerson)
                            ? new Success('Zuweisungen entfernt')
                            .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_SUCCESS)
                            : new Danger('Es konnten nicht alle Zuweisung entfernt werden')
                            .new Redirect('/Billing/Accounting/DebtorSelection', Redirect::TIMEOUT_ERROR)
                        )
                    )))
                )))
            );
        }

        return $Stage;
    }

}
