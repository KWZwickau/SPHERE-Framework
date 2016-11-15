<?php

namespace SPHERE\Application\Reporting\SerialLetter;

use SPHERE\Application\Contact\Address\Address;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivisionStudent;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivisionStudent;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Person\Service\Entity\ViewPerson;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblAddressPerson;
use SPHERE\Application\Reporting\SerialLetter\Service\Entity\TblSerialLetter;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
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
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Person as PersonIcon;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Search;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Icon\Repository\View;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Label;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTab;
use SPHERE\Common\Frontend\Layout\Structure\LayoutTabs;
use SPHERE\Common\Frontend\Link\Repository\Exchange;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning as WarningMessage;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
use SPHERE\Common\Frontend\Text\Repository\Warning;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Filter\Link\Pile;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter\StringGermanOrderSorter;

/**
 * Class Frontend
 * @package SPHERE\Application\Reporting\SerialLetter
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null   $SerialLetter
     * @param string $TabActive
     * @param null   $FilterGroup
     * @param null   $FilterPerson
     * @param null   $FilterStudent
     * @param null   $FilterYear
     *
     * @return Stage
     */
    public function frontendSerialLetter(
        $SerialLetter = null,
        $TabActive = 'STATIC',
        $FilterGroup = null,
        $FilterPerson = null,
        $FilterStudent = null,
        $FilterYear = null
    )
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Übersicht');

        $tblSerialLetterAll = SerialLetter::useService()->getSerialLetterAll();
        $tblFilterCategoryAll = SerialLetter::useService()->getFilterCategoryAll();

        //create Tabs
        $LayoutTabs[] = new LayoutTab('Serienbrief Statisch', 'STATIC');
        if (!empty( $LayoutTabs ) && $TabActive === 'PERSON') {
            $LayoutTabs[0]->setActive();
        }

        if ($tblFilterCategoryAll) {
            foreach ($tblFilterCategoryAll as $tblFilterCategory) {
                // Create Tabs
                $LayoutTabs[] = new LayoutTab('Serienbrief '.$tblFilterCategory->getName(), 'DYNAMIC'.$tblFilterCategory->getId());
            }
        }

        $TableContent = array();
        if ($tblSerialLetterAll) {
            array_walk($tblSerialLetterAll, function (TblSerialLetter $tblSerialLetter) use (&$TableContent) {
                $Item['Name'] = $tblSerialLetter->getName();
                $Item['Description'] = $tblSerialLetter->getDescription();
                $Item['Option'] =
                    ( new Standard('', '/Reporting/SerialLetter/Edit', new Edit(),
                        array('Id' => $tblSerialLetter->getId()), 'Bearbeiten') )
                    .( new Standard('', '/Reporting/SerialLetter/Destroy', new Remove(),
                        array('Id' => $tblSerialLetter->getId()), 'Löschen') )
                    .( new Standard('', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                        array('Id' => $tblSerialLetter->getId()), 'Personen auswählen') )
                    .( new Standard('', '/Reporting/SerialLetter/Address', new Setup(),
                        array('Id' => $tblSerialLetter->getId()), 'Addressen auswählen') )
                    .( new Standard('', '/Reporting/SerialLetter/Export', new View(),
                        array('Id' => $tblSerialLetter->getId()),
                        'Addressliste für Serienbriefe anzeigen und herunterladen') );
                array_push($TableContent, $Item);
            });
        }

        $FormSerialLetter = $this->formSerialLetter()
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

