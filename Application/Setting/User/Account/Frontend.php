<?php
namespace SPHERE\Application\Setting\User\Account;


use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Setting\User\Account\Service\Entity\TblUserAccount;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Exchange;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Binding\AbstractView;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendPrepare()
    {

        $Stage = new Stage('Übersicht', 'Vorbereitung der Accounts');
        $Stage->addButton(new Standard('Zurück', '/People/User', new ChevronLeft()));
        $Stage->addButton(new Standard('Personenzuweisung', '/People/User/Account/Person', new Listing()));

        $IsSend = $IsExport = false;
        $tblUserAccountList = Account::useService()->getUserAccountByIsSendAndIsExport($IsSend, $IsExport);
        $TableContent = array();
        if ($tblUserAccountList) {
            array_walk($tblUserAccountList, function (TblUserAccount $tblUserAccount) use (&$TableContent) {

                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['Address'] = new WarningMessage('Keine Adresse gewählt');
                $Item['Year'] = '';
                $Item['Division'] = '';
                $Item['PersonList'] = '';

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();

                    $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblRelationshipList) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt') {

                                $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
                                if ($tblPersonCustody && $tblPersonCustody->getId() != $tblPerson->getId()) {
                                    if ($Item['PersonList'] == '') {
                                        $Item['PersonList'] = $tblPersonCustody->getLastFirstName();
                                    } else {
                                        $Item['PersonList'] .= '; '.$tblPersonCustody->getLastFirstName();
                                    }
                                    continue;
                                }
                                $tblPersonStudent = $tblRelationship->getServiceTblPersonTo();
                                if ($tblPersonStudent && $tblPersonStudent->getId() != $tblPerson->getId()) {
                                    if ($Item['PersonList'] == '') {
                                        $Item['PersonList'] = $tblPersonStudent->getLastFirstName();
                                    } else {
                                        $Item['PersonList'] .= '; '.$tblPersonStudent->getLastFirstName();
                                    }
                                    continue;
                                }
                            }
                        }
                    }
                }
                $tblToPersonAddress = $tblUserAccount->getServiceTblToPersonAddress();
                if ($tblToPersonAddress) {
                    $tblAddress = $tblToPersonAddress->getTblAddress();
                    if ($tblAddress) {
                        $Item['Address'] = $tblAddress->getGuiString();
                    }
                }

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ( !empty($TableContent)
                                ? new TableData($TableContent, new Title('Übersicht', 'Aller zu erstellenden Accounts'),
                                    array(
                                        'Salutation'   => 'Anrede',
                                        'Name'         => 'Name',
                                        'Address'      => 'Adresse',
                                        'DivisionYear' => 'Jahr',
                                        'Division'     => 'Klasse',
                                        'PersonList'   => 'Beziehungs Person(en)'
                                    ))
                                : new WarningMessage('Keine Personen vorhanden denen ein Account erstellt werden soll.
                                Bitte klicken Sie auf die '.new Standard('Personenzuweisung', '/People/User/Account/Person', new Listing()))
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $FilterGroup
     * @param null $FilterStudent
     * @param null $FilterPerson
     * @param null $FilterYear
     *
     * @return Stage
     */
    public function frontendPreparePersonList(
        $FilterGroup = null,
        $FilterStudent = null,
        $FilterPerson = null,
        $FilterYear = null
    ) {
        $Stage = new Stage('Personen', 'Zuweisung');
        $Stage->addButton(new Standard('Zurück', '/People/User/Account', new ChevronLeft()));
        $IsSend = $IsExport = false;
        $tblUserAccountList = Account::useService()->getUserAccountByIsSendAndIsExport($IsSend, $IsExport);
        $Global = $this->getGlobal();
        $IsPost = false;
        if (!isset($Global->POST['Button'])) {
            // set Year
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    $Global->POST['FilterYear']['TblYear_Id'] = $tblYear->getId();
                }
            }
            $Global->savePost();
        } else {
            $IsPost = true;
        }


        $Timeout = false;
        $SearchTable = false;
        $IsCustody = false;
        if (isset($FilterGroup['TblGroup_Id']) && $FilterGroup['TblGroup_Id'] != 0) {
            $Result = Account::useService()->getStudentFilterResultListBySerialLetter($FilterGroup, $FilterStudent, $FilterPerson, $FilterYear, $Timeout);
            if ($Result) {
                $tblGroup = Group::useService()->getGroupById($FilterGroup['TblGroup_Id']);
                if ($tblGroup->getMetaTable() == 'CUSTODY') {
                    $IsCustody = true;
                    $SearchTable = $this->getStudentTableByResult($Result, $IsCustody);
                } else {
                    $SearchTable = $this->getStudentTableByResult($Result);
                }
            }
        }

        $TableLeftContent = array();
        if ($tblUserAccountList) {
            array_walk($tblUserAccountList, function (TblUserAccount $tblUserAccount) use (&$TableLeftContent) {

                $Item['Exchange'] = new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(
                    'Id' => $tblUserAccount->getId()
                ));
                $Item['Salutation'] = new Muted('-NA-');
                $Item['Name'] = '';
                $Item['Address'] = new WarningMessage('Keine Adresse gewählt');
                $Item['Year'] = '';
                $Item['Division'] = '';
                $Item['PersonList'] = '';

                $tblPerson = $tblUserAccount->getServiceTblPerson();
                if ($tblPerson) {

                    $Item['Exchange'] = new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(
                        'Id'       => $tblUserAccount->getId(),
                        'PersonId' => $tblPerson->getId()
                    ));

                    if ($tblPerson->getSalutation() != '') {
                        $Item['Salutation'] = $tblPerson->getSalutation();
                    }
                    $Item['Name'] = $tblPerson->getLastFirstName();

                    $tblRelationshipList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                    if ($tblRelationshipList) {
                        foreach ($tblRelationshipList as $tblRelationship) {
                            if ($tblRelationship->getTblType()->getName() == 'Sorgeberechtigt') {

                                $tblPersonCustody = $tblRelationship->getServiceTblPersonFrom();
                                if ($tblPersonCustody && $tblPersonCustody->getId() != $tblPerson->getId()) {
                                    if ($Item['PersonList'] == '') {
                                        $Item['PersonList'] = $tblPersonCustody->getLastFirstName();
                                    } else {
                                        $Item['PersonList'] .= '; '.$tblPersonCustody->getLastFirstName();
                                    }
                                    continue;
                                }
                                $tblPersonStudent = $tblRelationship->getServiceTblPersonTo();
                                if ($tblPersonStudent && $tblPersonStudent->getId() != $tblPerson->getId()) {
                                    if ($Item['PersonList'] == '') {
                                        $Item['PersonList'] = $tblPersonStudent->getLastFirstName();
                                    } else {
                                        $Item['PersonList'] .= '; '.$tblPersonStudent->getLastFirstName();
                                    }
                                    continue;
                                }
                            }
                        }
                    }
                }
                $tblToPersonAddress = $tblUserAccount->getServiceTblToPersonAddress();
                if ($tblToPersonAddress) {
                    $tblAddress = $tblToPersonAddress->getTblAddress();
                    if ($tblAddress) {
                        $Item['Address'] = $tblAddress->getGuiString();
                    }
                }

                array_push($TableLeftContent, $Item);
            });
        }

        $tblUserAccountAll = Account::useService()->getUserAccountAll();
        // remove existing Person
        /** @var TblUserAccount[] $tblUserAccountList */
        if ($tblUserAccountAll && $SearchTable) {
            $tblPersonList = array();
            foreach ($tblUserAccountAll as $tblUserAccount) {
                $tblPerson = $tblUserAccount->getServiceTblPerson();
                $tblPersonList[] = $tblPerson;
            }

            $tblPersonIdList = array();
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$tblPersonIdList) {
                if (!in_array($tblPerson->getId(), $tblPersonIdList)) {
                    array_push($tblPersonIdList, $tblPerson->getId());
                }
            });

            array_filter($SearchTable, function (&$Item) use ($tblPersonIdList) {
                if (in_array($Item['TblPerson_Id'], $tblPersonIdList)) {
                    $Item = false;
                }
            });

            $SearchTable = array_filter($SearchTable);
        }

        $FormStudent = $this->formFilterStudent();
        $FormStudent
            ->appendFormButton(new Primary('Filtern', new Filter()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Info',
                                new Info('Fügen Sie hier alle Personen hinzu für die ein Account erstellt werden soll.')
                                .new Info('Die Filterung basiert immer auf den Schüler. Der anzulegende Account (Personengebunden)
                                wird durch Ihre Auswahl bestimmt (Schüler / Sorgeberechtigt).'),
                                Panel::PANEL_TYPE_SUCCESS)
                            , 6),
                        new LayoutColumn(new Well(
                            new Panel('Personensuche'.new Bold('Test')
                                , $FormStudent
                                , Panel::PANEL_TYPE_INFO)
                        ), 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn('', 6),
                        new LayoutColumn(
                            ( $Timeout === true
                                ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                : ''
                            ).
                            ( !$IsPost
                                ? new WarningMessage('Inhalt lädt nach der Filterung')
                                : ''
                            )
                            , 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                                new TableData($TableLeftContent, new Title('Personen für die Accounts erstellt werden sollen'),
                                    array('Exchange'   => '',
                                          'Salutation' => 'Anrede',
                                          'Name'       => 'Name',
                                          'Address'    => 'Adresse',
                                          'Year'       => 'Jahr',
                                          'Division'   => 'Klasse',
                                          'PersonList' => 'Beziehungs Person(en) '
                                    ), array(
                                        'order'                => array(array(2, 'asc')),
                                        'columnDefs'           => array(
                                            array('orderable' => false, 'width' => '3%', 'targets' => 0)
                                        ),
                                        'ExtensionRowExchange' => array(
                                            'Enabled' => true,
                                            'Url'     => '/Api/Setting/UserAccount/Exchange',
                                            'Handler' => array(
                                                'From' => 'glyphicon-minus-sign',
                                                'To'   => 'glyphicon-plus-sign',
                                                'All'  => 'TableRemoveAll'
                                            ),
                                            'Connect' => array(
                                                'From' => 'TableCurrent',
                                                'To'   => 'TableAvailable',
                                            )
                                        )
                                    )
                                )
                            , new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(), 'Alle entfernen', 'TableRemoveAll')
                            )
                            , 6),
                        new LayoutColumn(
                            new Layout(
                                new LayoutGroup(array(
                                    new LayoutRow(
                                        new LayoutColumn(
                                            array(
                                                new TableData($SearchTable, new Title(( $IsCustody
                                                    ? 'Sorgeberechtigte zu den gefilterten Schülern'
                                                    : 'Gefilterte Schüler' )),
                                                    array('Exchange'     => '',
                                                          'Salutation'   => 'Anrede',
                                                          'Name'         => 'Name',
                                                          'Address'      => 'Adresse',
                                                          'DivisionYear' => 'Jahr',
                                                          'Division'     => 'Klasse',
                                                          'PersonList'   => 'Beziehungs Person(en)'
                                                    ),
                                                    array(
                                                        'order'                => array(array(2, 'asc')),
                                                        'columnDefs'           => array(
                                                            array('orderable' => false, 'width' => '3%', 'targets' => 0)
                                                        ),
                                                        'ExtensionRowExchange' => array(
                                                            'Enabled' => true,
                                                            'Url'     => '/Api/Setting/UserAccount/Exchange',
                                                            'Handler' => array(
                                                                'From' => 'glyphicon-plus-sign',
                                                                'To'   => 'glyphicon-minus-sign',
                                                                'All'  => 'TableAddAll'
                                                            ),
                                                            'Connect' => array(
                                                                'From' => 'TableAvailable',
                                                                'To'   => 'TableCurrent',
                                                            ),
                                                        )
                                                    ))
                                            , new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll')
                                            )
                                        )
                                    )
                                ))
                            )
                            , 6)
                    ))
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param array $Result
     * @param bool  $IsCustody
     *
     * @return array|bool
     */
    private function getStudentTableByResult($Result, $IsCustody = false)
    {

        $TableSearch = array();
        if (!empty($Result)) {
            /** @var AbstractView[]|ViewDivisionStudent[] $Row */
            foreach ($Result as $Index => $Row) {
                $DataPerson = $Row[1]->__toArray();
                $tblDivisionStudent = $Row[2]->getTblDivisionStudent();

                $DataPerson['DivisionYear'] = new Container(new Small(new Muted('Gefiltertes Jahr:'))).new Container('-NA-');
                $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                /** @var TblDivisionStudent $tblDivisionStudent */
                if ($tblDivisionStudent) {
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        if (( $tblYear = $tblDivision->getServiceTblYear() )) {
                            $DataPerson['DivisionYear'] = new Container(new Small(new Muted('Gefiltertes Jahr:'))).new Container($tblYear->getName());
                        }
                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container($tblDivision->getDisplayName());
                    }
                }

                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);

                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));
                $DataPerson['PersonList'] = '';

                if ($tblPerson) {
                    if ($IsCustody) {

//                        if($tblPerson->getLastName() == 'Beimler'){
                        // Add Custody to List
                        $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                        if ($tblToPersonList) {
                            foreach ($tblToPersonList as $tblToPerson) {
                                if ($tblToPerson->getTblType()->getName() == 'Sorgeberechtigt') {
                                    $tblPersonCustody = $tblToPerson->getServiceTblPersonFrom();

                                    if ($tblPersonCustody) {
                                        /** @noinspection PhpUndefinedFieldInspection */
                                        $DataPerson['Exchange'] = new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                                            'PersonId' => $tblPersonCustody->getId()
                                        ));

                                        $DataPerson['Name'] = $tblPersonCustody->getLastFirstName();
                                        $DataPerson['Salutation'] = ( $tblPersonCustody->getSalutation() !== ''
                                            ? $tblPersonCustody->getSalutation()
                                            : new Small(new Muted('-NA-')) );
                                        $tblAddress = Address::useService()->getAddressByPerson($tblPersonCustody);
                                    }
                                }
                                /** @noinspection PhpUndefinedFieldInspection */
                                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                                    /** @noinspection PhpUndefinedFieldInspection */
                                    $DataPerson['Address'] = $tblAddress->getGuiString();
                                }
                                $DataPerson['PersonList'] = new Small(new Muted('-NA-'));

                                $DataPerson['PersonList'] = $tblPerson->getLastFirstName();


                                if (isset($tblPersonCustody)) {
                                    $DataPerson['TblPerson_Id'] = $tblPersonCustody->getId();
                                }

                                // ignore duplicated Person
                                if ($DataPerson['Name']) {
                                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                                    }
                                }
                            }
                        }
