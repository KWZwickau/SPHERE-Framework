<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use SPHERE\Application\Api\Setting\UserAccount\ApiUserAccount;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Select;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Standard\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $View = new Stage();
        $View->setTitle('Auswertungen');
        $View->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $View;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendClassList($DivisionId = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Standard/Person/ClassList', new ChevronLeft()));
        }
        $tblDivisionAll = Division::useService()->getDivisionAll();

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Standard/Person/ClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc')
                                        )
                                    ))
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                ));
        } else {
            $Stage = $this->showClassList($Stage, $DivisionId);
        }

        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendExtendedClassList($DivisionId = null)
    {

        $Stage = new Stage('Auswertung', 'erweiterte Klassenlisten');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Standard/Person/ExtendedClassList',
                new ChevronLeft()));
        }
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $PersonList = array();

        if ($DivisionId !== null) {

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createExtendedClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/ExtendedClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Standard/Person/ExtendedClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc')
                                        )
                                    ))
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ($tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ($tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'         => '#',
                                        'StudentNumber'  => 'Schüler-Nr.',
                                        'LastName'       => 'Name',
                                        'FirstName'      => 'Vorname',
                                        'Gender'         => 'Geschlecht',
                                        'Address'        => 'Adresse',
                                        'Birthday'       => 'Geburtsdatum',
                                        'Birthplace'     => 'Geburtsort',
                                        'Guardian1'      => 'Sorgeberechtigter 1',
                                        'PhoneGuardian1' => 'Tel. Sorgeber. 1',
                                        'Guardian2'      => 'Sorgeberechtigter 2',
                                        'PhoneGuardian2' => 'Tel. Sorgeber. 2',
                                    ),
                                    array(
                                        "pageLength" => -1,
                                        "responsive" => false
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Weiblich', array(
                                    'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: ' . count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        }

        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendBirthdayClassList($DivisionId = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten Geburtstag');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Standard/Person/BirthdayClassList',
                new ChevronLeft()));
        }
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $PersonList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createBirthdayClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/BirthdayClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }


            }
        }

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Standard/Person/BirthdayClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc')
                                        )
                                    ))
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ($tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ($tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number' => '#',
                                        'Name' => 'Name, Vorname',
                                        'Address' => 'Anschrift',
                                        'Birthplace' => 'Geburtsort',
                                        'Birthday' => 'Geburtsdatum',
                                        'Age' => 'Alter',
                                    ),
                                    array(
                                        "pageLength" => -1,
                                        "responsive" => false,
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 4)
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Weiblich', array(
                                    'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: ' . count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        }

        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendMedicalInsuranceClassList($DivisionId = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten Krankenkasse');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Standard/Person/MedicalInsuranceClassList',
                new ChevronLeft()));
        }
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $PersonList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createMedicalInsuranceClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/MedicalInsuranceClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Standard/Person/MedicalInsuranceClassList',
                    new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc')
                                        )
                                    ))
                                , 12)
                        ), new Title(new Listing() . ' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ($tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ($tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number' =>'#',
                                        'StudentNumber' => 'Schüler-Nr.',
                                        'Name' => 'Name,<br/>Vorname',
                                        'Address' => 'Anschrift',
                                        'Birthday' => 'Geburtsdatum<br/>Geburtsort',
                                        'MedicalInsurance' => 'Krankenkasse',
                                        'Guardian' => '1. Sorgeberechtigter<br/>2. Sorgeberechtigter',
                                        'PhoneNumber' => 'Telefon<br/>Schüler',
                                        'PhoneGuardianNumber' => 'Telefon<br/>Sorgeberechtigte',
                                    ),
                                    array(
                                        "pageLength" => -1,
                                        "responsive" => false
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Weiblich', array(
                                    'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: ' . count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null $GroupId
     *
     * @return Stage
     */
    public function frontendGroupList($GroupId = null)
    {

        $Stage = new Stage('Auswertung', 'Personengruppenlisten');
        $tblGroupAll = Group::useService()->getGroupAll();
        $PersonList = array();
        $TableContent = array();
        if ($GroupId === null) {
            if ($tblGroupAll) {
                array_walk($tblGroupAll, function (TblGroup $tblGroup) use (&$TableContent) {

                    $MoreColumnInfo = '';
                    if ($tblGroup->getMetaTable() == 'PROSPECT'
                        || $tblGroup->getMetaTable() == 'STUDENT'
                        || $tblGroup->getMetaTable() == 'CUSTODY'
                        || $tblGroup->getMetaTable() == 'TEACHER'
                        || $tblGroup->getMetaTable() == 'CLUB') {
                        $MoreColumnInfo = ' '.new ToolTip(new Info(), 'Enthält gruppenspezifische Spalten');
                    }

                    $Item['Name'] = $tblGroup->getName().$MoreColumnInfo;
                    $Item['Count'] = Group::useService()->countMemberByGroup($tblGroup);
                    $Item['Option'] = new Standard(new Select(), '/Reporting/Standard/Person/GroupList', null, array(
                        'GroupId' => $tblGroup->getId()
                    ));
                    array_push($TableContent, $Item);
                });
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData(
                                    $TableContent, null, array('Name' => 'Name', 'Count' => 'Personen', 'Option' => '')
                                )
                            )
                        )
                    )
                )
            );
        } else {
            $tblGroup = Group::useService()->getGroupById($GroupId);
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/Standard/Person/GroupList', new ChevronLeft())
            );
            // TableData standard sort definition
            $ColumnDef = array(
                array('type' => 'german-string', 'targets' => 2),
                array('type' => 'german-string', 'targets' => 3),
                array('type' => 'de_date', 'targets' => 8)
            );
            $ColumnDefAdd = array();
            $ColumnHead = array();
            if ($tblGroup) {

                $ColumnStart = array(
                    'Number'                   => 'lfd. Nr.',
                    'Salutation'               => 'Anrede',
                    'FirstName'                => 'Vorname',
                    'LastName'                 => 'Nachname',
                    'Address'                  => 'Anschrift',
                    'PhoneNumber'              => 'Telefon Festnetz',
                    'MobilPhoneNumber'         => 'Telefon Mobil',
                    'Mail'                     => 'E-mail',
                    'Birthday'                 => 'Geburtstag',
                    'BirthPlace'               => 'Geburtsort',
                    'Gender'                   => 'Geschlecht',
                    'Nationality'              => 'Staatsangehörigkeit',
                    'Religion'                 => 'Konfession',
                    'ParticipationWillingness' => 'Mitarbeitsbereitschaft',
                    'ParticipationActivities'  => 'Mitarbeitsbereitschaft - Tätigkeiten',
                    'RemarkFrontend'           => 'Bemerkungen'
                );
                $ColumnCustom = array();
                if ($tblGroup->getMetaTable() == 'PROSPECT') {
                    $ColumnCustom = array(
                        'ReservationDate'     => 'Eingangsdatum',
                        'InterviewDate'       => 'Aufnahmegespräch',
                        'TrialDate'           => 'Schnuppertag',
                        'ReservationYear'     => 'Voranmeldung Schuljahr',
                        'ReservationDivision' => 'Voranmeldung Stufe',
                        'SchoolTypeA'         => 'Voranmeldung Schulart A',
                        'SchoolTypeB'         => 'Voranmeldung Schulart B'
                    );
                    $ColumnDefAdd = array(
                        array('type' => 'de_date', 'targets' => 16),
                        array('type' => 'de_date', 'targets' => 17),
                        array('type' => 'de_date', 'targets' => 18),
                    );
                }
                if ($tblGroup->getMetaTable() == 'STUDENT') {
                    $ColumnCustom = array(
                        'Identifier'           => 'Schülernummer',
                        'School'               => 'Schule',
                        'SchoolType'           => 'Schulart',
                        'SchoolCourse'         => 'Bildungsgang',
                        'Division'             => 'aktuelle Klasse',
                        'PictureSchoolWriting' => 'Einverständnis Foto Schulschriften',
                        'PicturePublication'   => 'Einverständnis Foto Veröffentlichungen',
                        'PictureWeb'           => 'Einverständnis Foto Internetpräsenz',
                        'PictureFacebook'      => 'Einverständnis Foto Facebookseite',
                        'PicturePrint'         => 'Einverständnis Foto Druckpresse',
                        'PictureFilm'          => 'Einverständnis Foto Ton/Video/Film',
                        'PictureAdd'           => 'Einverständnis Foto Werbung in eigener Sache',
                        'NameSchoolWriting'    => 'Einverständnis Name Schulschriften',
                        'NamePublication'      => 'Einverständnis Name Veröffentlichungen',
                        'NameWeb'              => 'Einverständnis Name Internetpräsenz',
                        'NameFacebook'         => 'Einverständnis Name Facebookseite',
                        'NamePrint'            => 'Einverständnis Name Druckpresse',
                        'NameFilm'             => 'Einverständnis Name Ton/Video/Film',
                        'NameAdd'              => 'Einverständnis Name Werbung in eigener Sache',
                    );
                }
                if ($tblGroup->getMetaTable() == 'CUSTODY') {
                    $ColumnCustom = array(
                        'Occupation' => 'Beruf',
                        'Employment' => 'Arbeitsstelle',
                        'Remark'     => 'Bemerkung Sorgeberechtigter',
                    );
                }
                if ($tblGroup->getMetaTable() == 'TEACHER') {
                    $ColumnCustom = array(
                        'TeacherAcronym' => 'Lehrerkürzel',
                    );
                }
                if ($tblGroup->getMetaTable() == 'CLUB') {
                    $ColumnCustom = array(
                        'ClubIdentifier' => 'Mitgliedsnummer',
                        'EntryDate'      => 'Eintrittsdatum',
                        'ExitDate'       => 'Austrittsdatum',
                        'ClubRemark'     => 'Bemerkung Vereinsmitglied',
                    );
                    $ColumnDefAdd = array(
                        array('type' => 'de_date', 'targets' => 17),
                        array('type' => 'de_date', 'targets' => 18),
                    );
                }
                // merge used column
                $ColumnHead = array_merge($ColumnStart, $ColumnCustom);
                // merge definition
                if (!empty($ColumnDefAdd)) {
                    $ColumnDef = array_merge($ColumnDef, $ColumnDefAdd);
                }

                $PersonList = Person::useService()->createGroupList($tblGroup);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/GroupList/Download', new Download(),
                            array('GroupId' => $tblGroup->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }

            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Panel('Gruppe:',
                                    $tblGroup->getName().
                                    (!empty($tblGroup->getDescription()) ? '<br/>' . $tblGroup->getDescription() : '').
                                    (!empty($tblGroup->getRemark()) ? '<br/>' . $tblGroup->getRemark() : ''),
                                    Panel::PANEL_TYPE_SUCCESS), 12
                            )
                        )
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    $ColumnHead,
                                    array(
                                        'order'      => array(
                                            array(3, 'asc'),
                                            array(2, 'asc')
                                        ),
                                        'columnDefs' => $ColumnDef,
                                        'pageLength' => -1,
                                        'responsive' => false
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Weiblich', array(
                                    'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: ' . count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        }

        return $Stage;
    }

    /**
     * @param Stage $Stage
     * @param $DivisionId
     *
     * @return Stage|string
     */
    public function showClassList(Stage $Stage, $DivisionId)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $PersonList = Person::useService()->createClassList($tblDivision);

        if ($tblDivision) {
            if ($PersonList) {
                $Stage->addButton(
                    new Primary('Herunterladen',
                        '/Api/Reporting/Standard/Person/ClassList/Download', new Download(),
                        array('DivisionId' => $tblDivision->getId()))
                );
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));

            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(new LayoutRow(array(
                        ($tblDivision->getServiceTblYear() ?
                            new LayoutColumn(
                                new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ) : ''),
                        new LayoutColumn(
                            new Panel('Klasse', $tblDivision->getDisplayName(),
                                Panel::PANEL_TYPE_SUCCESS), 4
                        ),
                        ($tblDivision->getTypeName() ?
                            new LayoutColumn(
                                new Panel('Schulart', $tblDivision->getTypeName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ) : ''),
                    ))),
                    new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(new TableData($PersonList, null,
                            array(
                                'Number'       => '#',
//                                'Salutation' => 'Anrede',
                                'FirstName'    => 'Vorname',
                                'LastName'     => 'Name',
                                'Gender'       => 'Geschlecht',
                                'Denomination' => 'Konfession',
                                'Birthday'     => 'Geburtsdatum',
                                'Birthplace'   => 'Geburtsort',
                                'Address'      => 'Adresse',
                                'Phone'        => new ToolTip('Telefon '.new Info(),
                                    'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                                'Mail'         => 'E-Mail',

                            ),
                            array(
                                "pageLength" => -1,
                                "responsive" => false
                            )
                        ))
                    ))),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Weiblich', array(
                                    'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: ' . count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        } else {
            return $Stage . new Danger('Klasse nicht gefunden.', new Ban());
        }

        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendInterestedPersonList()
    {

        $Stage = new Stage('Auswertung', 'Neuanmeldungen/Interessenten');
        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('PROSPECT'));
        $PersonList = Person::useService()->createInterestedPersonList();
        if ($PersonList) {
            $Stage->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Standard/Person/InterestedPersonList/Download', new Download())
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($PersonList, null,
                                array(
                                    'RegistrationDate' => 'Anmeldedatum',
                                    'InterviewDate'    => 'Aufnahmegespräch ',
                                    'TrialDate'        => 'Schnuppertag ',
                                    'FirstName'        => 'Vorname',
                                    'LastName'         => 'Name',
                                    'SchoolYear'    => 'Schuljahr',
                                    'DivisionLevel' => 'Klassenstufe',
                                    'TypeOptionA'   => 'Schulart 1',
                                    'TypeOptionB'   => 'Schulart 2',
                                    'Address'       => 'Adresse',
                                    'Birthday'      => 'Geburtsdatum',
                                    'Birthplace'    => 'Geburtsort',
                                    'Nationality'   => 'Staatsangeh.',
                                    'Denomination'  => 'Bekenntnis',
                                    'Siblings'      => 'Geschwister',
                                    'Father'        => 'Sorgeberechtigter 1',
                                    'Mother'        => 'Sorgeberechtigter 2',
                                    'Phone'         => 'Telefon Interessent',
                                    'PhoneGuardian' => 'Telefon Sorgeberechtigte',
                                    'MailGuardian'  => 'E-Mail Sorgeberechtigte',
                                    'Remark'        => 'Bemerkung',
                                ),
                                array(
                                    'order' => array(
                                        array(2, 'asc'),
                                        array(1, 'asc')
                                    ),
                                    "pageLength" => -1,
                                    "responsive" => false
                                )
                            )
                        )
                    )
                ),
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Weiblich', array(
                                'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Männlich', array(
                                'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4),
                        new LayoutColumn(
                            new Panel('Gesamt', array(
                                'Anzahl: ' . count($tblPersonList),
                            ), Panel::PANEL_TYPE_INFO)
                            , 4)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                null)
                        )
                    )
                ))
            ))
        );

        return $Stage;
    }

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendElectiveClassList($DivisionId = null)
    {

        $Stage = new Stage('Auswertung', 'Wahlfächer in Klassenlisten');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Standard/Person/ElectiveClassList',
                new ChevronLeft()));
        }
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $PersonList = array();

        if ($DivisionId !== null) {

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createElectiveClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/ElectiveClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Standard/Person/ElectiveClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        if ($DivisionId === null) {
            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($TableContent, null,
                                    array(
                                        'Year'     => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type'     => 'Schulart',
                                        'Count'    => 'Schüler',
                                        'Option'   => '',
                                    ), array(
                                        'order' => array(
                                            array(0, 'desc'),
                                            array(2, 'asc'),
                                            array(1, 'asc')
                                        )
                                    ))
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                )
            );
        } else {
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ($tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ($tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'           => '#',
                                        'Name'             => 'Name',
                                        'Birthday'         => 'Geb.-Datum',
                                        'Education'        => 'Bildungsgang',
                                        'ForeignLanguage1' => 'Fremdsprache 1',
                                        'ForeignLanguage2' => 'Fremdsprache 2',
                                        'ForeignLanguage3' => 'Fremdsprache 3',
                                        'Profile'          => 'Profil',
                                        'Orientation'      => 'Neigungskurs',
                                        'Religion'         => 'Religion',
                                        'Elective'         => 'Wahlfächer',
                                    ),
                                    array(
                                        "pageLength" => -1,
                                        "responsive" => false
                                    )
                                )
                            )
                        )
                    ),
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn(
                                new Panel('Weiblich', array(
                                    'Anzahl: '.Person::countFemaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Männlich', array(
                                    'Anzahl: '.Person::countMaleGenderByPersonList($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4),
                            new LayoutColumn(
                                new Panel('Gesamt', array(
                                    'Anzahl: '.count($tblPersonList),
                                ), Panel::PANEL_TYPE_INFO)
                                , 4)
                        )),
                        new LayoutRow(
                            new LayoutColumn(
                                (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null)
                            )
                        )
                    ))
                ))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Person
     * @param null $Year
     * @param null $Division
     *
     * @return Stage
     */
    public function frontendMetaDataComparison($Person = null, $Year = null, $Division = null) {
        $Stage = new Stage('Auswertung', 'Stammdatenabfrage');

        $FilterForm = $this->getStudentFilterForm();

        $Result = Person::useService()->getStudentFilterResult($Person, $Year, $Division);

        $TableContent = Person::useService()->getStudentTableContent($Result);

        $Table = new TableData($TableContent, null, array(
            'Division' => 'Klasse',
            'StudentNumber' => 'Schülernummer',
            'FirstName' => 'Vorname',
            'LastName' => 'Nachname',
            'Gender'  => 'Geschlecht',
            'Birthday'  => 'Geburtstag',
            'BirthPlace'  => 'Geburtsort',
            'Address' => 'Adresse',
            'Insurance' => 'Krankenkasse',
            'Religion'  => 'Religion',
            'PhoneFixedPrivate'  => 'Festnetz (Privat)',
            'PhoneFixedWork'  => 'Festnetz (Geschäftl.)',
            'PhoneFixedEmergency'  => 'Festnetz (Notfall)',
            'PhoneMobilePrivate'  => 'Mobil (Privat)',
            'PhoneMobileWork'  => 'Mobil (Geschäftl.)',
            'PhoneMobileEmergency'  => 'Mobil (Notfall)',
            'Sibling_1' => 'Geschwister1',
            'Sibling_2' => 'Geschwister2',
            'Sibling_3' => 'Geschwister3',

            'Custody_1_Salutation' => 'Sorg1 Anrede',
            'Custody_1_Title' => 'Sorg1 Titel',
            'Custody_1_FirstName' => 'Sorg1 Vorname',
            'Custody_1_LastName' => 'Sorg1 Nachname',
            'Custody_1_Address' => 'Sorg1 Adresse',
            'Custody_1_PhoneFixedPrivate' => 'Sorg1 Festnetz (Privat)',
            'Custody_1_PhoneFixedWork' => 'Sorg1 Festnetz (Geschäftl.)',
            'Custody_1_PhoneFixedEmergency' => 'Sorg1 Festnetz (Notfall)',
            'Custody_1_PhoneMobilePrivate' => 'Sorg1 Festnetz (Privat)',
            'Custody_1_PhoneMobileWork' => 'Sorg1 Festnetz (Geschäftl.)',
            'Custody_1_PhoneMobileEmergency' => 'Sorg1 Festnetz (Notfall)',
            'Custody_1_Mail_Private' => 'Sorg1 Mail (Privat)',
            'Custody_1_Mail_Work' => 'Sorg1 Mail (Geschäftl.)',

            'Custody_2_Salutation' => 'Sorg2 Anrede',
            'Custody_2_Title' => 'Sorg2 Titel',
            'Custody_2_FirstName' => 'Sorg2 Vorname',
            'Custody_2_LastName' => 'Sorg2 Nachname',
            'Custody_2_Address' => 'Sorg2 Adresse',
            'Custody_2_PhoneFixedPrivate' => 'Sorg2 Festnetz (Privat)',
            'Custody_2_PhoneFixedWork' => 'Sorg2 Festnetz (Geschäftl.)',
            'Custody_2_PhoneFixedEmergency' => 'Sorg2 Festnetz (Notfall)',
            'Custody_2_PhoneMobilePrivate' => 'Sorg2 Festnetz (Privat)',
            'Custody_2_PhoneMobileWork' => 'Sorg2 Festnetz (Geschäftl.)',
            'Custody_2_PhoneMobileEmergency' => 'Sorg2 Festnetz (Notfall)',
            'Custody_2_Mail_Private' => 'Sorg2 Mail (Privat)',
            'Custody_2_Mail_Work' => 'Sorg2 Mail (Geschäftl.)',
        ),
            array(
                'order'      => array(array(1, 'asc')),
                'columnDefs' => array(
                    array('type' => 'german-string', 'targets' => 1),
                ),
//                'pageLength' => -1,
//                'paging'     => false,
//                'searching'  => false,
                'responsive' => false,
            )
        );

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(new Well(
                            $FilterForm
                        ))
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            ApiUserAccount::receiverAccountModal()
                            .new Panel('Filterung',
                                (!empty($TableContent) ? new Primary('Herunterladen', '\Api\Reporting\Standard\Person\MetaDataComparison\Download', new Download(),
                                        array('Person' => $Person, 'Year' => $Year, 'Division' => $Division)).'<br /><br />' . $Table : new Warning('Keine Personen gefunden'))
                            )
                        )
                    )
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return Form
     */
    private function getStudentFilterForm()
    {
        $tblLevelShowList = array();

        $tblLevelList = Division::useService()->getLevelAll();
        if ($tblLevelList) {
            foreach ($tblLevelList as &$tblLevel) {
                if (!$tblLevel->getName()) {
                    $tblLevelClone = clone $tblLevel;
                    $tblLevelClone->setName('Stufenübergreifende Klassen');
                    $tblLevelShowList[] = $tblLevelClone;
                } else {
                    $tblLevelShowList[] = $tblLevel;
                }
            }
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Bildung', array(
                            (new SelectBox('Year['.ViewYear::TBL_YEAR_ID.']', 'Schuljahr',
                                array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                                ->setRequired(),
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_SERVICE_TBL_TYPE.']', 'Schulart',
                                array('Name' => Type::useService()->getTypeAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Klasse', array(
                            new SelectBox('Division['.ViewDivision::TBL_LEVEL_ID.']', 'Stufe',
                                array('{{ Name }} {{ serviceTblType.Name }}' => $tblLevelShowList)),
                            new AutoCompleter('Division['.ViewDivision::TBL_DIVISION_NAME.']', 'Gruppe',
                                'Klasse: Gruppe', array('Name' => Division::useService()->getDivisionAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                )),
                new FormRow(
                    new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Filtern')
                    )
                )
            ))
        );
    }
}