//        // Create Tabs
//        $LayoutTabs[] = new LayoutTab('Serienbrief Statisch', 'STATIC');
//        $LayoutTabs[] = new LayoutTab('Serienbrief Dynamisch', 'DYNAMIC');
//        if (!empty( $LayoutTabs ) && $TabActive === 'PERSON') {
//            $LayoutTabs[0]->setActive();
//        }

        $Timeout = null;
        $SearchResult = array();
        $IsFilter = false;
        if (( isset( $FilterGroup['TblGroup_Name'] ) && !empty( $FilterGroup['TblGroup_Name'] ) )
            || ( isset( $FilterYear['TblYear_Name'] ) && !empty( $FilterYear['TblYear_Name'] ) )
            || ( isset( $FilterStudent['TblLevel_Name'] ) && !empty( $FilterStudent['TblLevel_Name'] ) )
            || ( isset( $FilterStudent['TblDivision_Name'] ) && !empty( $FilterStudent['TblDivision_Name'] ) )
        ) {
            $IsFilter = true;

            // Database Join with foreign Key
            $Pile = new Pile(Pile::JOIN_TYPE_OUTER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
            );
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
            );
            $Pile->addPile(( new ViewDivisionStudent() )->getViewService(), new ViewDivisionStudent(),
                ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
            );
            $Pile->addPile(( new ViewYear() )->getViewService(), new ViewYear(),
                ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
            );

            if ($FilterGroup) {
                // Preparation FilterGroup
                array_walk($FilterGroup, function (&$Input) {

                    if (!is_array($Input)) {
                        if (!empty( $Input )) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterGroup = array_filter($FilterGroup);
            } else {
                $FilterGroup = array();
            }
            // Preparation FilterPerson
            if ($FilterPerson) {
                array_walk($FilterPerson, function (&$Input) {

                    if (!is_array($Input)) {
                        if (!empty( $Input )) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterPerson = array_filter($FilterPerson);
            } else {
                $FilterPerson = array();
            }
            // Preparation $FilterStudent
            if ($FilterStudent) {
                array_walk($FilterStudent, function (&$Input) {
                    if (!is_array($Input)) {
                        if (!empty( $Input )) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterStudent = array_filter($FilterStudent);
            } else {
                $FilterStudent = array();
            }
            // Preparation $FilterYear
            if ($FilterYear) {
                array_walk($FilterYear, function (&$Input) {
                    if (!is_array($Input)) {
                        if (!empty( $Input )) {
                            $Input = explode(' ', $Input);
                            $Input = array_filter($Input);
                        } else {
                            $Input = false;
                        }
                    }
                });
                $FilterYear = array_filter($FilterYear);
            } else {
                $FilterYear = array();
            }

            $Result = $Pile->searchPile(array(
                0 => $FilterGroup,
                1 => $FilterPerson,
                2 => $FilterStudent,
                3 => $FilterYear
            ));
            // get Timeout status
            $Timeout = $Pile->isTimeout();

            foreach ($Result as $Index => $Row) {
                /** @var array $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                $tblDivisionStudent = $Row[2]->getTblDivisionStudent();
                /** @var TblDivisionStudent $tblDivisionStudent */
                if ($tblDivisionStudent) {
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        if (strlen($tblDivision->getTblLevel()->getName()) == 1) {
                            $DivisionName = '0'.$tblDivision->getDisplayName();
                        } else {
                            $DivisionName = $tblDivision->getDisplayName();
                        }

                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container($DivisionName);
                    } else {
                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                    }
                } else {
                    $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                }

                $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Name'] = false;
                $DataPerson['Salutation'] = new Small(new Muted('-NA-'));

                if ($tblPerson) {
                    $DataPerson['Name'] = $tblPerson->getLastFirstName();
                    $DataPerson['Salutation'] = ( $tblPerson->getSalutation() !== ''
                        ? $tblPerson->getSalutation()
                        : new Small(new Muted('-NA-')) );
                    $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                    $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                }
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
                if (isset( $tblAddress ) && $tblAddress && $DataPerson['Name']) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $DataPerson['Address'] = $tblAddress->getGuiString();
                }
                $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
                if (isset( $tblStudent ) && $tblStudent && $DataPerson['Name']) {
                    $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
                }

                // ignore duplicated Person
                if ($DataPerson['Name']) {
                    if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                        $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                    }
                }
            }
        }

        if (isset( $FilterGroup['TblGroup_Name'] ) && !empty( $FilterGroup['TblGroup_Name'] )) {
            $FilterGroup['TblGroup_Name'] = implode(' ', $FilterGroup['TblGroup_Name']);
        }
        if (isset( $FilterYear['TblYear_Name'] ) && !empty( $FilterYear['TblYear_Name'] )) {
            $FilterYear['TblYear_Name'] = implode(' ', $FilterYear['TblYear_Name']);
        }
        if (isset( $FilterStudent['TblLevel_Name'] ) && !empty( $FilterStudent['TblLevel_Name'] )) {
            $FilterStudent['TblLevel_Name'] = implode(' ', $FilterStudent['TblLevel_Name']);
        }
        if (isset( $FilterStudent['TblDivision_Name'] ) && !empty( $FilterStudent['TblDivision_Name'] )) {
            $FilterStudent['TblDivision_Name'] = implode(' ', $FilterStudent['TblDivision_Name']);
        }

        $FormSerialLetterDynamic =
            new Form(new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new TextField('SerialLetter[Name]', 'Name', 'Name'), 4
                    ),
                    new FormColumn(
                        new TextField('SerialLetter[Description]', 'Beschreibung', 'Beschreibung'), 8
                    )
                )),
            )), null, new Route(__NAMESPACE__.''),
                array('TabActive'     => 'DYNAMIC',
                      'FilterGroup'   => $FilterGroup,
                      'FilterStudent' => $FilterStudent,
                      'FilterYear'    => $FilterYear
                )
            );
        $FormSerialLetterDynamic
            ->appendFormButton(new Primary('Speichern', new Save()))
            ->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert');

        switch ($TabActive) {
            case 'STATIC':
                $MetaTable = new Panel(new PlusSign().' Serienbreif anlegen '
                    , array(new Well($FormSerialLetter)), Panel::PANEL_TYPE_INFO);
                break;
            case 'DYNAMIC1':

                $MetaTable = new Panel(new Search().' Personen-Filterung'
                        , array(new Well($this->formFilterPersonGroup())), Panel::PANEL_TYPE_INFO)
                    .new Layout(
                        new LayoutGroup(array(
                            new LayoutRow(
                                new LayoutColumn(
                                    ( $Timeout === true
                                        ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                        : ''
                                    )
                                )
                            ),
                            new LayoutRow(
                                new LayoutColumn(
                                    ( $IsFilter
                                        ? new TableData($SearchResult, new \SPHERE\Common\Frontend\Table\Repository\Title('Vorschau'),
                                            array('Salutation'    => 'Anrede',
                                                  'Name'          => 'Name',
                                                  'Address'       => 'Adresse',
                                                  'Division'      => 'Klasse',
                                                  'StudentNumber' => 'Schüler-Nr.'
                                            ),
                                            array(
                                                'order'                => array(array(1, 'asc')),
                                                'columnDefs'           => array(
                                                    array('orderable' => false, 'width' => '3%', 'targets' => 0)
                                                ),
                                                'ExtensionRowExchange' => array(
                                                    'Enabled' => true,
                                                    'Url'     => '/Api/Reporting/SerialLetter/Exchange',
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
                                            )
                                        )
                                        : new WarningMessage('Bitte tragen Sie etwas in den Filter ein') )
                                )
                            )
                        ))
                    )
                    .new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    ( $IsFilter
                                        ? new Panel(new PlusSign().' Serienbreif anlegen '
                                            , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic, $SerialLetter)))
                                            , Panel::PANEL_TYPE_INFO)
                                        : '' )
                                )
                            )
                        )
                    );
                break;
            case 'DYNAMIC2':

                $MetaTable = new Panel(new Search().' Personen-Filterung'
                        , array(new Well($this->formFilterStudent())), Panel::PANEL_TYPE_INFO)
                    .new Layout(
                        new LayoutGroup(array(
                            new LayoutRow(
                                new LayoutColumn(
                                    ( $Timeout === true
                                        ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                        : ''
                                    )
                                )
                            ),
                            new LayoutRow(
                                new LayoutColumn(
                                    ( $IsFilter
                                        ? new TableData($SearchResult, new \SPHERE\Common\Frontend\Table\Repository\Title('Vorschau'),
                                            array('Salutation'    => 'Anrede',
                                                  'Name'          => 'Name',
                                                  'Address'       => 'Adresse',
                                                  'Division'      => 'Klasse',
                                                  'StudentNumber' => 'Schüler-Nr.'
                                            ),
                                            array(
                                                'order'                => array(array(1, 'asc')),
                                                'columnDefs'           => array(
                                                    array('orderable' => false, 'width' => '3%', 'targets' => 0)
                                                ),
                                                'ExtensionRowExchange' => array(
                                                    'Enabled' => true,
                                                    'Url'     => '/Api/Reporting/SerialLetter/Exchange',
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
                                            )
                                        )
                                        : new WarningMessage('Bitte tragen Sie etwas in den Filter ein') )
                                )
                            )
                        ))
                    )
                    .new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    ( $IsFilter
                                        ? new Panel(new PlusSign().' Serienbreif anlegen '
                                            , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic, $SerialLetter)))
                                            , Panel::PANEL_TYPE_INFO)
                                        : '' )
                                )
                            )
                        )
                    );
                break;
            case 'DYNAMIC3':

                $MetaTable = new Panel(new Search().' Personen-Filterung'
                        , array(new Well($this->formFilterProspect())), Panel::PANEL_TYPE_INFO)
                    .new Layout(
                        new LayoutGroup(array(
                            new LayoutRow(
                                new LayoutColumn(
                                    ( $Timeout === true
                                        ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                        : ''
                                    )
                                )
                            ),
                            new LayoutRow(
                                new LayoutColumn(
                                    ( $IsFilter
                                        ? new TableData($SearchResult, new \SPHERE\Common\Frontend\Table\Repository\Title('Vorschau'),
                                            array('Salutation'    => 'Anrede',
                                                  'Name'          => 'Name',
                                                  'Address'       => 'Adresse',
                                                  'Division'      => 'Klasse',
                                                  'StudentNumber' => 'Schüler-Nr.'
                                            ),
                                            array(
                                                'order'                => array(array(1, 'asc')),
                                                'columnDefs'           => array(
                                                    array('orderable' => false, 'width' => '3%', 'targets' => 0)
                                                ),
                                                'ExtensionRowExchange' => array(
                                                    'Enabled' => true,
                                                    'Url'     => '/Api/Reporting/SerialLetter/Exchange',
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
                                            )
                                        )
                                        : new WarningMessage('Bitte tragen Sie etwas in den Filter ein') )
                                )
                            )
                        ))
                    )
                    .new Layout(
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    ( $IsFilter
                                        ? new Panel(new PlusSign().' Serienbreif anlegen '
                                            , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetterDynamic, $SerialLetter)))
                                            , Panel::PANEL_TYPE_INFO)
                                        : '' )
                                )
                            )
                        )
                    );
                break;
            default:
                $MetaTable = new Panel(new PlusSign().' Serienbreif anlegen '
                    , array(new Well(SerialLetter::useService()->createSerialLetter($FormSerialLetter, $SerialLetter))), Panel::PANEL_TYPE_INFO);
        }
        if (!empty( $LayoutTabs ) && $TabActive === 'STATIC') {
            $LayoutTabs[0]->setActive();
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($TableContent, null, array(
                                'Name'        => 'Name',
                                'Description' => 'Beschreibung',
                                'Option'      => '',
                            ), array(
                                'columnDefs' => array(
                                    array('orderable' => false, 'width' => '130px', 'targets' => 2)
                                )
                            ))
                        ))
                    ))
                ), new Title(new ListingTable().' Übersicht')),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new LayoutTabs($LayoutTabs),
                            $MetaTable
                        ))
                    ))
                ), new Title(new PlusSign().' Hinzufügen'))
            ))
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function formSerialLetter()
    {

        return new Form(new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new TextField('SerialLetter[Name]', 'Name', 'Name'), 4
                ),
                new FormColumn(
                    new TextField('SerialLetter[Description]', 'Beschreibung', 'Beschreibung'), 8
                )
            )),
        )));
    }

    /**
     * @return Form
     */
    private function formFilterPersonGroup()
    {
        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new AutoCompleter('FilterGroup[TblGroup_Name]', 'Gruppe: Name', 'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                        , 3),
                ))
            )
            , new Primary('Filter starten', new Search()));
    }

    /**
     * @return Form
     */
    private function formFilterStudent()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new AutoCompleter('FilterGroup[TblGroup_Name]', 'Gruppe: Name', 'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                        , 3),
                    new FormColumn(
                        new AutoCompleter('FilterYear[TblYear_Name]', 'Bildung: Schuljahr', 'Bildung: Schuljahr', array('Name' => Term::useService()->getYearAll()))
                        , 3),
                    new FormColumn(
                        new TextField('FilterStudent[TblLevel_Name]', 'Klasse: Stufe', 'Klasse: Stufe')
                        , 3),
                    new FormColumn(
                        new TextField('FilterStudent[TblDivision_Name]', 'Klasse: Gruppe', 'Klasse: Gruppe')
                        , 3),
                ))
            )
            , new Primary('Filter starten', new Search()));
    }

    /**
     * @return Form
     */
    private function formFilterProspect()
    {

        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new AutoCompleter('FilterGroup[TblGroup_Name]', 'Gruppe: Name', 'Gruppe: Name', array('Name' => Group::useService()->getGroupAll()))
                        , 3),
                    new FormColumn(
                        new TextField('FilterYear[TblProspectReservation_ReservationYear]', 'Interessent: Schuljahr', 'Interessent: Schuljahr')
                        , 3),
                    new FormColumn(
                        new TextField('FilterStudent[TblProspectReservation_ReservationDivision]', 'Interessent: Stufe', 'Interessent: Stufe')
                        , 3),
                ))
            )
            , new Primary('Filter starten', new Search()));
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

        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));

        if (( $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id) )) {
            if ($SerialLetter == null) {
                $Global = $this->getGlobal();
                $Global->POST['SerialLetter']['Name'] = $tblSerialLetter->getName();
                $Global->POST['SerialLetter']['Description'] = $tblSerialLetter->getDescription();
                $Global->savePost();
            }

            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(array(
                    new LayoutColumn(
                        new Panel('Name', $tblSerialLetter->getName().' '
                            .new Small(new Muted($tblSerialLetter->getDescription())), Panel::PANEL_TYPE_INFO), 8
                    ),
                    new LayoutColumn(array(
                        new Title(new Edit().' Bearbeiten'),
                        new Well(
                            SerialLetter::useService()->updateSerialLetter(
                                $this->formSerialLetter()->appendFormButton(new Primary('Speichern', new Save())),
                                $tblSerialLetter, $SerialLetter
                            )
                        )
                    ))
                ))))
            );

        } else {
            return $Stage
            .new Danger('Serienbrief nicht gefunden', new Exclamation())
            .new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR);
        }

        return $Stage;
    }

    /**
     * @param null|int   $Id
     * @param null|array $FilterGroup
     * @param null|array $FilterStudent
     * @param null|array $FilterPerson
     * @param null|array $FilterYear
     * @param null|array $FilterType
     * @param string     $TabActive
     *
     * @return Stage|string
     */
    public function frontendSerialLetterPersonSelected(
        $Id = null,
        $FilterGroup = null,
        $FilterStudent = null,
        $FilterPerson = null,
        $FilterYear = null,
        $FilterType = null,
        $TabActive = 'PERSON'
    ) {

        $Stage = new Stage('Personen für Serienbriefe', 'Auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = ( $Id == null ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $Stage->addButton(new Standard(new Bold(new Info('Personen Auswahl')),
            '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
            array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Addressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Addressliste für Serienbriefe anzeigen und herunterladen'));

        $Filter = false;
        // No Filter Detected
        if (
            $FilterGroup === null
            && $FilterStudent === null
            && $FilterPerson === null
            && $FilterYear === null
            && $FilterType === null
        ) {
            // set Group Student and Execute Search
//            $FilterGroup['TblGroup_Name'] = 'Schüler';
            $Global = $this->getGlobal();
//            $Global->POST['FilterGroup']['TblGroup_Name'] = 'Schüler';

            // set Year
            $tblYearList = Term::useService()->getYearByNow();
            if ($tblYearList) {
                foreach ($tblYearList as $tblYear) {
                    $Global->POST['FilterYear']['TblYear_Name'] = $tblYear->getName();
                }
            }
            $Global->savePost();
        };

        // Database Join with foreign Key
        if ($FilterGroup) {
            $Filter = $FilterGroup;

            $Pile = new Pile(Pile::JOIN_TYPE_INNER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(), null, 'TblMember_serviceTblPerson');
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(), ViewPerson::TBL_PERSON_ID, null);
            // Group->Person
        }
        // Database Join with foreign Key
        if ($FilterStudent) {
            $Filter = $FilterStudent;

            $Pile = new Pile(Pile::JOIN_TYPE_INNER);
            $Pile->addPile(( new ViewPeopleGroupMember() )->getViewService(), new ViewPeopleGroupMember(),
                null, ViewPeopleGroupMember::TBL_MEMBER_SERVICE_TBL_PERSON
            );
            $Pile->addPile(( new ViewPerson() )->getViewService(), new ViewPerson(),
                ViewPerson::TBL_PERSON_ID, ViewPerson::TBL_PERSON_ID
            );
            $Pile->addPile(( new ViewDivisionStudent() )->getViewService(), new ViewDivisionStudent(),
                ViewDivisionStudent::TBL_DIVISION_STUDENT_SERVICE_TBL_PERSON, ViewDivisionStudent::TBL_DIVISION_TBL_YEAR
            );
            $Pile->addPile(( new ViewYear() )->getViewService(), new ViewYear(),
                ViewYear::TBL_YEAR_ID, ViewYear::TBL_YEAR_ID
            );
        }


        $Result = array();
        $Timeout = null;
        if ($Filter && isset( $Pile )) {
            // Preparation Filter
            array_walk($Filter, function (&$Input) {

                if (!empty( $Input )) {
                    $Input = explode(' ', $Input);
                    $Input = array_filter($Input);
                } else {
                    $Input = false;
                }
            });
            $Filter = array_filter($Filter);
            // Preparation FilterPerson
            if ($FilterPerson) {
                array_walk($FilterPerson, function (&$Input) {

                    if (!empty( $Input )) {
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
            // Preparation $FilterYear
            if ($FilterYear) {
                array_walk($FilterYear, function (&$Input) {

                    if (!empty( $Input )) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterYear = array_filter($FilterYear);
            } else {
                $FilterYear = array();
            }
            // Preparation $FilterType
            if ($FilterType) {
                array_walk($FilterType, function (&$Input) {

                    if (!empty( $Input )) {
                        $Input = explode(' ', $Input);
                        $Input = array_filter($Input);
                    } else {
                        $Input = false;
                    }
                });
                $FilterType = array_filter($FilterType);
            } else {
                $FilterType = array();
            }
            // Filter ordered by Database Join with foreign Key
            if ($FilterGroup) {
                $Result = $Pile->searchPile(array(
                    0 => $Filter,
                    1 => $FilterPerson
                ));
            }
            // Filter ordered by Database Join with foreign Key
            if ($FilterStudent) {
                $Result = $Pile->searchPile(array(
                    0 => array('TblGroup_Name' => array('Schüler')),
                    1 => $FilterPerson,
                    2 => $Filter,
                    3 => $FilterYear
//                    4 => $FilterType
                ));
            }
            // get Timeout status
            $Timeout = $Pile->isTimeout();
        }

        /**
         * @var int                                                        $Index
         * @var ViewPeopleGroupMember[]|ViewDivisionStudent[]|ViewPerson[] $Row
         */
        $SearchResult = array();
        foreach ($Result as $Index => $Row) {
            if ($FilterGroup) {
                /** @var array $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                $DataPerson['Division'] = new Small(new Muted('-NA-'));

            } else {
                /** @var array $DataPerson */
                $DataPerson = $Row[1]->__toArray();
                $tblDivisionStudent = $Row[2]->getTblDivisionStudent();
                if ($tblDivisionStudent) {
                    $tblDivision = $tblDivisionStudent->getTblDivision();
                    if ($tblDivision) {
                        if (strlen($tblDivision->getTblLevel()->getName()) == 1) {
                            $DivisionName = '0'.$tblDivision->getDisplayName();
                        } else {
                            $DivisionName = $tblDivision->getDisplayName();
                        }
                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container($DivisionName);
                    } else {
                        $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                    }
                } else {
                    $DataPerson['Division'] = new Small(new Muted('Gefilterte Klasse:')).new Container('-NA-');
                }
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Exchange'] = (string)new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(
                'Id'       => $Id,
                'PersonId' => $DataPerson['TblPerson_Id']
            ));
            $tblPerson = Person::useService()->getPersonById($DataPerson['TblPerson_Id']);
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Name'] = false;
            if ($tblPerson) {
                $DataPerson['Name'] = $tblPerson->getLastFirstName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            }
            if (!$DataPerson['Name']) {
//                var_dump($DataPerson['TblPerson_Id']);
            }
            /** @noinspection PhpUndefinedFieldInspection */
            $DataPerson['Address'] = (string)new WarningMessage('Keine Adresse hinterlegt!');
            if (isset( $tblAddress ) && $tblAddress && $DataPerson['Name']) {
                /** @noinspection PhpUndefinedFieldInspection */
                $DataPerson['Address'] = $tblAddress->getGuiString();
            }
            $DataPerson['StudentNumber'] = new Small(new Muted('-NA-'));
            if (isset( $tblStudent ) && $tblStudent && $DataPerson['Name']) {
                $DataPerson['StudentNumber'] = $tblStudent->getIdentifier();
            }

            // ignore duplicated Person
            if ($DataPerson['Name']) {
                if (!array_key_exists($DataPerson['TblPerson_Id'], $SearchResult)) {
                    $SearchResult[$DataPerson['TblPerson_Id']] = $DataPerson;
                }
            }

        }

        $tblPersonSearch = $SearchResult;
        $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);

        if (!empty( $tblPersonList ) && !empty( $tblPersonSearch )) {

            $tblPersonIdList = array();
            array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$tblPersonIdList) {
                if (!in_array($tblPerson->getId(), $tblPersonIdList)) {
                    array_push($tblPersonIdList, $tblPerson->getId());
                }
            });

            array_filter($tblPersonSearch, function (&$Item) use ($tblPersonIdList) {
                if (in_array($Item['TblPerson_Id'], $tblPersonIdList)) {
                    $Item = false;
                }
            });

            $tblPersonSearch = array_filter($tblPersonSearch);
        }
        if ($tblPersonList) {
            /** @noinspection PhpUnusedParameterInspection */
            array_walk($tblPersonList, function (TblPerson &$tblPerson) use ($Id) {

                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Exchange = new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(
                    'Id'       => $Id,
                    'PersonId' => $tblPerson->getId()
                ));
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Name = $tblPerson->getLastFirstName();
                $tblAddress = Address::useService()->getAddressByPerson($tblPerson);
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPerson->Address = new WarningMessage('Keine Adresse hinterlegt!');
                if ($tblAddress) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->Address = $tblAddress->getGuiString();
                }
                if ($tblStudent) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->StudentNumber = $tblStudent->getIdentifier();
                } else {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPerson->StudentNumber = new Small(new Muted('-NA-'));
                }


                $VisitedDivision = new Small(new Muted('-NA-'));
                $VisitedDivisionList = array();
                if ($tblPerson !== null) {
                    $tblDivisionStudentAllByPerson = Division::useService()->getDivisionStudentAllByPerson($tblPerson);
                    if ($tblDivisionStudentAllByPerson) {
                        foreach ($tblDivisionStudentAllByPerson as &$tblDivisionStudent) {
                            $tblDivision = $tblDivisionStudent->getTblDivision();
                            /** @var TblDivision $tblDivision */
                            if ($tblDivision) {
                                $tblLevel = $tblDivision->getTblLevel();
                                $tblYear = $tblDivision->getServiceTblYear();
                                if ($tblLevel && $tblYear) {
                                    $VisitedDivisionList[] = new Small(new Muted('Aktuelle Klasse:')).new Container($tblDivision->getDisplayName());
                                }
                            }
                        }

                        if (!empty( $VisitedDivisionList )) {
                            rsort($VisitedDivisionList);
                            $VisitedDivision = current($VisitedDivisionList);
                        }
                    }
                }
                $tblPerson->Division = $VisitedDivision;
            });
        }

        $FormGroup = new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(array(
                        new AutoCompleter('FilterGroup[TblGroup_Name]', 'Gruppe: Name', 'Gruppe: Name', array('Name' => Group::useService()->getGroupAll())),
                    ), 4),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_FIRST_NAME.']', 'Person: Vorname', 'Person: Vorname')
                    ), 4),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_LAST_NAME.']', 'Person: Nachname', 'Person: Nachname')
                    ), 4)
                ))
            )
            , new Primary('in Gruppen suchen'));

        $FormStudent = new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(array(
                        new AutoCompleter('FilterYear[TblYear_Name]', 'Bildung: Schuljahr', 'Bildung: Schuljahr', array('Name' => Term::useService()->getYearAll())),
                    ), 2),
