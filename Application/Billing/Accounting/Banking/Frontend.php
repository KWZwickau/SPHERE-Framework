<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankAccount;
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
use SPHERE\Common\Frontend\Icon\Repository\Briefcase;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\Icon\Repository\Unchecked;
use SPHERE\Common\Frontend\IFrontendInterface;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
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
        $Stage->setMessage('Zeigt die verfügbaren Debitoren an');
//        $Stage->addButton(
//            new Standard('Debitor anlegen', '/Billing/Accounting/Banking/Person', new Plus())
//        );
        new Backward();
        $TableContent = array();
        $tblDebtorAll = Banking::useService()->getDebtorAll();
        if ($tblDebtorAll) {
            array_walk($tblDebtorAll, function (TblDebtor $tblDebtor) use (&$TableContent) {

                $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                $tblPerson = $tblDebtor->getServiceTblPerson();
                if ($tblPerson) {
                    $Item['Person'] = $tblPerson->getFullName();
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    $Item['Address'] = new Warning('Nicht hinterlegt');
                    if ($tblAddress) {
                        $Item['Address'] = $tblAddress->getGuiString();
                    }
                    $Item['Option'] =
                        (new Standard('', '/Billing/Accounting/Banking/Change',
                            new Pencil(), array(
                                'Id' => $tblDebtor->getId()
                            ), 'Debitor bearbeiten'))->__toString();
//                    (new Standard('', '/Billing/Accounting/Banking/Destroy',
//                        new Remove(), array(
//                            'Id' => $tblDebtor->getId()
//                        ), 'Debitor löschen'))->__toString();
                    array_push($TableContent, $Item);
                }
            });
        }
        $TableContentPerson = array();
        $tblPersonAll = Person::useService()->getPersonAll();

        if ($tblPersonAll) {
            array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$TableContentPerson) {

                $Item['Person'] = $tblPerson->getFullName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $Item['Address'] = new Warning('Nicht hinterlegt');
                if ($tblAddress) {
                    $Item['Address'] = $tblAddress->getGuiString();
                }
                $Item['Option'] =
                    (new Standard('', '/Billing/Accounting/Banking/Add',
                        new Plus(), array(
                            'Id' => $tblPerson->getId()
                        ), 'Erstellen'))->__toString();

                $Item['DebtorNumber'] = '';
                $tblDebtor = Banking::useService()->getDebtorByPerson($tblPerson);
                if ($tblDebtor) {
                    $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                }
                if ($Item['DebtorNumber'] !== '') {
                    $Item['Option'] = (new Standard('', '/Billing/Accounting/Banking/Change',
                        new Pencil(), array(
                            'Id' => $tblDebtor->getId()
                        ), 'Debitor bearbeiten'))->__toString();
                }
                array_push($TableContentPerson, $Item);
            });
        }


        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContentPerson, null,
                                array(
                                    'Person'       => 'Person',
                                    'Address'      => 'Adresse',
                                    'DebtorNumber' => 'Debitor-Nummer',
                                    'Option'       => ''
                                ))
                        )
                    ), new Title(new PlusSign().' Hinzufügen / '.new Pencil().' Bearbeiten')
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
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Auf die Person konnte nicht zugegriffen werden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }

        $Content = '';
        $tblDebtor = Banking::useService()->getDebtorByPerson($tblPerson);
        if ($tblDebtor) {
            $Content = $tblDebtor->getDebtorNumber();
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
                                new Warning('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(
                            ( !empty( $Content ) ) ?
                                new Panel('Debitor-Nummer der Person', array($Content)
                                    , Panel::PANEL_TYPE_SUCCESS)
                                : null
                            , 4)
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
            )
        );

        $Form = $this->formDebtor()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($PersonPanel
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( empty( $Content ) ) ?
                                new Well(
                                    Banking::useService()->createDebtor(
                                        $Form, $Debtor, $Id
                                    )) : new \SPHERE\Common\Frontend\Message\Repository\Warning(
                                'Diese Person besitzt bereits eine Debitor-Nummer')
                            , 6)
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );
        return $Stage;
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
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking', new ChevronLeft()));
        $Stage->addButton(new Backward());
        $tblDebtor = $Id === null ? false : Banking::useService()->getDebtorById($Id);
        if (!$tblDebtor) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Auf den Debitor konnte nicht zugegriffen werden'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
        }
        $tblPerson = $tblDebtor->getServiceTblPerson();
        if (!$tblPerson) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Person konnte nicht aufgerufen werden.'));
            return $Stage.new Redirect('/Billing/Accounting/Banking', Redirect::TIMEOUT_ERROR);
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
                                new Warning('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(
                            new Panel('Debtor-Nummer', $tblDebtor->getDebtorNumber(), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
            )
        );

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Debtor'] )) {
            $Global->POST['Debtor']['DebtorNumber'] = $tblDebtor->getDebtorNumber();
            $Global->savePost();
        }

        $Form = $this->formDebtor()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($PersonPanel
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                Banking::useService()->changeDebtor(
                                    $Form, $tblDebtor, $Debtor
                                ))
                            , 6)
                    ), new Title(new Pencil().' Bearbeiten')
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
     * @return Stage
     */
    public function frontendBankAccount()
    {

        $Stage = new Stage('Bankinformation', 'Übersicht');
        new Backward();
        $TableContent = array();
        $tblBankAccountAll = Banking::useService()->getBankAccountAll();
        if ($tblBankAccountAll) {
            array_walk($tblBankAccountAll, function (TblBankAccount $tblBankAccount) use (&$TableContent) {

                $Item['Owner'] = $tblBankAccount->getOwner();
                $Item['Bank'] = $tblBankAccount->getBankName();
                $Item['IBAN'] = $tblBankAccount->getIBAN();
                $Item['BIC'] = $tblBankAccount->getBIC();
                $Item['CashSign'] = $tblBankAccount->getCashSign();
                $Item['Person'] = new Warning('Person nicht gefunden');
                if ($tblBankAccount->getServiceTblPerson()) {
                    $Item['Person'] = $tblBankAccount->getServiceTblPerson()->getFullName();
                }
                $Item['Option'] =
                    (new Standard('', '/Billing/Accounting/BankAccount/Change',
                        new Pencil(), array(
                            'Id' => $tblBankAccount->getId()
                        ), 'Bankdaten einsehen / bearbeiten'))->__toString();

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
                                    'Person'   => 'Person',
                                    'Owner'    => 'Kontoinhaber',
                                    'Bank'     => 'Bank',
                                    'IBAN'     => 'IBAN',
                                    'BIC'      => 'BIC',
                                    'CashSign' => 'Kassenzeichen',
                                    'Option'   => ''
                                ))
                        )
                    ), new Title(new Listing().' Übersicht')
                )
            )
            .$this->layoutPersonList('BankAccount')
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendAddBankAccount($Id = null, $Account = null)
    {

        $Stage = new Stage('Kontodaten', 'Anlegen');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankAccount', new ChevronLeft()));
        $Stage->addButton(new Backward());
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        if (!$tblPerson) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Person konnte nicht aufgerufen werden.'));
            return $Stage.new Redirect('/Billing/Accounting/BankAccount', Redirect::TIMEOUT_ERROR);
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
                                new Warning('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                            , 4)
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
            )
        );
        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Account'] )) {
            $Global->POST['Account']['Owner'] = $tblPerson->getFirstName().' '.$tblPerson->getLastName();
            $Global->savePost();
        }

        $TableContent = array();
        $tblAccountList = Banking::useService()->getBankAccountByPerson($tblPerson);
        if ($tblAccountList) {
            array_walk($tblAccountList, function (TblBankAccount $tblBankAccount) use (&$TableContent) {

                $Item['Owner'] = $tblBankAccount->getOwner();
                $Item['BankName'] = $tblBankAccount->getBankName();
                $Item['IBAN'] = $tblBankAccount->getIBAN();
                $Item['BIC'] = $tblBankAccount->getBIC();
                $Item['CashSign'] = $tblBankAccount->getCashSign();

                array_push($TableContent, $Item);
            });
        }

        $Form = $this->formAccount()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($PersonPanel
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty( $TableContent ) ) ?
                                new TableData($TableContent, null,
                                    array('Owner'    => 'Kontoinhaber',
                                          'BankName' => 'Name der Bank',
                                          'IBAN'     => 'IBAN',
                                          'BIC'      => 'BIC',
                                          'CashSign' => 'Kassenzeichen'
                                    ), array("bPaginate" => false))
                                : null
                        )
                    ), new Title(new Briefcase().' Aufgenommene Kontodaten')
                )
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Banking::useService()->createAccount(
                                $Form, $tblPerson, $Account)
                        ))
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendChangeBankAccount($Id = null, $Account = null)
    {

        $Stage = new Stage('Konto', 'Bearbeiten');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankAccount', new ChevronLeft()));
        $Stage->addButton(new Backward());
        $tblBankAccount = $Id === null ? false : Banking::useService()->getBankAccountById($Id);
        if (!$tblBankAccount) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Konto nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/BankAccount', Redirect::TIMEOUT_ERROR);
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Account'] )) {
            $Global->POST['Account']['Owner'] = $tblBankAccount->getOwner();
            $Global->POST['Account']['BankName'] = $tblBankAccount->getBankName();
            $Global->POST['Account']['IBAN'] = $tblBankAccount->getIBAN();
            $Global->POST['Account']['BIC'] = $tblBankAccount->getBIC();
            $Global->POST['Account']['CashSign'] = $tblBankAccount->getCashSign();
            $Global->savePost();
        }

        $tblPerson = $tblBankAccount->getServiceTblPerson();
        $FullName = new Warning('Person nicht gefunden.');
        $tblAddress = false;
        if ($tblPerson) {
            $FullName = $tblPerson->getFullName();
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
        }
        $PersonPanel = new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Name', $FullName, Panel::PANEL_TYPE_SUCCESS)
                        , 4),
                    new LayoutColumn(
                        new Panel('Addresse', ( $tblAddress ) ? $tblAddress->getGuiString() :
                            new Warning('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                        , 4)
                )), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
            )
        );

        $Form = $this->formAccount()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            $PersonPanel
            .$this->layoutBankAccount($tblBankAccount)
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Banking::useService()->changeBankAccount(
                                $Form, $tblBankAccount, $Account)
                        ))
                    ), new Title(new Pencil().' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param TblBankAccount $tblBankAccount
     *
     * @return Layout
     */
    public function layoutBankAccount(TblBankAccount $tblBankAccount)
    {

        $Owner = $tblBankAccount->getOwner();
        $BankName = $tblBankAccount->getBankName();
        $IBAN = $tblBankAccount->getIBAN();
        $BIC = $tblBankAccount->getBIC();
        $CashSign = $tblBankAccount->getCashSign();

        return new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Kontoinhaber', array($Owner), Panel::PANEL_TYPE_SUCCESS)
                        , 5),
                    new LayoutColumn(
                        new Panel('IBAN', array($IBAN), Panel::PANEL_TYPE_SUCCESS)
                        , 5),
                    new LayoutColumn(
                        new Panel('Kassenzeichen', array($CashSign), Panel::PANEL_TYPE_SUCCESS)
                        , 2),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Name der Bank', array($BankName), Panel::PANEL_TYPE_SUCCESS)
                        , 5),
                    new LayoutColumn(
                        new Panel('BIC', array($BIC), Panel::PANEL_TYPE_SUCCESS)
                        , 5),
                )),
            ), new Title(new Briefcase().' Kontodaten'))
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
        $TableContent = array();
        $tblBankReferenceAll = Banking::useService()->getBankReferenceAll();
        if ($tblBankReferenceAll) {
            array_walk($tblBankReferenceAll, function (TblBankReference $tblBankReference) use (&$TableContent) {

                $Item['Reference'] = $tblBankReference->getReference();
                $Item['ReferenceDate'] = $tblBankReference->getReferenceDate();
                $Item['Person'] = new Warning('Person nicht gefunden');
                if ($tblBankReference->getServiceTblPerson()) {
                    $Item['Person'] = $tblBankReference->getServiceTblPerson()->getFullName();
                }
                $Item['Option'] =
                    (new Standard('', '/Billing/Accounting/BankReference/Change',
                        new Pencil(), array(
                            'Id' => $tblBankReference->getId()
                        ), 'Datum bearbeiten'))
                    .(new Standard('', '/Billing/Accounting/BankReference/Remove',
                        new Disable(), array(
                            'Id' => $tblBankReference->getId()
                        ), 'Mandatsreferenz entfernen'))->__toString();

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
                                    'Person'        => 'Person',
                                    'Reference'     => 'Mandatsreferenz',
                                    'ReferenceDate' => 'Gültig ab',
                                    'Option'        => ''
                                ))
                        )
                    ), new Title(new Listing().' Übersicht')
                )
            )
            .$this->layoutPersonList('BankReference')
        );
        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Reference
     *
     * @return Stage
     */
    public function frontendAddBankReference($Id = null, $Reference = null)
    {

        $Stage = new Stage('Mandatsreferenz', 'Anlegen');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankReference', new ChevronLeft()));
        $Stage->addButton(new Backward());
        $tblPerson = $Id === null ? false : Person::useService()->getPersonById($Id);
        $ReferenceContent = array();
        if (!$tblPerson) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Person nicht gefunden.'));
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
                                new Warning('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(( !empty( $ReferenceContent ) ) ?
                            new Panel('Mandatsreferenz'.new PullRight('Gültig ab:'), $ReferenceContent, Panel::PANEL_TYPE_SUCCESS)
                            : null
                            , 4),
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
            )
        );

        $Form = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Verweis', array(new TextField('Reference[Reference]', '', 'Mandatsreferenz-Nummer'))
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Gültig ab', array(new DatePicker('Reference[ReferenceDate]', '', 'Mandatsreferenz Datum', new Time()))
                            , Panel::PANEL_TYPE_INFO)
                        , 6)
                ))
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

    /**
     * @param null $Id
     * @param null $Reference
     *
     * @return Stage
     */
    public function frontendChangeBankReference($Id = null, $Reference = null)
    {

        $Stage = new Stage('Mandatsreferenz', 'Bearbeiten');
//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankReference', new ChevronLeft()));
        $Stage->addButton(new Backward());
        $tblBankReference = $Id === null ? false : Banking::useService()->getBankReferenceById($Id);
        if (!$tblBankReference) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Mandatsreferenz nicht gefunden.'));
            return $Stage.new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR);
        }
        $tblPerson = $tblBankReference->getServiceTblPerson();
        if (!$tblPerson) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Person nicht gefunden.'));
            return $Stage.new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR);
        }

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
                                new Warning('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
                            , 4),
                        new LayoutColumn(( !empty( $ReferenceContent ) ) ?
                            new Panel('Mandatsreferenz'.new PullRight('Gültig ab:'), $ReferenceContent, Panel::PANEL_TYPE_SUCCESS)
                            : null
                            , 4),
                    )
                ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
            )
        );


        $Form = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Mandatsreferenz: '.$tblBankReference->getReference(), array(
                            new DatePicker('Reference[ReferenceDate]', '', 'Mandatsreferenz Datum', new Time())),
                            Panel::PANEL_TYPE_INFO)
                    )
                ))
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent($PersonPanel
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            Banking::useService()->changeReference(
                                $Form, $tblBankReference, $Reference)
                        ), 6)
                    ), new Title(new Pencil().' Bearbeiten')
                )
            )
        );
        return $Stage;
    }

    /**
     * @param $Target
     *
     * @return bool|Layout
     */
    public function layoutPersonList($Target)
    {

        $TableContentPerson = array();
        $tblPersonAll = Person::useService()->getPersonAll();
        if ($tblPersonAll) {
            array_walk($tblPersonAll, function (TblPerson $tblPerson) use (&$TableContentPerson, $Target) {

                $Item['Person'] = $tblPerson->getFullName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $Item['Address'] = new Warning('Nicht hinterlegt');
                if ($tblAddress) {
                    $Item['Address'] = $tblAddress->getGuiString();
                }
                $Item['Option'] =
                    (new Standard('', '/Billing/Accounting/'.$Target.'/Add',
                        new Plus(), array(
                            'Id' => $tblPerson->getId()
                        ), 'Erstellen'))->__toString();
//                if ($Target === 'BankAccount') {
//                    $Item['BankAcc'] = '';
//                    $tblBankAccountList = Banking::useService()->getBankAccountByPerson($tblPerson);
//                    if ($tblBankAccountList) {
//                        $Counting = count($tblBankAccountList);
//
//                        if ($Counting === 1) {
//                            $Item['BankAcc'] = new \SPHERE\Common\Frontend\Text\Repository\Success('Kontodaten OK');
//                        } elseif ($Counting >= 2) {
//                            $Item['BankAcc'] = new \SPHERE\Common\Frontend\Text\Repository\Success($Counting.' Kontodaten OK');
//                        }
//                    }
//                }
//                if ($Target === 'BankReference') {
//                    $Item['BankRef'] = '';
//                    $tblReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
//                    if ($tblReferenceList) {
//                        $Counting = count($tblReferenceList);
//
//                        if ($Counting === 1) {
//                            $Item['BankRef'] = new \SPHERE\Common\Frontend\Text\Repository\Success('Eine Mandatsreferenz');
//                        } elseif ($Counting >= 2)
//                            $Item['BankRef'] = new \SPHERE\Common\Frontend\Text\Repository\Success($Counting.' Mandatsreferenzen');
//                    }
//                }

                array_push($TableContentPerson, $Item);
            });
        }
        if ($Target === 'BankAccount') {
            return new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContentPerson, null,
                                array(
                                    'Person'  => 'Person',
                                    'Address' => 'Adresse',
//                                    'BankAcc' => 'Kontodaten',
                                    'Option'  => ''
                                ))
                        )
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            );
        } elseif ($Target === 'BankReference') {
            return new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContentPerson, null,
                                array(
                                    'Person'  => 'Person',
                                    'Address' => 'Adresse',
//                                    'BankRef' => 'Mandatsreferenzen',
                                    'Option'  => ''
                                ))
                        )
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            );
        }

        return false;
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
                                    new Warning('Nicht hinterlegt'), Panel::PANEL_TYPE_SUCCESS)
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
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage
            .new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        // Abbruch beim löschen der Zuordnungen
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerification) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Keine Daten zum fakturieren vorhanden.'));
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

                            $tblPerson = $tblRelationShip->getServiceTblPersonFrom();
                            if ($tblPerson) {
                                $tblBankAccount = Banking::useService()->getBankAccountByPerson($tblPerson);
                                $tblBankReference = Banking::useService()->getBankReferenceByPerson($tblPerson);

                                if (!empty( $tblBankAccount ) || !empty( $tblBankReference )) {
                                    $PaymentPerson[] = $tblPerson;
                                }
                            }
                        });
                    }
