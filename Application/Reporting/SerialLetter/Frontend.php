<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 19.04.2016
 * Time: 08:10
 */

namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Exchange;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $SerialLetter
     *
     * @return Stage
     */
    public function frontendSerialLetter($SerialLetter = null)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Übersicht');

        $tblSerialLetterAll = SerialLetter::useService()->getSerialLetterAll();

        if ($tblSerialLetterAll) {
            foreach ($tblSerialLetterAll as &$tblSerialLetter) {
                $tblSerialLetter->Option =
                    (new Standard(new Edit(), '/Reporting/SerialLetter/Edit', null,
                        array('Id' => $tblSerialLetter->getId()), 'Bearbeiten'))
                    . (new Standard(new Remove(), '/Reporting/SerialLetter/Destroy', null,
                        array('Id' => $tblSerialLetter->getId()), 'Löschen'))
                    . (new Standard(new PersonIcon(), '/Reporting/SerialLetter/Person/Select', null,
                        array('Id' => $tblSerialLetter->getId()), 'Peresonen auswählen'))
                    . (new Standard(new ListingTable(), '/Reporting/SerialLetter/Select', null,
                        array('Id' => $tblSerialLetter->getId()), 'Addressen auswählen'))
                    . (new Standard(new EyeOpen(), '/Reporting/SerialLetter/Export', null,
                        array('Id' => $tblSerialLetter->getId()),
                        'Addressliste für Serienbriefe anzeigen und herunterladen'));
            }
        }

        $Form = $this->formSerialLetter()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblSerialLetterAll, null, array(
                                'Name' => 'Name',
                                'Description' => 'Beschreibung',
                                'Option' => '',
                            ))
                        ))
                    ))
                ), new Title(new ListingTable() . ' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Well(SerialLetter::useService()->createSerialLetter($Form, $SerialLetter))
                        ))
                    ))
                ), new Title(new PlusSign() . ' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSerialLetter()
    {

        $tblGroupAll = Group::useService()->getGroupAll();
        // Gruppe "Alle" aus der Auswahl entfernen
        if ($tblGroupAll) {
            /** @var TblGroup $tblGroup */
            $tblGroup = current($tblGroupAll);
            if ($tblGroup->getMetaTable() == 'COMMON') {
                array_shift($tblGroupAll);
            }
        }

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('SerialLetter[Name]', 'Name', 'Name'), 4
                ),
                new FormColumn(
                    new TextField('SerialLetter[Description]', 'Beschreibung', 'Beschreibung'), 8
                )
            ))
        )));
    }

    /**
     * @param null $Id
     * @param null $SerialLetter
     *
     * @return Stage|string
     */
    public function frontendSerialLetterEdit($Id = null, $SerialLetter = null)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Bearbeiten');

        if (($tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id))) {
            if ($SerialLetter == null) {
                $Global = $this->getGlobal();
                $Global->POST['SerialLetter']['Name'] = $tblSerialLetter->getName();
                $Global->POST['SerialLetter']['Description'] = $tblSerialLetter->getDescription();
                $Global->savePost();
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Name', $tblSerialLetter->getName() . ' '
                            . new Small(new Muted($tblSerialLetter->getDescription())), Panel::PANEL_TYPE_INFO), 8
                    ),
                    new LayoutColumn(
                        new Well(
                            SerialLetter::useService()->updateSerialLetter(
                                $this->formSerialLetter()->appendFormButton(new Primary('Speichern', new Save())),
                                $tblSerialLetter, $SerialLetter
                            )
                        )
                    )
                ))))
            );

        } else {
            return $Stage
            . new Danger('Serienbrief nicht gefunden', new Exclamation())
            . new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param null|int $Id
     * @param null|array $FilterGroup
     * @param null|array $FilterStudent
     * @param null|array $FilterPerson
     *
     * @return Stage|string
     */
    public function frontendSerialLetterPersonSelected($Id = null, $FilterGroup = null, $FilterStudent = null, $FilterPerson = null)
    {

        $Stage = new Stage('Personen für Serienbriefe', 'Auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = ($Id == null ? false : SerialLetter::useService()->getSerialLetterById($Id));
        if (!$tblSerialLetter) {
            return $Stage . new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $Filter = false;

        if (
            $FilterGroup === null
            && $FilterStudent === null
            && $FilterPerson === null
        ) {
            $FilterGroup['TblGroup_Name'] = 'Schüler';
            $Global = $this->getGlobal();
            $Global->POST['FilterGroup']['TblGroup_Name'] = 'Schüler';
            $Global->savePost();
        };

        if ($FilterGroup) {
            $Filter = $FilterGroup;

            $Pile = new Pile();
            $Pile->addPile((new ViewPeopleGroupMember())->getViewService(), new ViewPeopleGroupMember(), null, 'TblMember_serviceTblPerson');
            $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(), ViewPerson::TBL_PERSON_ID, null);
        }
        if ($FilterStudent) {
            $Filter = $FilterStudent;

            $Pile = new Pile();
            $Pile->addPile((new ViewDivisionStudent())->getViewService(), new ViewDivisionStudent(), null, 'TblDivisionStudent_serviceTblPerson');
            $Pile->addPile((new ViewPerson())->getViewService(), new ViewPerson(), ViewPerson::TBL_PERSON_ID, null);
        }


        $Result = array();
        $Timeout = null;
        if ($Filter && isset($Pile)) {
            array_walk($Filter, function (&$Input) {

                if (!empty($Input)) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $Filter = array_filter($Filter);

            if ($FilterPerson) {
                array_walk($FilterPerson, function (&$Input) {

                    if (!empty($Input)) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterPerson = array_filter($FilterPerson);
            } else {
                $FilterPerson = array();
            }

            $Result = $Pile->searchPile(array(
                $Filter,
                $FilterPerson
            ));

            $Timeout = $Pile->isTimeout();
        }

        /**
         * @var int $Index
         * @var ViewPeopleGroupMember[]|ViewDivisionStudent[]|ViewPerson[] $Row
         */
        $SearchResult = array();
        foreach ($Result as $Index => $Row) {
            /** @var array $DataPerson */
            $DataPerson = $Row[1]->__toArray();
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Exchange'] = (string)new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                'Id' => $Id,
                'PersonId' => $DataPerson['TblPerson_Id']
            ));
            $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Name'] = $tblPerson->getFullName();
            $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
            if ($tblAddress) {
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = $tblAddress->getGuiString();
            }

            if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
            }
        }

        $tblPersonAll = $SearchResult;

        $tblPersonList = SerialLetter::useService()->getPersonBySerialLetter($tblSerialLetter);
        if (!empty($tblPersonList) && !empty($tblPersonAll)) {

            $tblPersonAll = array_udiff($tblPersonAll, $tblPersonList,
                function ($tblPersonA, $tblPersonB) {

                    if ($tblPersonA instanceof TblPerson && !$tblPersonB instanceof TblPerson) {
                        return $tblPersonA->getId() - $tblPersonB['TblPerson_Id'];
                    }
                    if (!$tblPersonA instanceof TblPerson && $tblPersonB instanceof TblPerson) {
                        return $tblPersonA['TblPerson_Id'] - $tblPersonB->getId();
                    }
                    return 0;
                }
            );
        }

        if ($tblPersonList) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblPersonList, function (TblPerson &$tblPerson) use ($Id) {

                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Exchange = new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(
                    'Id' => $Id,
                    'PersonId' => $tblPerson->getId()
                ));
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Name = $tblPerson->getFullName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Address = new WarningMessage('Keine Adresse hinterlegt!');
                if ($tblAddress) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->Address = $tblAddress->getGuiString();
                }

            });
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title('Personen', 'Zugewiesen'),

                            new Layout(
                                new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(

                                            new Panel('Serienbief',
                                                $tblSerialLetter->getName() . ' ' . new Small(new Muted($tblSerialLetter->getDescription()))
                                                , Panel::PANEL_TYPE_SUCCESS, new PullRight(new Label('Enthält ' . count($tblPersonList) . ' Person(en)', Label::LABEL_TYPE_INFO))
                                            ),
                                            new TableData($tblPersonList, null,
                                                array('Exchange' => '',
                                                    'Name' => 'Name',
                                                    'Address' => 'Adresse'), array(
                                                    'order' => array(array(1, 'asc')),
                                                    'columnDefs' => array(
                                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                                    ),
                                                    'ExtensionRowExchange' => array(
                                                        'Enabled' => true,
                                                        'Url' => '/Api/Reporting/SerialLetter/Exchange',
                                                        'Handler' => array(
                                                            'From' => 'glyphicon-minus-sign',
                                                            'To' => 'glyphicon-plus-sign',
                                                        ),
                                                        'Connect' => array(
                                                            'From' => 'TableCurrent',
                                                            'To' => 'TableAvailable',
                                                        )
                                                    )
                                                )
                                            )
                                        ), 12),
                                    ))
                                ))
                            )
                        ), 6),

                        new LayoutColumn(array(
                            new Title('Personen', 'Verfügbar'),

                            new Layout(
                                new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            new Panel(new Search() . ' Personen-Suche nach ' . new Bold('Personengruppe'), array(
                                                new Form(
                                                    new FormGroup(
                                                        new FormRow(array(
                                                            new FormColumn(array(
                                                                new AutoCompleter('FilterGroup[TblGroup_Name]', 'Gruppe: Name', 'Gruppe: Name', array('Name' => Group::useService()->getGroupAll())),
                                                            ), 6),
                                                            new FormColumn(array(
                                                                new TextField('FilterPerson[' . ViewPerson::TBL_PERSON_FIRST_NAME . ']', 'Person: Vorname', 'Person: Vorname'),
                                                                new TextField('FilterPerson[' . ViewPerson::TBL_PERSON_LAST_NAME . ']', 'Person: Nachname', 'Person: Nachname')
                                                            ), 6)
                                                        ))
                                                    )
                                                    , new Primary('in Gruppen suchen'))
                                            ), Panel::PANEL_TYPE_INFO)
                                            , 6
                                        ),
                                        new LayoutColumn(
                                            new Panel(new Search() . ' Personen-Suche nach ' . new Bold('Klasse / Schüler'), array(
                                                new Form(
                                                    new FormGroup(
                                                        new FormRow(array(
                                                            new FormColumn(array(
                                                                new TextField('FilterStudent[TblLevel_Name]', 'Klasse: Stufe', 'Klasse: Stufe'),
                                                                new TextField('FilterStudent[TblDivision_Name]', 'Klasse: Gruppe', 'Klasse: Gruppe'),
                                                            ), 6),
                                                            new FormColumn(array(
                                                                new TextField('FilterPerson[' . ViewPerson::TBL_PERSON_FIRST_NAME . ']', 'Person: Vorname', 'Person: Vorname'),
                                                                new TextField('FilterPerson[' . ViewPerson::TBL_PERSON_LAST_NAME . ']', 'Person: Nachname', 'Person: Nachname')
                                                            ), 6)
                                                        ))
                                                    )
                                                    , new Primary('in Klassen suchen'))
                                            ), Panel::PANEL_TYPE_INFO)
                                            , 6
                                        ),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            ($Timeout === true
                                                ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                                : ''
                                            )
                                        )
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new TableData($tblPersonAll, null,
                                                array('Exchange' => ' ',
                                                    'Name' => 'Name',
                                                    'Address' => 'Adresse'), array(
                                                    'order' => array(array(1, 'asc')),
                                                    'columnDefs' => array(
                                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                                    ),
                                                    'ExtensionRowExchange' => array(
                                                        'Enabled' => true,
                                                        'Url' => '/Api/Reporting/SerialLetter/Exchange',
                                                        'Handler' => array(
                                                            'From' => 'glyphicon-plus-sign',
                                                            'To' => 'glyphicon-minus-sign'
                                                        ),
                                                        'Connect' => array(
                                                            'From' => 'TableAvailable',
                                                            'To' => 'TableCurrent',
                                                        ),
                                                    )
                                                )
                                            )
                                        ), 12)
                                    ))
                                ))
                            )
                        ), 6)
                    )),
                ))
            )
        );

        return $Stage;
    }

    /**
     * @param null $Id
     * @param null $Check
     *
     * @return Stage|string
     */
    public function frontendSerialLetterSelected(
        $Id = null,
        $Check = null
    )
    {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));

        if (($tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id))) {

            $dataList = array();
            $columnList = array();

            $tblGroup = $tblSerialLetter->getServiceTblGroup();
            if ($tblGroup) {
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
                if ($tblPersonList) {
                    $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');

                    $columnList = array(
                        'Number' => 'Nr.',
                        'Person' => 'Person',
                        'Addresses' => 'Adressen'
                    );

                    // Set Global Post
                    $Global = $this->getGlobal();
                    if ($Check == null) {
                        $tblAddressPersonAll = SerialLetter::useService()->getAddressPersonAllBySerialLetter($tblSerialLetter);
                        if ($tblAddressPersonAll) {
                            foreach ($tblAddressPersonAll as $tblAddressPerson) {
                                if ($tblAddressPerson->getServiceTblPerson()
                                    && $tblAddressPerson->getServiceTblPersonToAddress()
                                    && $tblAddressPerson->getServiceTblToPerson()
                                ) {
                                    $Global->POST['Check']
                                    [$tblAddressPerson->getServiceTblPerson()->getId()]
                                    [$tblAddressPerson->getServiceTblToPerson()->getId()]
                                    ['Address'] = 1;

                                    $Global->POST['Check']
                                    [$tblAddressPerson->getServiceTblPerson()->getId()]
                                    [$tblAddressPerson->getServiceTblToPerson()->getId()]
                                    ['Salutation'] = $tblAddressPerson->getServiceTblSalutation() ? $tblAddressPerson->getServiceTblSalutation()->getId() : 0;
                                }
                            }
                        }
                    }
                    $Global->savePost();

                    $personCount = 0;
                    /** @var TblPerson $tblPerson */
                    foreach ($tblPersonList as $tblPerson) {
                        $dataList[$tblPerson->getId()]['Number'] = ++$personCount;
                        $dataList[$tblPerson->getId()]['Person'] = $tblPerson->getLastFirstName();

                        $tblSalutationAll = Person::useService()->getSalutationAll();
                        if ($tblSalutationAll) {
                            $tblSalutation = new TblSalutation('Familie');
                            $tblSalutation->setId(TblAddressPerson::SALUTATION_FAMILY);
                            $tblSalutationAll['Family'] = $tblSalutation;
                        }

                        $subDataList = array();
                        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
                        if ($tblAddressToPersonList) {
                            foreach ($tblAddressToPersonList as $tblToPerson) {
                                $subDataList[] = array(
                                    'Person' => $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getLastFirstName() : '',
                                    'Relationship' => '',
                                    'Address' => new CheckBox('Check[' . $tblPerson->getId() . '][' . $tblToPerson . '][Address]',
                                        '&nbsp; ' . $tblToPerson->getTblAddress()->getGuiString(), 1),
                                    'Salutation' => new SelectBox('Check[' . $tblPerson->getId() . '][' . $tblToPerson . '][Salutation]',
                                        '', array('Salutation' => $tblSalutationAll))
                                );
                            }
                        }

                        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
                        if ($tblRelationshipAll) {
                            foreach ($tblRelationshipAll as $tblRelationship) {
                                if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {
                                    if ($tblRelationship->getServiceTblPersonTo()->getId() == $tblPerson->getId()) {
                                        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonFrom());
                                    } else {
                                        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonTo());
                                    }
                                    if ($tblAddressToPersonList) {
                                        foreach ($tblAddressToPersonList as $tblToPerson) {
                                            $subDataList[] = array(
                                                'Person' => $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getLastFirstName() : '',
                                                'Relationship' => $tblRelationship->getTblType()->getName(),
                                                'Address' => new CheckBox('Check[' . $tblPerson->getId() . '][' . $tblToPerson . '][Address]',
                                                    '&nbsp; ' . $tblToPerson->getTblAddress()->getGuiString(), 1),
                                                'Salutation' => new SelectBox('Check[' . $tblPerson->getId() . '][' . $tblToPerson . '][Salutation]',
                                                    '', array('Salutation' => $tblSalutationAll))
                                            );
                                        }
                                    }
                                }
                            }
                        }

                        if (empty($subDataList)) {
                            $dataList[$tblPerson->getId()]['Addresses'] = new WarningMessage(
                                'Keine Adressen hinterlegt!', new Exclamation()
                            );
                        } else {
                            $dataList[$tblPerson->getId()]['Addresses'] = new TableData(
                                $subDataList, null,
                                array(
                                    'Person' => 'Person',
                                    'Relationship' => 'Beziehung',
                                    'Address' => 'Adresse',
                                    'Salutation' => 'Anrede'
                                ), null
                            );
                        }
                    }
                }
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Name', $tblSerialLetter->getName() . ' '
                            . new Small(new Muted($tblSerialLetter->getDescription())), Panel::PANEL_TYPE_INFO), 8
                    ),
                    new LayoutColumn(
                        new Panel('Gruppe',
                            $tblSerialLetter->getServiceTblGroup() ? $tblSerialLetter->getServiceTblGroup()->getName() : '',
                            Panel::PANEL_TYPE_INFO), 4
                    ),
                    new LayoutColumn(
                        SerialLetter::useService()->setPersonAddressSelection(
                            new Form(new FormGroup(new FormRow(
                                new FormColumn(array(
                                        new TableData($dataList, null, $columnList, null)
                                    ,
                                        new Primary('Speichern', new Save())
                                    )
                                )))), $tblSerialLetter, $Check
                        )
                    )
                ))))
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendSerialLetterExport(
        $Id = null
    )
    {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen herunterladen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));

        if (($tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id))) {

            $dataList = array();
            $columnList = array(
                'Number' => 'Nr.',
                'Person' => 'Person der Gruppe',
                'Salutation' => 'Anrede',
                'PersonToAddress' => 'Adressat',
                'Address' => 'Adresse',
            );

            $countAddresses = 0;
            $tblGroup = $tblSerialLetter->getServiceTblGroup();
            if ($tblGroup) {
                $count = 0;
                $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
                if ($tblPersonList) {
                    $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName');
                    /** @var TblPerson $tblPerson */
                    foreach ($tblPersonList as $tblPerson) {
                        $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                            $tblPerson);
                        if ($tblAddressPersonAllByPerson) {
                            foreach ($tblAddressPersonAllByPerson as $tblAddressPerson) {
                                if ($tblAddressPerson->getServiceTblToPerson()
                                    && $tblAddressPerson->getServiceTblToPerson()->getTblAddress()
                                ) {
                                    $countAddresses++;
                                }
                                $dataList[] = array(
                                    'Number' => ++$count,
                                    'Person' => ($tblAddressPerson->getServiceTblPerson()
                                        ? $tblAddressPerson->getServiceTblPerson()->getLastFirstName()
                                        : new Warning(new Exclamation() . ' Person nicht gefunden.')),
                                    'PersonToAddress' => ($tblAddressPerson->getServiceTblPersonToAddress()
                                        ? $tblAddressPerson->getServiceTblPersonToAddress()->getLastFirstName()
                                        : new Warning(new Exclamation() . ' Person nicht gefunden.')),
                                    'Address' => ($tblAddressPerson->getServiceTblToPerson()
                                        ? $tblAddressPerson->getServiceTblToPerson()->getTblAddress()->getGuiString()
                                        : new Warning(new Exclamation() . ' Adresse nicht gefunden.')),
                                    'Salutation' => $tblAddressPerson->getServiceTblSalutation()
                                        ? $tblAddressPerson->getServiceTblSalutation()->getSalutation()
                                        : new Warning(new Exclamation() . ' Keine Anrede hinterlegt.')
                                );
                            }
                        } else {
                            $dataList[] = array(
                                'Number' => ++$count,
                                'Person' => $tblPerson->getLastFirstName(),
                                'PersonToAddress' => new Warning(new Exclamation() . ' Keine Person mit Adresse hinterlegt.'),
                                'Address' => '',
                                'Salutation' => ''
                            );
                        }
                    }
                }
            }

            if ($countAddresses > 0) {
                $Stage->addButton(
                    new \SPHERE\Common\Frontend\Link\Repository\Primary('Herunterladen',
                        '/Api/Reporting/SerialLetter/Download', new Download(),
                        array('Id' => $tblSerialLetter->getId()))
                );
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            $countAddresses == 0
                                ? new LayoutColumn(
                                new WarningMessage('Keine Adressen ausgewählt.',
                                    new Exclamation())
                            )
                                : null,
                            new LayoutColumn(
                                new Panel('Name', $tblSerialLetter->getName() . ' '
                                    . new Small(new Muted($tblSerialLetter->getDescription())), Panel::PANEL_TYPE_INFO),
                                8
                            ),
                            new LayoutColumn(
                                new Panel('Gruppe',
                                    $tblSerialLetter->getServiceTblGroup() ? $tblSerialLetter->getServiceTblGroup()->getName() : '',
                                    Panel::PANEL_TYPE_INFO), 4
                            ),
                            new LayoutColumn(
                                new TableData(
                                    $dataList, null, $columnList
                                )
                            )
                        ))
                    )
                )
            );

            return $Stage;
        } else {
            return $Stage . new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param $Id
     * @param bool|false $Confirm
     * @return Stage
     */
    public function frontendSerialLetterDestroy($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Löschen');
        if ($Id) {
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft())
            );
            $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
            if (!$tblSerialLetter) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger(new Ban() . ' Die Adressliste für Serienbriefe konnte nicht gefunden werden.'),
                            new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Adressliste für Serienbriefe', new Bold($tblSerialLetter->getName()) .
                                ($tblSerialLetter->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    . new Muted(new Small(new Small($tblSerialLetter->getDescription()))) : ''),
                                Panel::PANEL_TYPE_INFO),
                            new Panel(new Question() . ' Diese Adressliste für Serienbriefe wirklich löschen?', array(
                                $tblSerialLetter->getName() . ' ' . $tblSerialLetter->getDescription()
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Reporting/SerialLetter/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                . new Standard(
                                    'Nein', '/Reporting/SerialLetter', new Disable()
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                (SerialLetter::useService()->destroySerialLetter($tblSerialLetter)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success() . ' Die Adressliste für Serienbriefe wurde gelöscht')
                                    : new Danger(new Ban() . ' Die Adressliste für Serienbriefe konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_SUCCESS)
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban() . ' Daten nicht abrufbar.'),
                        new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}