//                    new FormColumn(array(
//                        new AutoCompleter('FilterType[TblType_Name]', 'Bildung: Schulart', 'Bildung: Schulart', array('Name' => Type::useService()->getTypeAll())),
//                    ), 6),
                    new FormColumn(array(
                        new TextField('FilterStudent[TblLevel_Name]', 'Klasse: Stufe', 'Klasse: Stufe')
                    ), 2),
                    new FormColumn(array(
                        new TextField('FilterStudent[TblDivision_Name]', 'Klasse: Gruppe', 'Klasse: Gruppe')
                    ), 2),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_FIRST_NAME.']', 'Person: Vorname', 'Person: Vorname')
                    ), 3),
                    new FormColumn(array(
                        new TextField('FilterPerson['.ViewPerson::TBL_PERSON_LAST_NAME.']', 'Person: Nachname', 'Person: Nachname')
                    ), 3)
                )),
                new FormRow(array(

                ))
            ))
            , new Primary('in Klassen suchen'));

        // set Success by filtered Input field in FormGroup
        if ($FilterGroup) {
            foreach ($FilterGroup as $Field => $Value) {
                if ($Value) {
                    $FormGroup->setSuccess('FilterGroup['.$Field.']', '', new Filter());
                }
            }
            if ($FilterPerson) {
                foreach ($FilterPerson as $Field => $Value) {
                    if ($Value) {
                        $FormGroup->setSuccess('FilterPerson['.$Field.']', '', new Filter());
                    }
                }
            }
        }

        // set Success by filtered Input field in FormStudent
        if ($FilterStudent) {
            foreach ($FilterStudent as $Field => $Value) {
                if ($Value) {
                    $FormStudent->setSuccess('FilterStudent['.$Field.']', '', new Filter());
                }
            }
            if ($FilterPerson) {
                foreach ($FilterPerson as $Field => $Value) {
                    if ($Value) {
                        $FormStudent->setSuccess('FilterPerson['.$Field.']', '', new Filter());
                    }
                }
            }
            if ($FilterYear) {
                foreach ($FilterYear as $Field => $Value) {
                    if ($Value) {
                        $FormStudent->setSuccess('FilterYear['.$Field.']', '', new Filter());
                    }
                }
            }
            if ($FilterType) {
                foreach ($FilterType as $Field => $Value) {
                    if ($Value) {
                        $FormStudent->setSuccess('FilterType['.$Field.']', '', new Filter());
                    }
                }
            }
        }

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Anzahl Anschreiben: '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );


        // Create Tabs
        $LayoutTabs[] = new LayoutTab('Personen in Gruppen suchen', 'PERSON', array('Id' => $tblSerialLetter->getId()));
        $LayoutTabs[] = new LayoutTab('Schüler in Klassen suchen', 'DIVISION', array('Id' => $tblSerialLetter->getId()));
        if (!empty( $LayoutTabs ) && $TabActive === 'PERSON') {
            $LayoutTabs[0]->setActive();
        }

        switch ($TabActive) {
            case 'PERSON':
                $MetaTable = new Panel(new Search().' Personen-Suche nach '.new Bold('Personengruppe')
                    , array($FormGroup), Panel::PANEL_TYPE_INFO);
                break;
            case 'DIVISION':
                $MetaTable = new Panel(new Search().' Schüler-Suche nach '.new Bold('Schuljahr / Klasse / Schüler')
                    , array($FormStudent), Panel::PANEL_TYPE_INFO);
                break;
            default:
                $MetaTable = new Panel(new Search().' Personen-Suche nach '.new Bold('Personengruppe')
                    , array($FormGroup), Panel::PANEL_TYPE_INFO);
        }