//                    $tblDebtor = Banking::useService()->getDebtorByPerson($tblPerson);
                    $tblBankAccount = Banking::useService()->getBankAccountByPerson($tblPerson);
                    $tblBankReference = Banking::useService()->getBankReferenceByPerson($tblPerson);
                    if (!empty( $tblBankAccount ) || !empty( $tblBankReference )) { // ToDO /*!empty( $tblDebtor ) ||*/ Schüler nur mit Debitornummer auch anzeigen lassen?
                        $PaymentPerson[] = $tblPerson;
                    }

                    $Item['Item'] = $tblItem->getName();
                    $Item['Value'] = $tblBasketVerification->getSummaryPrice();
                    $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                    if (!empty( $PaymentPerson )) {
                        $Item['SelectPayers'] = new SelectBox('Data['.$tblBasketVerification->getId().'][PersonPayers]', '', array(
                            '{{ FullName }}'
//                    .' - {{ ServiceBillingDebtor.ServicePeoplePerson.FullName }}'
//                    .'{% if( ServiceBillingDebtor.Description is not empty) %} - {{ ServiceBillingDebtor.Description }}{% endif %}'
                            => $PaymentPerson
                        ));
                    } else {
                        $Item['SelectPayers'] = new Warning('Bezahler anlegen!');
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
            $Stage->setContent(new Warning('Warenkorb nicht gefunden'));
            return $Stage.new Redirect('/Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }
        // Abbruch beim löschen der Zuordnungen
        $tblBasketVerification = Basket::useService()->getBasketVerificationByBasket($tblBasket);
        if (!$tblBasketVerification) {
            $Stage->setContent(new \SPHERE\Common\Frontend\Message\Repository\Warning('Keine Daten zum fakturieren vorhanden.'));
            return $Stage.new Redirect('Billing/Accounting/Basket', Redirect::TIMEOUT_ERROR);
        }

//        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Basket/Verification', new ChevronLeft()
//            , array('Id' => $tblBasket->getId())));
        $Stage->addButton(new Backward(true));
        $Global = $this->getGlobal();

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
//                    $Payment[] = new RadioBox('test','','inhalt');
                        $tblBankAccountList = Banking::useService()->getBankAccountByPerson($tblPersonPayers);
                        if ($tblBankAccountList) {
                            foreach ($tblBankAccountList as $tblBankAccount) {
                                $Payment[] = 'Bank: '.new RadioBox('Data['.$tblDebtorSelection->getId().'][SelectBox]',
                                        $tblBankAccount->getOwner().' - '.$tblBankAccount->getBankName(), 'Ban'.$tblBankAccount->getId());
                            }
                        }
                        $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPersonPayers);
                        if ($tblBankReferenceList) {
                            foreach ($tblBankReferenceList as $tblBankReference) {
                                $Payment[] = 'Mandatsreferenz: '.new RadioBox('Data['.$tblDebtorSelection->getId().'][SelectBox]',
                                        $tblBankReference->getReference(), 'Ref'.$tblBankReference->getId());
                            }
                        }
                        $Debtor = array();
                        if (Banking::useService()->getDebtorByPerson($tblPerson)) {
                            $Debtor[] = Banking::useService()->getDebtorByPerson($tblPerson);
                        }
                        if (Banking::useService()->getDebtorByPerson($tblPersonPayers)) {
                            $Debtor[] = Banking::useService()->getDebtorByPerson($tblPersonPayers);
                        }

                        if (!empty( $Debtor )) {
//                        $Debtor[] = new TblDebtor();
                            $Item['SelectDebtor'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Debtor]', '',
                                array(
                                    '{{ DebtorNumber }}'
//                                .' - {{ ServicePeoplePerson.FullName }}'
//                        .'{% if( ServiceBillingDebtor.Description is not empty) %} - {{ ServiceBillingDebtor.Description }}{% endif %}'
                                    => $Debtor)
                            );
                        } else {
                            $Item['SelectDebtor'] = new \SPHERE\Common\Frontend\Text\Repository\Danger('Debitor benötigt!');
                        }
                        if (!empty( $Payment )) {
                            $Item['SelectBox'] = new Panel('Zahlung:', $Payment);
                        } else {
                            $Item['SelectBox'] = new Warning('keine Kontoinformationen');
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

        $Global->savePost();

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
//                            'SelectPayType' => 'Typ'
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

        //ToDO doppeltes Array für sinnvolleres auslesen.

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
                        if ($tblDebtorSelection->getTblDebtor() === false && $tblDebtorSelection->getTblBankAccount() === false
                            || $tblDebtorSelection->getTblDebtor() === false && $tblDebtorSelection->getTblBankReference() === false
                        ) {
                            if ($tblDebtorSelection->getServiceTblPaymentType()->getName() === 'Bar') {
                                $Status[] = new \SPHERE\Common\Frontend\Text\Repository\Success(new Check().' Bar');
                            } elseif ($tblDebtorSelection->getServiceTblPaymentType()->getName() === 'SEPA-Überweisung') {
                                $Status[] = new \SPHERE\Common\Frontend\Text\Repository\Success(new Check().' SEPA-Überweisung');
                            } else {
                                $Status[] = new Warning(new Unchecked().' Offen');

                            }
                        } else {
                            $Status[] = new \SPHERE\Common\Frontend\Text\Repository\Success(new Check().' OK');
                        }

                    }
                    $Item['ItemPayer'] = new \SPHERE\Common\Frontend\Layout\Repository\Listing($ItemPayer);
                    $Item['Status'] = new \SPHERE\Common\Frontend\Layout\Repository\Listing($Status);
                }
                $Item['Option'] = new Standard('', '/Billing/Accounting/DebtorSelection/PaySelection', new Pencil(),
                    array('Id' => $tblPerson->getId()), 'Bearbeiten');

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
            $Stage->setContent(new Warning('Person nicht gefunden'));
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
                            $tblBankAccount = Banking::useService()->getBankAccountByPerson($tblPerson);
                            $tblBankReference = Banking::useService()->getBankReferenceByPerson($tblPerson);

                            if (!empty( $tblBankAccount ) || !empty( $tblBankReference )) {
                                $PaymentPerson[] = $tblPerson;
                            }
                        }
                    });
                }
                $tblDebtor = Banking::useService()->getDebtorByPerson($tblPerson);
                $tblBankAccount = Banking::useService()->getBankAccountByPerson($tblPerson);
                $tblBankReference = Banking::useService()->getBankReferenceByPerson($tblPerson);
                if (!empty( $tblDebtor ) || !empty( $tblBankAccount ) || !empty( $tblBankReference )) {
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
                    $Item['SelectPayers'] = new Warning('Bezahler anlegen!');
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
            $Stage->setContent(new Warning('Person nicht gefunden'));
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
                    $tblBankAccountList = Banking::useService()->getBankAccountByPerson($tblPersonPayers);
                    if ($tblBankAccountList) {
                        foreach ($tblBankAccountList as $tblBankAccount) {
                            $Payment[] = 'Bank: '.new RadioBox('Data['.$tblDebtorSelection->getId().'][SelectBox]',
                                    $tblBankAccount->getOwner().' - '.$tblBankAccount->getBankName(), 'Ban'.$tblBankAccount->getId());
                        }
                    }
                    $tblBankReferenceList = Banking::useService()->getBankReferenceByPerson($tblPersonPayers);
                    if ($tblBankReferenceList) {
                        foreach ($tblBankReferenceList as $tblBankReference) {
                            $Payment[] = 'Mandatsreferenz: '.new RadioBox('Data['.$tblDebtorSelection->getId().'][SelectBox]',
                                    $tblBankReference->getReference().new Muted(' Gültig ab: ').$tblBankReference->getReferenceDate(),
                                    'Ref'.$tblBankReference->getId());
                        }
                    }
                    $Debtor = array();
                    if (Banking::useService()->getDebtorByPerson($tblPerson)) {
                        $Debtor[] = Banking::useService()->getDebtorByPerson($tblPerson);
                    }
                    if (Banking::useService()->getDebtorByPerson($tblPersonPayers)) {
                        $Debtor[] = Banking::useService()->getDebtorByPerson($tblPersonPayers);
                    }

                    if (!empty( $Debtor )) {
                        $Item['SelectDebtor'] = new SelectBox('Data['.$tblDebtorSelection->getId().'][Debtor]', '',
                            array(
                                '{{ DebtorNumber }}'
                                => $Debtor)
                        );
                    } else {
                        $Item['SelectDebtor'] = new \SPHERE\Common\Frontend\Text\Repository\Danger('Debitor benötigt!');
                    }
                    if (!empty( $Payment )) {
                        $Item['SelectBox'] = new Panel('Zahlung:', $Payment);
                    } else {
                        $Item['SelectBox'] = new Warning('keine Kontoinformationen');
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

        $Global->savePost();

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

}