//                        }

                        continue;
                    } else {

                        /** @noinspection PhpUndefinedFieldInspection */
                        $DataPerson['Exchange'] = new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                            'PersonId' => $tblPerson->getId()
                        ));

                        $DataPerson['Name'] = $tblPerson->getLastFirstName();
                        $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                            ? $tblPerson->getSalutation()
                            : new Small(new Muted('-NA-')) );
                        $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
//                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                    }
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset($tblAddress) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }

                // Show Custody
                $tblToPersonList = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                if ($tblToPersonList) {
                    foreach ($tblToPersonList as $tblToPerson) {
                        if ($tblToPerson->getTblType()->getName() == 'Sorgeberechtigt') {
                            if ($DataPerson['PersonList'] == '') {
                                $tblPersonCustody = $tblToPerson->getServiceTblPersonFrom();
                                if ($tblPersonCustody) {
                                    $DataPerson['PersonList'] = $tblPersonCustody->getLastFirstName();
                                }
                            } else {
                                $tblPersonCustody = $tblToPerson->getServiceTblPersonFrom();
                                if ($tblPersonCustody) {
                                    $DataPerson['PersonList'] .= '; '.$tblPersonCustody->getLastFirstName();
                                }
                            }
                        }
                    }
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $TableSearch)) {
                        $TableSearch[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }

        return ( !empty($TableSearch) ? $TableSearch : false );
    }

    /**
     * @return Form
     */
    private function formFilterStudent()
    {

        $GroupList = array();
        $tblGroup = Group::useService()->getGroupByMetaTable('STUDENT');
        if ($tblGroup) {
            $GroupList[] = $tblGroup;
        }
        $tblGroupCustody = Group::useService()->getGroupByMetaTable('CUSTODY');
        if ($tblGroupCustody) {
            $GroupList[] = $tblGroupCustody;
        }
        $LevelList = array();
        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as $tblLevel) {
                if ($tblLevel->getName() !== '') {
                    $LevelList[] = $tblLevel;
                }
            }
        }

        $FormGroup = array();
        // Filter
        $FormGroup[] = new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new SelectBox('FilterGroup[TblGroup_Id]', 'Gruppe: Name', array('Name' => $GroupList))
                    , 6),
                new FormColumn(
                    new SelectBox('FilterYear[TblYear_Id]', 'Bildung: Schuljahr',
                        array('{{Name}} {{Description}}' => Term::useService()->getYearAll()))
                    , 6)
            )),
            new FormRow(array(
                new FormColumn(
                    new SelectBox('FilterStudent[TblLevel_Id]', 'Klasse: Stufe',
                        array('{{ Name }} {{ serviceTblType.Name }}' => $LevelList))
                    , 6),
                new FormColumn(
                    new AutoCompleter('FilterStudent[TblDivision_Name]', 'Klasse: Gruppe', '',
                        array('Name' => Division::useService()->getDivisionAll()))
                    , 6),
            )),
            new FormRow(array(
                new FormColumn(
                    new TextField('FilterPerson[TblPerson_FirstName]', 'Vorname', 'Person: Vorname')
                    , 6),
                new FormColumn(
                    new TextField('FilterPerson[TblPerson_LastName]', 'Nachname', 'Person: Nachname')
                    , 6),
            ))
        ));
        // POST StandardGroup (first Visit)
        $Global = $this->getGlobal();
        if (!isset($Global->POST['FilterGroup']['TblGroup_Id'])) {
            $Global->POST['FilterGroup']['TblGroup_Id'] = $tblGroup->getId();
            $Global->savePost();
        }
        return new Form(
            $FormGroup
        );
    }
}