//        $MetaTable = new Well($MetaTable);

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Title(new PersonIcon().' Personen', 'Zugewiesen'),

                            new Layout(
                                new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new Panel('Serienbief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
                                        ), 12),
                                    ))
                                ))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new Title(new PersonIcon().' Personen', 'Verfügbar'),
                            new Layout(
                                new LayoutGroup(array(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            new LayoutTabs($LayoutTabs),
                                            $MetaTable
                                        ), 12),
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(
                                            ( $Timeout === true
                                                ? new WarningMessage('Die Tabelle enthält nur einen Teil der Suchergebnisse!')
                                                : ''
                                            )
                                        )
                                    )),
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                            ( !$FilterStudent && !$FilterGroup
                                                ? new WarningMessage('Benutzen Sie bitte den Filter')
                                                : ( empty( $tblPersonSearch )
                                                    ? new WarningMessage('Keine Ergebnisse bei aktueller Filterung '.new SuccessText(new Filter()))
                                                    : ''
                                                )
                                            )
                                        ,

                                        ), 12)
                                    ))
                                ))
                            )
                        ), 6)
                    ))
                ))
            )
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new TableData($tblPersonList, null,
                                array('Exchange'      => '',
                                      'Name'          => 'Name',
                                      'Address'       => 'Adresse',
                                      'Division'      => 'Klasse',
                                      'StudentNumber' => 'Schüler-Nr.'
                                ),
                                array(
                                    'order'                => array(array(1, 'asc')),
                                    'columnDefs'           => array(
                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                    ),
                                    'ExtensionRowExchange' => array(
                                        'Enabled' => true,
                                        'Url'     => '/Api/Reporting/SerialLetter/Exchange',
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
                            ),
                            new Exchange(Exchange::EXCHANGE_TYPE_MINUS, array(), 'Alle entfernen', 'TableRemoveAll')
                        ), 6),
                        new LayoutColumn(array(
//                            ( !$FilterStudent && !$FilterGroup
//                                ? ''
//                                :
                            new TableData($tblPersonSearch, null,
                                    array('Exchange'      => ' ',
                                          'Name'          => 'Name',
                                          'Address'       => 'Adresse',
                                          'Division'      => 'Klasse',
                                          'StudentNumber' => 'Schüler-Nr.'
                                    ),
                                    array(
                                        'order'                => array(array(1, 'asc')),
                                        'columnDefs'           => array(
                                            array('orderable' => false, 'width' => '1%', 'targets' => 0)
                                        ),
                                        'ExtensionRowExchange' => array(
                                            'Enabled' => true,
                                            'Url'     => '/Api/Reporting/SerialLetter/Exchange',
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
                                    )
//                                )
                            ),
                            ( !$FilterStudent && !$FilterGroup
                                ? ''
                                : new Exchange(Exchange::EXCHANGE_TYPE_PLUS, array(), 'Alle hinzufügen', 'TableAddAll')
                            )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null|int $Id
     *
     * @return Stage|string
     */
    public function frontendPersonAddress(
        $Id = null
    ) {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen auswählen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
        if (!$tblSerialLetter) {
            return $Stage.new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }
        $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
            array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        $Stage->addButton(new Standard(new Bold(new Info('Adressen Auswahl')), '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Addressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Addressliste für Serienbriefe anzeigen und herunterladen'));

        $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
        if (!$tblPersonList) {
            return $Stage.new Danger('Es sind keine Personen dem Serienbrief zugeordnet', new Exclamation());
        }

        $TableContent = array();
        array_walk($tblPersonList, function (TblPerson $tblPerson) use (&$TableContent, $Id, $tblSerialLetter) {
            $Item['Name'] = $tblPerson->getLastFirstName();
            $Item['StudentNumber'] = new Small(new Muted('-NA-'));
            $Item['Address'] = array();
            $Item['Option'] = new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                array('Id'       => $Id,
                      'PersonId' => $tblPerson->getId(),
                      'Route'    => '/Reporting/SerialLetter/Address'));
            $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
            if ($tblStudent) {
                $Item['StudentNumber'] = $tblStudent->getIdentifier();
            }

            $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
            if ($tblAddressPersonList) {
                $Data = array();
                /** @var TblAddressPerson $tblAddressPerson */
                foreach ($tblAddressPersonList as $tblAddressPerson) {
                    if (( $serviceTblPersonToAddress = $tblAddressPerson->getServiceTblToPerson() )) {
                        if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                            if (( $PersonToAddress = $tblToPerson->getServiceTblPerson() )) {
                                if (( $tblAddress = $serviceTblPersonToAddress->getTblAddress() )) {
                                    if (!isset( $Data[$tblAddress->getId()]['Person'] )) {
                                        $Data[$tblAddress->getId()]['Person'] =
                                            $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                    } else {
                                        $Data[$tblAddress->getId()]['Person'] =
                                            $Data[$tblAddress->getId()]['Person'].', '.
                                            $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                    }
                                    if (!isset( $Data[$tblAddress->getId()]['District'] )) {
                                        if (( $tblCity = $tblAddress->getTblCity() )) {
                                            $Data[$tblAddress->getId()]['District'] = $tblAddress->getTblCity()->getDistrict();
                                        }
                                    }
                                    if (!isset( $Data[$tblAddress->getId()]['Street'] )) {
                                        $Data[$tblAddress->getId()]['Street'] =
                                            $tblAddress->getStreetName().' '.$tblAddress->getStreetNumber();
                                    }
                                    if (!isset( $Data[$tblAddress->getId()]['City'] )) {
                                        if (( $tblCity = $tblAddress->getTblCity() )) {
                                            $Data[$tblAddress->getId()]['City'] = $tblCity->getCode().' '.$tblCity->getName();
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                if (!empty( $Data )) {
                    foreach ($Data as $AddressPanel) {
                        $Item['Address'][] = new LayoutColumn(
                            new Panel('', $AddressPanel)
                            , 4);
                    }
                }


                $Item['Address'] = array_filter($Item['Address']);

                $Item['Address'] = (string)new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            $Item['Address']
                        )
                    )
                );
            }

            if (empty( $Item['Address'] )) {
                $Item['Address'] = new WarningMessage('Keine Adressen ausgewählt!');
            }

            array_push($TableContent, $Item);
        });

        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Anzahl Anschreiben: '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );

//        // Selectbox Field's
//        $tblSalutationAll = Person::useService()->getSalutationAll();
//        if ($tblSalutationAll) {
//            $tblSalutation = new TblSalutation('Familie');
//            $tblSalutation->setId(TblAddressPerson::SALUTATION_FAMILY);
//            $tblSalutationAll['Family'] = $tblSalutation;
//        }

        $Buttons = array();
        $Buttons[] = new Standard('Löschen', '/Reporting/SerialLetter/Address/Remove', new Remove(), array('Id' => $tblSerialLetter->getId()));   //
        $Buttons[] = new Standard('Personen direkt anschreiben', '/Reporting/SerialLetter/Address/Person', new Edit()
            , array('Id' => $tblSerialLetter->getId()));
        $Buttons[] = new Standard('Sorgeberechtigte anschreiben', '/Reporting/SerialLetter/Address/Guardian', new Edit()
            , array('Id' => $tblSerialLetter->getId()));

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Layout(
                                new LayoutGroup(
                                    new LayoutRow(array(
                                        new LayoutColumn(array(
                                                new Title(new Setup().' Adressen', 'Zuweisung'),
                                                new Panel('Serienbief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter)
                                            )
                                            , 6),
                                        new LayoutColumn(array(
                                            new Title('Adressauswahl', 'Automatik'),
                                            new Panel('Adressen von untenstehenden Personen',
                                                $Buttons
                                                , Panel::PANEL_TYPE_INFO)
                                        ), 6)
//                                        new LayoutColumn(array(
//                                            new Title(new Setup().' Adressen', 'Automatik'),
//                                            new LayoutTabs($LayoutTabs),
//                                            $MetaTable
//                                        ), 6)
                                    ))
                                )
                            )
                        ), 12),
                        new LayoutColumn(
                            new TableData($TableContent, null, array(
                                'Name'          => 'Name',
                                'StudentNumber' => 'Schüler-Nr.',
                                'Address'       => 'Serienbrief Adresse',
                                'Option'        => '',
                            ), array(
                                'order'      => array(array(2, 'desc')),
                                'columnDefs' => array(
                                    array('orderable' => false, 'width' => '1%', 'targets' => -1),
                                    array('width' => '15%', 'targets' => 0),
                                    array('width' => '10%', 'targets' => 1)
                                )
                            ))
                        )
                    ))
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
    public function frontendSetAddressByPerson($Id = null)
    {

        $Stage = new Stage('Befüllen der Adressen', '');
        $tblSerialLetter = ( !$Id ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            $Stage->setContent(new WarningMessage('Es konnte kein Serienbrief gefunden werden'));
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id)));

        $Stage->setContent(
            SerialLetter::useService()->createAddressPersonSelf($tblSerialLetter)
        );

        return $Stage;
    }

    /**
     * @param $Id
     *
     * @return Stage
     */
    public function frontendSetAddressByPersonGuardian($Id = null)
    {

        $Stage = new Stage('Befüllen der Adressen', 'aus Sorgeberechtigten');
        $tblSerialLetter = ( !$Id ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            $Stage->setContent(new WarningMessage('Es konnte kein Serienbrief gefunden werden'));
        }
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id)));

        $Stage->setContent(
            SerialLetter::useService()->createAddressPersonGuardian($tblSerialLetter)
        );

        return $Stage;
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
     * @return Stage
     */
    public function frontendAddressRemove($Id = null, $Confirm = false)
    {

        $Stage = new Stage('Adresslisten für Serienbriefe', 'Löschen');
        if ($Id) {
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/SerialLetter/Address', new ChevronLeft(), array('Id' => $Id))
            );
            $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id);
            if (!$tblSerialLetter) {
                $Stage->setContent(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(new LayoutColumn(array(
                            new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gefunden werden.'),
                            new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
                    if ($tblPersonList) {
                        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
                        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
                            'Anzahl Anschreiben: '.$SerialLetterCount,);
                        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                                .' Person(en)', Label::LABEL_TYPE_INFO)
                        );
                    } else {
                        $PanelContent = 'Keine Personen im Warenkorb';
                        $PanelFooter = '';
                    }

                    $Stage->setContent(
                        new Layout(new LayoutGroup(
                            new LayoutRow(array(
                                new LayoutColumn(array(
                                    new Panel('Serienbief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
                                ), 6),
                                new LayoutColumn(
                                    new Panel(new Question().' Alle Zuweisungen für Adressen des Serienbriefs wirklich löschen?', array(
                                        $tblSerialLetter->getName().' '.$tblSerialLetter->getDescription()
                                    ),
                                        Panel::PANEL_TYPE_DANGER,
                                        new Standard(
                                            'Ja', '/Reporting/SerialLetter/Address/Remove', new Ok(),
                                            array('Id' => $Id, 'Confirm' => true)
                                        )
                                        .new Standard(
                                            'Nein', '/Reporting/SerialLetter/Address', new Disable(),
                                            array('Id' => $Id)
                                        )
                                    )
                                )
                            ))

                        ))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                ( SerialLetter::useService()->removeSerialLetterAddress($tblSerialLetter)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Zuweisungen für Adressen des Serienbriefs wurde gelöscht')
                                    : new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gelöscht werden')
                                ),
                                new Redirect('/Reporting/SerialLetter/Address', Redirect::TIMEOUT_SUCCESS, array('Id' => $tblSerialLetter->getId()))
                            )))
                        )))
                    );
                }
            }
        } else {
            $Stage->setContent(
                new Layout(new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(array(
                        new Danger(new Ban().' Daten nicht abrufbar.'),
                        new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }

    /**
     * @param null   $Id
     * @param null   $PersonId
     * @param string $Route
     * @param null   $Check
     *
     * @return Stage|string
     */
    public function frontendPersonAddressEdit($Id = null, $PersonId = null, $Route = '/Reporting/SerialLetter/Address', $Check = null)
    {

        $Stage = new Stage('Adresse(n)', 'Auswählen');
        $tblSerialLetter = ( $Id === null ? false : SerialLetter::useService()->getSerialLetterById($Id) );
        if (!$tblSerialLetter) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
            return $Stage.new Danger('Serienbrief nicht gefunden', new Exclamation());
        }

        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft(), array('Id' => $Id)));
        $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
            array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
        $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
            array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
        $Stage->addButton(new Standard('Addressliste', '/Reporting/SerialLetter/Export', new View(),
            array('Id' => $tblSerialLetter->getId()),
            'Addressliste für Serienbriefe anzeigen und herunterladen'));

        $tblPerson = Person::useService()->getPersonById($PersonId);
        if (!$tblPerson) {
            return $Stage.new WarningMessage('Person nicht gefunden', new Exclamation());
        }

        // Set Global Post
        $Global = $this->getGlobal();
        if ($Check === null) {
            $tblAddressAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
            if ($tblAddressAllByPerson) {
                foreach ($tblAddressAllByPerson as $tblAddressPerson) {
                    if ($tblAddressPerson->getServiceTblPerson()
                        && $tblAddressPerson->getServiceTblPersonToAddress()
                        && $tblAddressPerson->getServiceTblToPerson()
                    ) {
                        $Global->POST['Check']
                        [$tblAddressPerson->getServiceTblPerson()->getId()]
                        [$tblAddressPerson->getServiceTblToPerson()->getId()]
                        ['Address'] = 1;

//                        $Global->POST['Check']
//                        [$tblAddressPerson->getServiceTblPerson()->getId()]
//                        [$tblAddressPerson->getServiceTblToPerson()->getId()]
//                        ['Salutation'] = $tblAddressPerson->getServiceTblSalutation() ? $tblAddressPerson->getServiceTblSalutation()->getId() : 0;
                    }
                }
            }
        }
        $Global->savePost();

        $dataList = array();
        $columnList = array(
            'Salutation'   => 'Anrede',
            'Person'       => 'Person',
            'Relationship' => 'Beziehung',
            'Address'      => 'Adressen'
        );

        $personCount = 0;
        $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblPerson);
        if ($tblAddressToPersonList) {
            foreach ($tblAddressToPersonList as $tblToPerson) {

                $dataList[$tblPerson->getId()]['Number'] = ++$personCount;
                $dataList[$tblPerson->getId()]['Person'] = $tblPerson->getLastFirstName();
                $subDataList[] = array(
                    'Salutation'   => $tblPerson->getSalutation(),
                    'Person'       => $tblToPerson->getServiceTblPerson() ? new Bold($tblToPerson->getServiceTblPerson()->getFullName()) : '',
                    'Relationship' => '',
                    'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Address]',
                        '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1),
                );
            }
        }

        $tblRelationshipAll = Relationship::useService()->getPersonRelationshipAllByPerson($tblPerson);
        $PersonToPersonId = array();
        if ($tblRelationshipAll) {
            foreach ($tblRelationshipAll as $tblRelationship) {
                $tblType = $tblRelationship->getTblType();
                if ($tblType && $tblType->getName() !== 'Arzt') {
                    if ($tblRelationship->getServiceTblPersonTo() && $tblRelationship->getServiceTblPersonFrom()) {
                        if ($tblRelationship->getServiceTblPersonTo()->getId() == $tblPerson->getId()) {
                            $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonFrom());
                            $direction = $tblRelationship->getServiceTblPersonFrom()->getLastFirstName().' ist '.$tblType->getName()
                                .' für '.new Bold($tblPerson->getLastFirstName());
                        } else {
                            $tblAddressToPersonList = Address::useService()->getAddressAllByPerson($tblRelationship->getServiceTblPersonTo());
                            $direction = new Bold($tblPerson->getLastFirstName()).' ist '.$tblType->getName()
                                .' für '.$tblRelationship->getServiceTblPersonTo()->getLastFirstName();
                        }
                        if ($tblAddressToPersonList) {
                            foreach ($tblAddressToPersonList as $tblToPerson) {
                                $PersonIdAddressIdNow = ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getId() : '' ).'.'.
                                    ( $tblToPerson->getTblAddress() ? $tblToPerson->getTblAddress()->getId() : '' );
                                // ignore duplicated Person by Relationship
                                if (!array_key_exists($PersonIdAddressIdNow, $PersonToPersonId)) {
                                    $subDataList[] = array(
                                        'Salutation'   => ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getSalutation() : '' ),
                                        'Person'       => ( $tblToPerson->getServiceTblPerson() ? $tblToPerson->getServiceTblPerson()->getFullName() : '' ),
                                        'Relationship' => $direction,
                                        'Address'      => new CheckBox('Check['.$tblPerson->getId().']['.$tblToPerson.'][Address]',
                                            '&nbsp; '.$tblToPerson->getTblAddress()->getGuiString(), 1)
                                    );
                                    $PersonToPersonId[$PersonIdAddressIdNow] = $PersonIdAddressIdNow;
                                }
                            }
                        } else {
                            /** @var TblToPerson $tblRelationship */
                            if ($tblRelationship->getServiceTblPersonTo()->getId() == $tblPerson->getId()) {
                                $subDataList[] = array(
                                    'Salutation'   => ( $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getSalutation() : '' ),
                                    'Person'       => ( $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getFullName() : '' ),
                                    'Relationship' => $direction,
                                    'Address'      => new Warning(
                                        new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Keine Adresse hinterlegt')
                                );
                            } else {
                                $subDataList[] = array(
                                    'Salutation'   => ( $tblRelationship->getServiceTblPersonFrom() ? $tblRelationship->getServiceTblPersonFrom()->getSalutation() : '' ),
                                    'Person'       => ( $tblRelationship->getServiceTblPersonTo() ? $tblRelationship->getServiceTblPersonTo()->getFullName() : '' ),
                                    'Relationship' => $direction,
                                    'Address'      => new Warning(
                                        new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Keine Adresse hinterlegt')
                                );
                            }
                        }
                    }
                }
            }
        }

        if (isset( $subDataList )) {
            $Form = new Form(
                new FormGroup(
                    new FormRow(
                        new FormColumn(array(
                            new TableData($subDataList, null, $columnList,
                                array(
                                    'order' => array(array(2, 'asc'), array(1, 'asc')),
//                                    'columnDefs' => array(
//                                        array('orderable' => false, 'width' => '1%', 'targets' => 0)
//                                    ),
                                )),
                            new Primary('Speichern', new Save())
                        ))
                    )
                )
            );
        }

        $tblPersonList = SerialLetter::useService()->getPersonAllBySerialLetter($tblSerialLetter);
        $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
        $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
            'Anzahl Anschreiben: '.$SerialLetterCount,);
        $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                .' Person(en)', Label::LABEL_TYPE_INFO)
        );

        $tblAddressPersonList = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter, $tblPerson);
        $PanelPerson = new Panel(
            new Bold($tblPerson->getFullName()),
            'Verwendete Adresse(n): '.( $tblAddressPersonList === false ? 0 : count($tblAddressPersonList) ),
            Panel::PANEL_TYPE_SUCCESS);

        if (isset( $Form )) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(array(
                                new Title(new Listing().' Addressen', 'Auswahl')
                            , new Layout(
                                    new LayoutGroup(
                                        new LayoutRow(array(
                                            new LayoutColumn(
                                                new Panel('Serienbief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter
                                                )
                                                , 6),
                                            new LayoutColumn(
                                                $PanelPerson
                                                , 6)
                                        ))
                                    )
                                ),
                                new Well(SerialLetter::useService()->setPersonAddressSelection(
                                    $Form, $tblSerialLetter, $tblPerson, $Check, $Route
                                ))
                            ))
                        )
                    )
                )
            );
        } else {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Title(new Listing().'  Addressen', 'Auswahl')
                                , 12),
                            new LayoutColumn(
                                new Panel('Serienbief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter
                                )
                                , 6),
                            new LayoutColumn(
                                $PanelPerson
                                , 6),
                            new LayoutColumn(
                                new WarningMessage('Die Person '.$tblPerson->getFullName().' sowie die in Beziehung gesetzten Personen besitzen keine Adresse. Zur Person '
                                    .new Standard('', '/People/Person', new PersonIcon(), array('Id' => $tblPerson->getId())))
                                , 12)
                        ))
                    )
                )
            );
        }

        return $Stage;
    }

    /**
     * @param null $Id
     *
     * @return Stage|string
     */
    public function frontendSerialLetterExport(
        $Id = null
    ) {
        $Stage = new Stage('Adresslisten für Serienbriefe', 'Person mit Adressen herunterladen');
        $Stage->addButton(new Standard('Zurück', '/Reporting/SerialLetter', new ChevronLeft()));
        if (( $tblSerialLetter = SerialLetter::useService()->getSerialLetterById($Id) )) {

            $Stage->addButton(new Standard('Personen Auswahl', '/Reporting/SerialLetter/Person/Select', new PersonGroup(),
                array('Id' => $tblSerialLetter->getId()), 'Personen auswählen'));
            $Stage->addButton(new Standard('Adressen Auswahl', '/Reporting/SerialLetter/Address', new Setup(),
                array('Id' => $tblSerialLetter->getId()), 'Adressen auswählen'));
            $Stage->addButton(new Standard(new Bold(new Info('Addressliste')), '/Reporting/SerialLetter/Export', new View(),
                array('Id' => $tblSerialLetter->getId()),
                'Addressliste für Serienbriefe anzeigen und herunterladen'));

            $dataList = array();
            $columnList = array(
                'Number'          => 'Nr.',
                'Person'          => 'Person',
                'StudentNumber'   => 'Schüler-Nr.',
                'Salutation'      => 'Anrede',
                'PersonToAddress' => 'Adressat',
                'Address'         => 'Adresse',
                'Option'          => ''
            );

            $countAddresses = 0;
            $count = 0;
            $tblPersonList = false;
            $tbSerialPersonList = SerialLetter::useService()->getSerialPersonBySerialLetter($tblSerialLetter);
            if ($tbSerialPersonList) {
                foreach ($tbSerialPersonList as $tbSerialPerson) {
                    if ($tbSerialPerson->getServiceTblPerson()) {
                        $tblPersonList[] = $tbSerialPerson->getServiceTblPerson();
                    }
                }
            }
            if ($tblPersonList) {
                $tblPersonList = $this->getSorter($tblPersonList)->sortObjectBy('LastFirstName', new StringGermanOrderSorter());
                /** @var TblPerson $tblPerson */
                foreach ($tblPersonList as $tblPerson) {
                    $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                        $tblPerson);
                    if ($tblAddressPersonAllByPerson) {
                        /** @var TblAddressPerson $tblAddressPerson */
                        foreach ($tblAddressPersonAllByPerson as $tblAddressPerson) {
                            // clean data if missing address
                            if (!$tblAddressPerson->getServiceTblToPerson()) {
                                SerialLetter::useService()->destroySerialAddressPerson($tblAddressPerson);
                            }
                        }
                    }

                    // get fresh list
                    $tblAddressPersonAllByPerson = SerialLetter::useService()->getAddressPersonAllByPerson($tblSerialLetter,
                        $tblPerson);
                    if ($tblAddressPersonAllByPerson) {
                        /** @var TblAddressPerson $tblAddressPerson */
                        $AddressList = array();
                        array_walk($tblAddressPersonAllByPerson, function (TblAddressPerson $tblAddressPerson) use (&$AddressList, $tblPerson, $Id) {
                            if (( $serviceTblPersonToAddress = $tblAddressPerson->getServiceTblToPerson() )) {
                                if (( $tblToPerson = $tblAddressPerson->getServiceTblToPerson() )) {
                                    if (( $PersonToAddress = $tblToPerson->getServiceTblPerson() )) {
                                        if (( $tblAddress = $serviceTblPersonToAddress->getTblAddress() )) {
                                            if (!isset( $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'] )) {
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'] =
                                                    $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                    $PersonToAddress->getSalutation();
                                            } else {
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'] =
                                                    $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'].', '.
                                                    $PersonToAddress->getLastName().' '.$PersonToAddress->getFirstName();
                                                $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] =
                                                    ( $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] !== ''
                                                        ? $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'].', '.
                                                        $PersonToAddress->getSalutation()
                                                        : $PersonToAddress->getSalutation() );
                                            }
                                        }

                                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                                        if ($tblStudent) {
                                            $StudentNumber = $tblStudent->getIdentifier();
                                        } else {
                                            $StudentNumber = new Small(new Muted('-NA-'));
                                        }

                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['Person'] =
                                            ( $tblAddressPerson->getServiceTblPerson()
                                                ? $tblAddressPerson->getServiceTblPerson()->getLastFirstName()
                                                : new Warning(new Exclamation().' Person nicht gefunden.') );
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['StudentNumber'] = $StudentNumber;
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToAddress'] =
                                            $AddressList[$tblPerson->getId().$tblAddress->getId()]['PersonToWrite'];
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['Address'] =
                                            ( $tblAddressPerson->getServiceTblToPerson()
                                                ? $tblAddressPerson->getServiceTblToPerson()->getTblAddress()->getGuiString()
                                                : new Warning(new Exclamation().' Adresse nicht gefunden.') );
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['Salutation'] =
                                            isset( $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] )
                                            && $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList'] !== ''
                                                ? $AddressList[$tblPerson->getId().$tblAddress->getId()]['SalutationList']
                                                : new Warning(new Exclamation().' Keine Anrede hinterlegt.');
                                        $AddressList[$tblPerson->getId().$tblAddress->getId()]['Option'] =
                                            new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                                array('Id'       => $Id,
                                                      'PersonId' => $tblPerson->getId(),
                                                      'Route'    => '/Reporting/SerialLetter/Export'));
                                    }
                                }
                            }
                        });
                        if ($AddressList) {
                            foreach ($AddressList as $Address) {
                                $countAddresses++;
                                $dataList[] = array(
                                    'Number'          => ++$count,
                                    'Person'          => ( isset( $Address['Person'] ) ? $Address['Person'] : '' ),
                                    'StudentNumber'   => ( isset( $Address['StudentNumber'] ) ? $Address['StudentNumber'] : '' ),
                                    'PersonToAddress' => ( isset( $Address['PersonToAddress'] ) ? $Address['PersonToAddress'] : '' ),
                                    'Address'         => ( isset( $Address['Address'] ) ? $Address['Address'] : '' ),
                                    'Salutation'      => ( isset( $Address['Salutation'] ) ? $Address['Salutation'] : '' ),
                                    'Option'          => ( isset( $Address['Option'] ) ? $Address['Option'] : '' )
                                );
                            }
                        }
                    } else {
                        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                        if ($tblStudent) {
                            $StudentNumber = $tblStudent->getIdentifier();
                        } else {
                            $StudentNumber = new Small(new Muted('-NA-'));
                        }

                        $dataList[] = array(
                            'Number'          => ++$count,
                            'Person'          => $tblPerson->getLastFirstName(),
                            'StudentNumber'   => $StudentNumber,
                            'PersonToAddress' => new Warning(new Exclamation().' Keine Person mit Adresse hinterlegt.'),
                            'Address'         => '',
                            'Salutation'      => '',
                            'Option'          => new Standard('', '/Reporting/SerialLetter/Address/Edit', new Edit(),
                                array('Id'       => $Id,
                                      'PersonId' => $tblPerson->getId(),
                                      'Route'    => '/Reporting/SerialLetter/Export'))
                        );
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

            $SerialLetterCount = SerialLetter::useService()->getSerialLetterCount($tblSerialLetter);
            $PanelContent = array('Name: '.$tblSerialLetter->getName().' '.new Small(new Muted($tblSerialLetter->getDescription())),
                'Anzahl Anschreiben: '.$SerialLetterCount,);
            $PanelFooter = new PullRight(new Label('Enthält '.( $tblPersonList === false ? 0 : count($tblPersonList) )
                    .' Person(en)', Label::LABEL_TYPE_INFO)
            );

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
                                new Title(new EyeOpen().' Adressen', 'Übersicht')
                            ),
                            new LayoutColumn(
                                new Panel('Serienbrief', $PanelContent, Panel::PANEL_TYPE_SUCCESS, $PanelFooter),
                                6),
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
            return $Stage.new Danger('Adressliste für Serienbrief nicht gefunden', new Exclamation());
        }
    }

    /**
     * @param            $Id
     * @param bool|false $Confirm
     *
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
                            new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gefunden werden.'),
                            new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                        )))
                    )))
                );
            } else {
                if (!$Confirm) {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(array(
                            new Panel('Adressliste für Serienbriefe', new Bold($tblSerialLetter->getName()).
                                ( $tblSerialLetter->getDescription() !== '' ? '&nbsp;&nbsp;'
                                    .new Muted(new Small(new Small($tblSerialLetter->getDescription()))) : '' ),
                                Panel::PANEL_TYPE_INFO),
                            new Panel(new Question().' Diese Adressliste für Serienbriefe wirklich löschen?', array(
                                $tblSerialLetter->getName().' '.$tblSerialLetter->getDescription()
                            ),
                                Panel::PANEL_TYPE_DANGER,
                                new Standard(
                                    'Ja', '/Reporting/SerialLetter/Destroy', new Ok(),
                                    array('Id' => $Id, 'Confirm' => true)
                                )
                                .new Standard(
                                    'Nein', '/Reporting/SerialLetter', new Disable()
                                )
                            )
                        )))))
                    );
                } else {
                    $Stage->setContent(
                        new Layout(new LayoutGroup(array(
                            new LayoutRow(new LayoutColumn(array(
                                ( SerialLetter::useService()->destroySerialLetter($tblSerialLetter)
                                    ? new Success(new \SPHERE\Common\Frontend\Icon\Repository\Success().' Die Adressliste für Serienbriefe wurde gelöscht')
                                    : new Danger(new Ban().' Die Adressliste für Serienbriefe konnte nicht gelöscht werden')
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
                        new Danger(new Ban().' Daten nicht abrufbar.'),
                        new Redirect('/Reporting/SerialLetter', Redirect::TIMEOUT_ERROR)
                    )))
                )))
            );
        }
        return $Stage;
    }
}
