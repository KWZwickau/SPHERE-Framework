<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankAccount;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor;
use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Briefcase;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
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
    public function frontendDebtor()
    {

        $Stage = new Stage();
        $Stage->setTitle('Debitoren');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage('Zeigt die verfügbaren Debitoren an');
//        $Stage->addButton(
//            new Standard('Debitor anlegen', '/Billing/Accounting/Banking/Person', new Plus())
//        );

        $TableContent = array();
        $tblDebtorAll = Banking::useService()->getDebtorAll();
        if ($tblDebtorAll) {
            array_walk($tblDebtorAll, function (TblDebtor $tblDebtor) use (&$TableContent) {

                $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                $tblPerson = $tblDebtor->getServicePeoplePerson();
                $Item['Person'] = $tblPerson->getFullName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $Item['Address'] = '';
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
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'DebtorNumber' => 'Debitoren-Nr',
                                    'Person'       => 'Name',
                                    'Address'      => 'Adresse',
                                    'Option'       => ''
                                ))
                        )
                    ), new Title(new Listing().' Übersicht')
                )
            )
            .$this->layoutPersonList('Banking')
        );

        return $Stage;
    }

    /**
     * @param      $Id
     * @param null $Debtor
     *
     * @return Stage
     */
    public function frontendDebtorAdd($Id, $Debtor = null)
    {

        $Stage = new Stage('Debitor', 'Anlegen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($Id);

        $TableContent = array();
        $tblDebtorList = Banking::useService()->getDebtorAllByPerson($tblPerson);
        if ($tblDebtorList) {
            array_walk($tblDebtorList, function (TblDebtor $tblDebtor) use (&$TableContent) {

                $TableContent[] = $tblDebtor->getDebtorNumber();
            });
        }

        $PersonPanel = '';
        if ($tblPerson) {
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
                                ( !empty( $TableContent ) ) ?
                                    new Panel('Debitor-Nummer der Person', $TableContent
                                        , Panel::PANEL_TYPE_SUCCESS)
                                    : null
                                , 4)
                        )
                    ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
                )
            );
        }

        $Form = $this->formDebtor()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        if ($tblPerson) {
            $Stage->setContent($PersonPanel
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                ( empty( $TableContent ) ) ?
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
        } else {
            $Stage->setContent(new Warning('Person konnte nicht aufgerufen werden.'));
        }
        return $Stage;
    }

    public function frontendDebtorChange($Id, $Debtor = null)
    {

        $Stage = new Stage('Debitor', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/Banking', new ChevronLeft()));
        $tblDebtor = Banking::useService()->getDebtorById($Id);
        $tblPerson = $tblDebtor->getServicePeoplePerson();
        $PersonPanel = '';
        if ($tblPerson) {
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
        }

        $Global = $this->getGlobal();
        if (!isset( $Global->POST['Debtor'] )) {
            $Global->POST['Debtor']['DebtorNumber'] = $tblDebtor->getDebtorNumber();
            $Global->savePost();
        }

        $Form = $this->formDebtor()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        if ($tblPerson) {
            $Stage->setContent($PersonPanel
                .
                new Layout(
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
        } else {
            $Stage->setContent(new Warning('Person konnte nicht aufgerufen werden.'));
        }
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
                if ($tblBankAccount->getServicePeoplePerson()) {
                    $Item['Person'] = $tblBankAccount->getServicePeoplePerson()->getFullName();
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
                                    'Owner'    => 'Kontoinhaber',
                                    'Bank'     => 'Bank',
                                    'IBAN'     => 'IBAN',
                                    'BIC'      => 'BIC',
                                    'CashSign' => 'Kassenzeichen',
                                    'Person'   => 'Person',
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
     * @param      $Id
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendBankAccountAdd($Id, $Account = null)
    {

        $Stage = new Stage('Kontodaten', 'Anlegen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankAccount', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($Id);
        $PersonPanel = '';
        if ($tblPerson) {
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

        if ($tblPerson) {
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
        } else {
            $Stage->setContent(new Warning('Person konnte nicht aufgerufen werden.'));
        }
        return $Stage;
    }

    public function frontendBankAccountView($Id)
    {

        $Stage = new Stage('Konto', 'Detailansicht');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankAccount', new ChevronLeft()));
        $tblBankAccount = Banking::useService()->getBankAccountById($Id);
        if ($tblBankAccount) {

            $tblPerson = $tblBankAccount->getServicePeoplePerson();
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
                    )), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
                )
            );

            $Stage->setContent(
                $PersonPanel
                .$this->layoutBankAccount($tblBankAccount)
                .new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Standard('Bearbeiten', '/Billing/Accounting/BankAccount/Change', new Pencil(),
                                    array('Id' => $tblBankAccount->getId()))
                            )
                        )
                    )
                )
            );
        } else {
            $Stage->setContent(
                new \SPHERE\Common\Frontend\Message\Repository\Warning('Konto nicht gefunden')
                .new Redirect('/Billing/Accounting/BankAccount', Redirect::TIMEOUT_ERROR)
            );
        }
        return $Stage;
    }

    /**
     * @param      $Id
     * @param null $Account
     *
     * @return Stage
     */
    public function frontendBankAccountChange($Id, $Account = null)
    {

        $Stage = new Stage('Konto', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankAccount', new ChevronLeft()));
        $tblBankAccount = Banking::useService()->getBankAccountById($Id);
        if ($tblBankAccount) {

            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Account'] )) {
                $Global->POST['Account']['Owner'] = $tblBankAccount->getOwner();
                $Global->POST['Account']['BankName'] = $tblBankAccount->getBankName();
                $Global->POST['Account']['IBAN'] = $tblBankAccount->getIBAN();
                $Global->POST['Account']['BIC'] = $tblBankAccount->getBIC();
                $Global->POST['Account']['CashSign'] = $tblBankAccount->getCashSign();
                $Global->savePost();
            }

            $tblPerson = $tblBankAccount->getServicePeoplePerson();
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
                                Banking::useService()->changeAccount(
                                    $Form, $tblBankAccount, $Account)
                            ))
                        ), new Title(new Pencil().' Bearbeiten')
                    )
                )
            );
        } else {
            $Stage->setContent(
                new \SPHERE\Common\Frontend\Message\Repository\Warning('Konto nicht gefunden')
                .new Redirect('/Billing/Accounting/BankAccount', Redirect::TIMEOUT_ERROR)
            );
        }
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

        $Stage = new Stage('Referenzen', 'Übersicht');
        $TableContent = array();
        $tblBankReferenceAll = Banking::useService()->getBankReferenceAll();
        if ($tblBankReferenceAll) {
            array_walk($tblBankReferenceAll, function (TblBankReference $tblBankReference) use (&$TableContent) {

                $Item['Reference'] = $tblBankReference->getReference();
                $Item['ReferenceDate'] = $tblBankReference->getReferenceDate();
                $Item['Person'] = new Warning('Person nicht gefunden');
                if ($tblBankReference->getServicePeoplePerson()) {
                    $Item['Person'] = $tblBankReference->getServicePeoplePerson()->getFullName();
                }
                $Item['Option'] =
                    (new Standard('', '/Billing/Accounting/BankReference/Change',
                        new Pencil(), array(
                            'Id' => $tblBankReference->getId()
                        ), 'Datum bearbeiten'))->__toString()
                    .(new Standard('', '/Billing/Accounting/BankReference/Deactivate',
                        new Disable(), array(
                            'Id' => $tblBankReference->getId()
                        ), 'Referenz entfernen'))->__toString();

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
                                    'Reference'     => 'Referenz',
                                    'ReferenceDate' => 'Gültig ab',
                                    'Person'        => 'Person',
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
     * @param      $Id
     * @param null $Reference
     *
     * @return Stage
     */
    public function frontendBankReferenceAdd($Id, $Reference = null)
    {

        $Stage = new Stage('Referenz', 'Anlegen');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankReference', new ChevronLeft()));
        $tblPerson = Person::useService()->getPersonById($Id);
        $ReferenceContent = array();
        $PersonPanel = '';
        if ($tblPerson) {

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
                                new Panel('Referenzen'.new PullRight('Gültig ab:'), $ReferenceContent, Panel::PANEL_TYPE_SUCCESS)
                                : null
                                , 4),
                        )
                    ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
                )
            );
        }


        $Form = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Verweis', array(new TextField('Reference[Reference]', '', 'Referenz-Nummer'))
                            , Panel::PANEL_TYPE_INFO)
                        , 6),
                    new FormColumn(
                        new Panel('Gültig ab', array(new DatePicker('Reference[ReferenceDate]', '', 'Referenz Datum', new Time()))
                            , Panel::PANEL_TYPE_INFO)
                        , 6)
                ))
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        if ($tblPerson) {
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
        } else {
            $Stage->setContent(new Warning('Person konnte nicht aufgerufen werden.'));
        }
        return $Stage;
    }

    public function frontendBankReferenceChange($Id, $Reference = null)
    {

        $Stage = new Stage('Referenz', 'Bearbeiten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Accounting/BankReference', new ChevronLeft()));
        $tblBankReference = Banking::useService()->getBankReferenceById($Id);
        $tblPerson = $tblBankReference->getServicePeoplePerson();
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
                                new Panel('Referenzen'.new PullRight('Gültig ab:'), $ReferenceContent, Panel::PANEL_TYPE_SUCCESS)
                                : null
                                , 4),
                        )
                    ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
                )
            );
        }


        $Form = new Form(
            new FormGroup(
                new FormRow(array(
//                    new FormColumn(
//                        new TextField('Reference[Reference]', '', 'Referenz-Nummer')
//                        , 6),
                    new FormColumn(
                        new Panel('Referenz: '.$tblBankReference->getReference(), array(
                            new DatePicker('Reference[ReferenceDate]', '', 'Referenz Datum', new Time())),
                            Panel::PANEL_TYPE_INFO)
                    )
                ))
            )
        );
        $Form->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        if ($tblPerson) {
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
        } else {
            $Stage->setContent(new Warning('Person konnte nicht aufgerufen werden.'));
        }
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

                if ($Target === 'Banking') {
                    $Item['DebtorNumber'] = '';
                    $tblDebtor = Banking::useService()->getDebtorByPerson($tblPerson);
                    if ($tblDebtor) {
                        $Item['DebtorNumber'] = $tblDebtor->getDebtorNumber();
                    }
                    if ($Item['DebtorNumber'] !== '') {
                        $Item['Option'] = '';
                    }
                }
                if ($Target === 'BankAccount') {
                    $Item['BankAcc'] = '';
                    $tblBankAccountList = Banking::useService()->getBankAccountByPerson($tblPerson);
                    if ($tblBankAccountList) {
                        $Counting = count($tblBankAccountList);

                        if ($Counting === 1) {
                            $Item['BankAcc'] = new \SPHERE\Common\Frontend\Text\Repository\Success('Kontodaten OK');
                        } elseif ($Counting >= 2) {
                            $Item['BankAcc'] = new \SPHERE\Common\Frontend\Text\Repository\Success($Counting.' Kontodaten OK');
                        }
                    }
                }
                if ($Target === 'BankReference') {
                    $Item['BankRef'] = '';
                    $tblReferenceList = Banking::useService()->getBankReferenceByPerson($tblPerson);
                    if ($tblReferenceList) {
                        $Counting = count($tblReferenceList);

                        if ($Counting === 1) {
                            $Item['BankRef'] = new \SPHERE\Common\Frontend\Text\Repository\Success('Eine Referenz');
                        } elseif ($Counting >= 2)
                            $Item['BankRef'] = new \SPHERE\Common\Frontend\Text\Repository\Success($Counting.' Referenzen');
                    }
                }

                array_push($TableContentPerson, $Item);
            });
        }
        if ($Target === 'Banking') {
            return new Layout(
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
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            );
        } elseif ($Target === 'BankAccount') {
            return new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContentPerson, null,
                                array(
                                    'Person'  => 'Person',
                                    'Address' => 'Adresse',
                                    'BankAcc' => 'Kontodaten',
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
                                    'BankRef' => 'Referenzen',
                                    'Option'  => ''
                                ))
                        )
                    ), new Title(new PlusSign().' Hinzufügen')
                )
            );
        }

        return false;
    }

    public function frontendBankReferenceDeactivate($Id, $Confirm = false)
    {

        $Stage = new Stage('Referenz', 'Entfernen');
        $tblBankReference = Banking::useService()->getBankReferenceById($Id);
        if ($tblBankReference) {

            $tblPerson = $tblBankReference->getServicePeoplePerson();
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
                                    new Panel('Referenzen', $ReferenceContent, Panel::PANEL_TYPE_SUCCESS)
                                    : null
                                    , 4),
                            )
                        ), new Title(new \SPHERE\Common\Frontend\Icon\Repository\Person().' Person')
                    )
                );
            }


            $Content = array();
            $Content[] = 'Referenz: '.$tblBankReference->getReference();
            $Content[] = 'Datum: '.$tblBankReference->getReferenceDate();
            if (!$Confirm) {
                $Stage->setContent(
                    $PersonPanel
                    .new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new Panel(new Question().' Referenz wirklich entfernen?',
                            $Content,
                            Panel::PANEL_TYPE_DANGER,
                            new Standard(
                                'Ja', '/Billing/Accounting/BankReference/Deactivate', new Ok(),
                                array('Id' => $Id, 'Confirm' => true)
                            )
                            .new Standard(
                                'Nein', '/Billing/Accounting/BankReference', new Disable())
                        )
                    ))))
                );
            } else {

                // Destroy Group
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            ( Banking::useService()->deactivateBankReference($tblBankReference)
                                ? new Success('Referenz entfernt')
                                .new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_SUCCESS)
                                : new Danger('Referenz konnte nicht entfernt werden')
                                .new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR)
                            )
                        )))
                    )))
                );
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger('Referenz nicht gefunden'),
                        new Redirect('/Billing/Accounting/BankReference', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }

        return $Stage;
    }
}
