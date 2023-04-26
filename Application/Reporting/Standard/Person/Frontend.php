<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use DateTime;
use SPHERE\Application\Api\Education\Division\DivisionTeacher;
use SPHERE\Application\Api\Reporting\Standard\ApiStandard;
use SPHERE\Application\Education\Graduation\Gradebook\MinimumGradeCount\SelectBoxItem;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\ViewDivision;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\ViewYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\ViewPeopleGroupMember;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Search\Group\Group;
use SPHERE\Application\Reporting\Individual\Individual;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Ban;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
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
use SPHERE\Common\Frontend\Table\Structure\TableColumn;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Table\Structure\TableHead;
use SPHERE\Common\Frontend\Table\Structure\TableRow;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Debugger;

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
                    array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
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
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
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
     * @param Stage $Stage
     * @param int   $DivisionId
     * @param bool  $showDownLoadButton
     *
     * @return Stage|string
     */
    public function showClassList(Stage $Stage, $DivisionId, $showDownLoadButton = true)
    {

        $tblDivision = Division::useService()->getDivisionById($DivisionId);
        $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);
        $PersonList = Person::useService()->createClassList($tblPersonList);

        if ($tblDivision) {
            if ($PersonList) {
                if($showDownLoadButton){
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/ClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                }
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));

            }

            $DivisionPanelContent = $this->getDivisionPanelContent($tblDivision);

            $HeadList = array(
                'Number'           => '#',
                'LastName'         => 'Name',
                'FirstName'        => 'Vorname',
                'Gender'           => 'Geschlecht',
                'Denomination'     => 'Konfession',
                'Birthday'         => 'Geburtsdatum',
                'Birthplace'       => 'Geburtsort',
                'Address'          => 'Adresse',
                'Phone'            => new ToolTip('Telefon ' . new Info(),
                    'p=Privat; g=Geschäftlich; n=Notfall; f=Fax; Bev.=Bevollmächtigt; Vorm.=Vormund; NK=Notfallkontakt'),
                'Mail'             => 'E-Mail',
                'ForeignLanguage1' => 'Fremdsprache 1',
                'ForeignLanguage2' => 'Fremdsprache 2',
                'ForeignLanguage3' => 'Fremdsprache 3',
                'Religion'         => 'Religion',
            );
            if(($tblLevel = $tblDivision->getTblLevel())){
                if(($tblType = $tblLevel->getServiceTblType())){
                    // Profil
                    if(($tblLevel->getName() == 8
                            || $tblLevel->getName() == 9
                            || $tblLevel->getName() == 10)
                        && $tblType->getName() == 'Gymnasium'){
                        $HeadList['Profile'] = 'Profil';
                    }
                    // Wahlbereich
                    if(($tblLevel->getName() == 7
                            || $tblLevel->getName() == 8
                            || $tblLevel->getName() == 9)
                        && $tblType->getName() == 'Mittelschule / Oberschule'){
                        $HeadList['Orientation'] = 'Wahlbereich';
                    }
                    // Wahlfach
                    if($tblLevel->getName() == 10
                        && $tblType->getName() == 'Mittelschule / Oberschule'){
                        $HeadList['Elective'] = 'Wahlfächer';
                    }

                }
            }

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(array(
                        new LayoutRow(array(
                            ($tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                            new LayoutColumn(
                                new Panel('Klasse', $DivisionPanelContent,
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ($tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : ''),
                        )),
                        ($inActivePanel = $this->getInActiveStudentPanel($tblDivision))
                            ? new LayoutRow(new LayoutColumn($inActivePanel))
                            : null
                    )),
                    new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(new TableData($PersonList, null,
                            $HeadList
                            , array(
                                'pageLength' => -1,
                                'responsive' => false,
                                'columnDefs' => array(
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                ),
                            )))
                    ))),
                    $this->getGenderLayoutGroup($tblPersonList)
                ))
            );
        } else {
            return $Stage . new Danger('Klasse nicht gefunden.', new Ban());
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

        $TableContent = array();
        if ($DivisionId === null) {
            $tblDivisionAll = Division::useService()->getDivisionAll();
            if ($tblDivisionAll) {
                array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent, &$IsAuthorized) {

                    $Item['Year'] = '';
                    $Item['Division'] = $tblDivision->getDisplayName();
                    $Item['Type'] = $tblDivision->getTypeName();
                    if ($tblDivision->getServiceTblYear()) {
                        $Item['Year'] = $tblDivision->getServiceTblYear()->getDisplayName();
                    }
                    $Item['Option'] = new Standard('', '/Reporting/Standard/Person/ExtendedClassList', new EyeOpen(),
                        array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
                    $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
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
                                        'Year' => 'Jahr',
                                        'Division' => 'Klasse',
                                        'Type' => 'Schulart',
                                        'Count' => 'Schüler',
                                        'Option' => '',
                                    ), array(
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
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
            $IsAuthorized = false;
            if ($tblDivision = Division::useService()->getDivisionById($DivisionId)) {
                $PersonList = Person::useService()->createExtendedClassList($tblDivision);
                if ($PersonList) {
                    foreach($PersonList as $Row){
                        if($Row['Authorized']){
                            $IsAuthorized = true;
                            break;
                        }
                    }
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Standard/Person/ExtendedClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
                $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

                $tableHead = array(
                    'Number'         => '#',
                    'StudentNumber'  => 'Schüler-Nr.',
                    'LastName'       => 'Name',
                    'FirstName'      => 'Vorname',
                    'Gender'         => 'Geschlecht',
                    'Address'        => 'Adresse',
                    'Birthday'       => 'Geburtsdatum',
                    'Birthplace'     => 'Geburtsort',
                    'Guardian1'      => 'Sorgeberechtigter 1',
                    'PhoneGuardian1' => 'Tel. Sorgeber. 1 '.
                        new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                    'Guardian2'      => 'Sorgeberechtigter 2',
                    'PhoneGuardian2' => 'Tel. Sorgeber. 2 '.
                        new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                    'Guardian3'      => 'Sorgeberechtigter 3',
                    'PhoneGuardian3' => 'Tel. Sorgeber. 3 '.
                        new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                );

                if($IsAuthorized){
                    $tableHead = array_merge($tableHead, array(
                        'Authorized'      => 'Bevollmächtigte(r)',
                        'PhoneAuthorized' => 'Tel. Bevollm. '.
                            new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax'),
                    ));
                }

                $tableHead['AuthorizedToCollect'] = 'Abholberechtigte';

                $DivisionPanelContent = $this->getDivisionPanelContent($tblDivision);

                $Stage->setContent(
                    new Layout(array(
                        new LayoutGroup(array(
                            new LayoutRow(array(
                                ($tblDivision->getServiceTblYear() ?
                                    new LayoutColumn(
                                        new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                            Panel::PANEL_TYPE_SUCCESS), 4
                                    ) : ''),
                                new LayoutColumn(
                                    new Panel('Klasse', $DivisionPanelContent,
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ),
                                ($tblDivision->getTypeName() ?
                                    new LayoutColumn(
                                        new Panel('Schulart', $tblDivision->getTypeName(),
                                            Panel::PANEL_TYPE_SUCCESS), 4
                                    ) : ''),
                            )),
                            ($inActivePanel = $this->getInActiveStudentPanel($tblDivision))
                                ? new LayoutRow(new LayoutColumn($inActivePanel))
                                : null
                        )),
                        new LayoutGroup(
                            new LayoutRow(
                                new LayoutColumn(
                                    new TableData($PersonList, null, $tableHead,
                                        array(
                                            "pageLength" => -1,
                                            "responsive" => false,
                                            'columnDefs' => array(
                                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                                            ),
                                        )
                                    )
                                )
                            )
                        ),
                        $this->getGenderLayoutGroup($tblPersonList)
                    ))
                );
            } else {
                $Stage->setContent(
                    new Warning('Klasse nicht verfügbar.')
                );
            }
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
                    array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
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
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
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
                    new LayoutGroup(array(
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
                        )),
                        ($inActivePanel = $this->getInActiveStudentPanel($tblDivision))
                            ? new LayoutRow(new LayoutColumn($inActivePanel))
                            : null
                    )),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number' => '#',
                                        'Name' => 'Name, Vorname',
                                        'Address' => 'Anschrift',
                                        'Birthplace' => 'Geburtsort',
                                        'Birth' => 'Geburtsdatum',
                                        'BirthDay' => 'Geburtstag',
                                        'BirthMonth' => 'Geburtsmonat',
                                        'BirthYear' => 'Geburtsjahr',
                                        'Age' => 'Alter',
                                    ),
                                    array(
                                        "pageLength" => -1,
                                        "responsive" => false,
                                        'columnDefs' => array(
                                            array('type' => 'de_date', 'targets' => 4),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                        )
                                    )
                                )
                            )
                        )
                    ),
                    $this->getGenderLayoutGroup($tblPersonList)
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
                    array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
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
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
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
                    new LayoutGroup(array(
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
                        )),
                        ($inActivePanel = $this->getInActiveStudentPanel($tblDivision))
                            ? new LayoutRow(new LayoutColumn($inActivePanel))
                            : null
                    )),
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
                                        "responsive" => false,
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                        ),
                                    )
                                )
                            )
                        )
                    ),
                    $this->getGenderLayoutGroup($tblPersonList)
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
                    $count = Group::useService()->countMemberByGroup($tblGroup);
                    if($count == 0){
                        $count .= ' ';
                    }
                    $Item['Count'] = $count;
                    $Item['Option'] = new Standard(new EyeOpen(), '/Reporting/Standard/Person/GroupList', null, array(
                        'GroupId' => $tblGroup->getId()
                    ), 'Anzeigen');

                    // nicht Gruppe Alle (Bei großen Schulen kommt hier eh ein Error 500)
                    if ($tblGroup->getMetaTable() != 'COMMON') {
                        array_push($TableContent, $Item);
                    }
                });
            }

            $Stage->setContent(
                new Layout(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData(
                                    $TableContent, null, array('Name' => 'Name', 'Count' => 'Personen', 'Option' => ''),
                                    array(
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => 1),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
                                        'order' => array(
                                            array(0, 'asc'),
                                        )
                                    )
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
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                array('type' => 'de_date', 'targets' => 8)
            );
            $ColumnDefAdd = array();
            $ColumnHead = array();
            if ($tblGroup) {

                $ColumnStart = array(
                    'Number'                   => 'lfd. Nr.',
                    'Salutation'               => 'Anrede',
                    'Title'                    => 'Titel',
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
                    'Division'                 => 'aktuelle Klasse',
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
                        array('type' => 'de_date', 'targets' => 17),
                        array('type' => 'de_date', 'targets' => 18),
                        array('type' => 'de_date', 'targets' => 19),
                    );
                }
                if ($tblGroup->getMetaTable() == 'STUDENT') {
                    $ColumnCustom = array(
                        'Identifier'           => 'Schülernummer',
                        'School'               => 'Schule',
                        'SchoolType'           => 'Schulart',
                        'SchoolCourse'         => 'Bildungsgang',
                        'Division'             => 'aktuelle Klasse',
                    );
                    //Agreement Head
                    if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                        foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                            $tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory);
                            foreach($tblAgreementTypeList as $tblAgreementType){
                                $ColumnCustom['AgreementType'.$tblAgreementType->getId()] = $tblAgreementType->getName();
                            }
                        }
                    }
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
                        array('type' => 'de_date', 'targets' => 18),
                        array('type' => 'de_date', 'targets' => 19),
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
                                    ($tblGroup->getDescription(true) ? '<br/>' . $tblGroup->getDescription(true) : '').
                                    ($tblGroup->getRemark() ? '<br/>' . $tblGroup->getRemark() : ''),
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
                    $this->getGenderLayoutGroup($tblPersonList)
                ))
            );
        }

        return $Stage;
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return bool|Panel
     */
    public function getInActiveStudentPanel(TblDivision $tblDivision)
    {
        $inActiveStudentList = array();
        if (($tblDivisionStudentAll = Division::useService()->getDivisionStudentAllByDivision($tblDivision, true))) {
            foreach ($tblDivisionStudentAll as $tblDivisionStudent) {
                if ($tblDivisionStudent->isInActive()
                    && ($tblPerson = $tblDivisionStudent->getServiceTblPerson())
                ) {
                    $inActiveStudentList[] = $tblPerson->getLastFirstName() . ' (Deaktivierung: ' . $tblDivisionStudent->getLeaveDate() . ')';
                }
            }
        }

        return empty($inActiveStudentList) ? false : new Panel('Ehemaliger Schüler dieser Klasse', $inActiveStudentList, Panel::PANEL_TYPE_WARNING);
    }

    /**
     * @return Stage
     */
    public function frontendInterestedPersonList()
    {

        $Stage = new Stage('Auswertung', 'Neuanmeldungen/Interessenten');
        $tblPersonList = Group::useService()->getPersonAllByGroup(Group::useService()->getGroupByMetaTable('PROSPECT'));
        $hasGuardian = false;
        $hasAuthorizedPerson = false;
        $PersonList = Person::useService()->createInterestedPersonList($hasGuardian, $hasAuthorizedPerson);
        if ($PersonList) {
            $Stage->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Standard/Person/InterestedPersonList/Download', new Download())
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }


        $Item['TransferCompany'] = $Item['TransferStateCompany'] = $Item['TransferType'] = $Item['TransferCourse'] = $Item['TransferDate'] = $Item['TransferRemark'] = '';

        $columns = array(
            'FirstName'            => 'Vorname',
            'LastName'             => 'Name',
            'RegistrationDate'     => 'Anmeldedatum',
            'InterviewDate'        => 'Aufnahmegespräch ',
            'TrialDate'            => 'Schnuppertag ',
            'SchoolYear'           => 'Schuljahr',
            'DivisionLevel'        => 'Klassenstufe',
            'TypeOptionA'          => 'Schulart 1',
            'TypeOptionB'          => 'Schulart 2',
            'TransferCompany'      => 'Abgebende Schule / Kita',
            'TransferStateCompany' => 'Staatliche Stammschule',
            'TransferType'         => 'Letzte Schulart',
            'TransferCourse'       => 'Letzter Bildungsgang',
            'TransferDate'         => 'Aufnahme Datum',
            'TransferRemark'       => 'Aufnahme Bemerkung',
            'Address'              => 'Adresse',
            'Birthday'             => 'Geburtsdatum',
            'Birthplace'           => 'Geburtsort',
            'Nationality'          => 'Staatsangeh.',
            'Denomination'         => 'Bekenntnis',
            'Siblings'             => 'Geschwister',
            'Custody1'             => 'Sorgeberechtigter 1',
            'Custody2'             => 'Sorgeberechtigter 2',
            'Custody3'             => 'Sorgeberechtigter 3'
        );

        if ($hasGuardian) {
            $columns['Guardian'] = 'Vormund';
        }
        if ($hasAuthorizedPerson) {
            $columns['AuthorizedPerson'] = 'Bevollmächtigter';
        }

        $columns['Phone'] = 'Telefon Interessent';
        $columns['Mail'] = 'E-Mail Interessent';
        $columns['PhoneGuardian'] = 'Telefon Sorgeberechtigte';
        $columns['MailGuardian'] = 'E-Mail Sorgeberechtigte';
        $columns['Remark'] = 'Bemerkung';

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($PersonList, null,
                                $columns,
                                array(
                                    'order' => array(
                                        array(4, 'asc'),
                                        array(3, 'asc')
                                    ),
                                    "pageLength" => -1,
                                    "responsive" => false,
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 4),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 14),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 15),
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 16),
                                    ),
                                )
                            )
                        )
                    )
                ),
                $this->getGenderLayoutGroup($tblPersonList)
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
                    array('DivisionId' => $tblDivision->getId()), 'Anzeigen');
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
                                        'columnDefs' => array(
                                            array('type' => 'natural', 'targets' => array(1,3)),
                                            array("orderable" => false, "targets"   => -1),
                                        ),
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
                    new LayoutGroup(array(
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
                        )),
                        ($inActivePanel = $this->getInActiveStudentPanel($tblDivision))
                            ? new LayoutRow(new LayoutColumn($inActivePanel))
                            : null
                    )),
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
                                        'Orientation'      => (Student::useService()->getStudentSubjectTypeByIdentifier('ORIENTATION'))->getName(),
                                        'Religion'         => 'Religion',
                                        'Elective'         => 'Wahlfächer',
                                        'Elective1'         => 'Wahlfach 1',
                                        'Elective2'         => 'Wahlfach 2',
                                        'Elective3'         => 'Wahlfach 3',
                                        'Elective4'         => 'Wahlfach 4',
                                        'Elective5'         => 'Wahlfach 5',
                                    ),
                                    array(
                                        "pageLength" => -1,
                                        "responsive" => false,
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                        ),
                                    )
                                )
                            )
                        )
                    ),
                    $this->getGenderLayoutGroup($tblPersonList)
                ))
            );
        }

        return $Stage;
    }

    /**
     * @param null $Person
     * @param null $Year
     * @param null $Division
     * @param null $Option
     * @param null $PersonGroup
     *
     * @return Stage
     */
    public function frontendMetaDataComparison($Person = null, $Year = null, $Division = null, $Option = null, $PersonGroup = null) {
        $Stage = new Stage('Auswertung', 'Stammdatenabfrage');

        $FilterForm = $this->getStudentFilterForm();

        $Result = Person::useService()->getStudentFilterResult($Person, $Year, $Division, $PersonGroup);

        $Service = Person::useService();
        $TableContent = $Service->getStudentTableContent($Result, $Option, $PersonGroup);
        $MetaComparisonList = $Service->getMetaComparisonList();

        $AddCount = 0;

        $TableHead = array();
        $TableHead['Division'] = 'Klasse';
        $TableHead['StudentNumber'] = 'Schülernummer';
        $TableHead['FirstName'] = 'Vorname';
        $TableHead['LastName'] = 'Nachname';
        $TableHead['Gender'] = 'Geschlecht';
        $TableHead['Birthday'] = 'Geburtsdatum';
        $TableHead['BirthPlace'] = 'Geburtsort';
        $TableHead['Nationality'] = 'Staatsangehörigkeit';
        $TableHead['Address'] = 'Adresse';
        $TableHead['Medication'] = 'Medikamente';
        $TableHead['InsuranceState'] = 'Versicherungsstatus';
        $TableHead['Insurance'] = 'Krankenkasse';
        $TableHead['Religion'] = 'Konfession';
        $TableHead['PhoneFixedPrivate'] = 'Festnetz (Privat)';
        $TableHead['PhoneFixedWork'] = 'Festnetz (Geschäftl.)';
        $TableHead['PhoneFixedEmergency'] = 'Festnetz (Notfall)';
        $TableHead['PhoneMobilePrivate'] = 'Mobil (Privat)';
        $TableHead['PhoneMobileWork'] = 'Mobil (Geschäftl.)';
        $TableHead['PhoneMobileEmergency'] = 'Mobil (Notfall)';
        $TableHead['MailPrivate'] = 'E-Mail Privat';
        $TableHead['MailWork'] = 'E-Mail Geschäftlich';
        if(isset($PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID])
            && $PersonGroup[ViewPeopleGroupMember::TBL_GROUP_ID] != '0'){
            $TableHead['PersonGroup'] = 'Personengruppe';
            $AddCount = 1;
        }
        $TableHead['Sibling_1'] = 'Geschwister1';
        $TableHead['Sibling_2'] = 'Geschwister2';
        $TableHead['Sibling_3'] = 'Geschwister3';

        $ColumnDef = array(
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
            // Sibling
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (22 + $AddCount)),
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (23 + $AddCount)),
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (24 + $AddCount)),
        );

        $SortCount = 0;
        foreach($MetaComparisonList as $Type => $TypeCount){
            if($TypeCount >= 1){
                for($i = 1; $i <= $TypeCount ; $i++) {
                    $TableHead[$Type.$i.'_Salutation'] = $Type.' '.$i.' Anrede';
                    $TableHead[$Type.$i.'_Title'] = $Type.' '.$i.' Titel';
                    $TableHead[$Type.$i.'_FirstName'] = $Type.' '.$i.' Vorname';
                    $TableHead[$Type.$i.'_LastName'] = $Type.' '.$i.' Nachname';
                    $TableHead[$Type.$i.'_Birthday'] = $Type.' '.$i.' Geburtsdatum';
                    $TableHead[$Type.$i.'_BirthPlace'] = $Type.' '.$i.' Geburtsort';
                    $TableHead[$Type.$i.'_Job'] = $Type.' '.$i.' Beruf';
                    $TableHead[$Type.$i.'_Address'] = $Type.' '.$i.' Adresse';
                    $TableHead[$Type.$i.'_PhoneFixedPrivate'] = $Type.' '.$i.' Festnetz (Privat)';
                    $TableHead[$Type.$i.'_PhoneFixedWork'] = $Type.' '.$i.' Festnetz (Geschäftl.)';
                    $TableHead[$Type.$i.'_PhoneFixedEmergency'] = $Type.' '.$i.' Festnetz (Notfall)';
                    $TableHead[$Type.$i.'_PhoneMobilePrivate'] = $Type.' '.$i.' Festnetz (Privat)';
                    $TableHead[$Type.$i.'_PhoneMobileWork'] = $Type.' '.$i.' Festnetz (Geschäftl.)';
                    $TableHead[$Type.$i.'_PhoneMobileEmergency'] = $Type.' '.$i.' Festnetz (Notfall)';
                    $TableHead[$Type.$i.'_Mail_Private'] = $Type.' '.$i.' Mail (Privat)';
                    $TableHead[$Type.$i.'_Mail_Work'] = $Type.' '.$i.' Mail (Geschäftl.)';
                    $ColumnDef[] = array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (27 + $AddCount + (16 * $SortCount)));
                    $ColumnDef[] = array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (28 + $AddCount + (16 * $SortCount)));
                    $SortCount++;
                }
            }
        }

        $Table = new TableData($TableContent, null, $TableHead,
            array(
                'order'      => array(array(1, 'asc')),
                'columnDefs' => $ColumnDef,
//                'columnDefs' => array(
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
//                    // Sibling
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (22 + $AddCount)),
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (23 + $AddCount)),
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (24 + $AddCount)),
//                    // Custody 1
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (27 + $AddCount)),
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (28 + $AddCount)),
//                    // Custody 2
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (43 + $AddCount)),
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (44 + $AddCount)),
//                    // Custody 3
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (59 + $AddCount)),
//                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (60 + $AddCount)),
//                ),
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
                            new Title('Filterung')
                            . (!empty($TableContent) ? new Primary('Herunterladen', '\Api\Reporting\Standard\Person\MetaDataComparison\Download', new Download(),
                                        array('Person' => $Person, 'Year' => $Year, 'Division' => $Division, 'Option' => $Option, 'PersonGroup' => $PersonGroup))
                                .'<br /><br />' . $Table : new Warning('Keine Personen gefunden'))
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
        $tblGroupList = Group::useService()->getGroupAll();

        if(!isset($_POST['PersonGroup']['TblGroup_Id'])){
            $_POST['PersonGroup']['TblGroup_Id'] = 1;
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
                    new FormColumn(
                        new Panel('Geschwister',
                            array(
                                new CheckBox('Option', 'ehemalige Geschwister mit anzeigen', '1')
                            )
                        , Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Gruppe',
                            new SelectBox('PersonGroup['.ViewPeopleGroupMember::TBL_GROUP_ID.']', 'Personengruppe', array('{{ Name }}' => $tblGroupList))
                        , Panel::PANEL_TYPE_INFO)
                    , 4)
                )),
                new FormRow(
                    new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Filtern')
                    )
                )
            ))
        );
    }

    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendAbsence($Data = null)
    {
        $stage = new Stage('Auswertung', 'Fehlzeiten');

        $stage->setMessage(
            new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation())
        );

        if ($Data == null) {
            $global = $this->getGlobal();
            $global->POST['Data']['Date'] = (new DateTime('now'))->format('d.m.Y');
            $global->savePost();
        }

        $receiverContent = ApiStandard::receiverBlock(
            (new ApiStandard())->reloadAbsenceContent(), 'AbsenceContent'
        );

        $certificateRelevantList[] = new SelectBoxItem(0, '');
        $certificateRelevantList[] = new SelectBoxItem(1, 'ja');
        $certificateRelevantList[] = new SelectBoxItem(2, 'nein');

        $datePickerFrom = new DatePicker('Data[Date]', '', 'Datum von', new Calendar());
        $datePickerTo = new DatePicker('Data[ToDate]', '', 'Datum bis', new Calendar());
        $typeSelectBox = new SelectBox('Data[Type]', 'Schulart', array('Name' => Type::useService()->getTypeAll()));
        $divisionTextField = new TextField('Data[DivisionName]', '', 'Klasse');
        $groupTextField = new TextField('Data[GroupName]', '', 'oder Personengruppe');
        $certificateRelevantSelectBox = new SelectBox('Data[IsCertificateRelevant]', 'Fehlzeit zeugnisrelevant',
            array('Name' => $certificateRelevantList));
        $optionAbsenceOnline = new CheckBox('Data[IsAbsenceOnline]', 'Nur unbearbeitete Online Fehlzeiten von Eltern/Schülern anzeigen', 1);
        $button = (new Primary('Filtern', '', new Filter()))->ajaxPipelineOnClick(ApiStandard::pipelineReloadAbsenceContent());

        $stage->setContent(
           new Form(new FormGroup(new FormRow(array(
                new FormColumn(
                    new Panel(
                        'Filter',
                        new Layout (new LayoutGroup(array(
                            new LayoutRow(array(
                                new LayoutColumn(
                                    $datePickerFrom, 4
                                ),
                                new LayoutColumn(
                                    $datePickerTo, 4
                                ),
                                new LayoutColumn(
                                    $certificateRelevantSelectBox, 4
                                ),
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    $typeSelectBox, 4
                                ),
                                new LayoutColumn(
                                    $divisionTextField, 4
                                ),
                                new LayoutColumn(
                                    $groupTextField, 4
                                ),
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    $optionAbsenceOnline . new Container('&nbsp;')
                                ),
                            )),
                            new LayoutRow(array(
                                new LayoutColumn(
                                    $button
                                ),
                            ))
                        ))),
                        Panel::PANEL_TYPE_INFO
                    )
                )
           ))))
            . $receiverContent
        );

        return $stage;
    }

    /**
     * @return Stage
     */
    public function frontendClub()
    {

        $Stage = new Stage('Auswertung', 'Fördervereinsmitgliedschaft');
        $PersonList = Person::useService()->createClubList();
        if ($PersonList) {
            $Stage->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Standard/Person/ClubList/Download', new Download())
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($PersonList, null,
                                array(
                                    'Number'                => 'Mitgliedsnummer',
                                    'Title'                 => 'Titel',
                                    'LastName'              => 'Sorgeberechtigt Name',
                                    'FirstName'             => 'Sorgeberechtigt Vorname',
                                    'StudentLastName'       => 'Schüler / Interessent Name',
                                    'StudentFirstName'      => 'Schüler / Interessent Vorname',
                                    'Type'                  => 'Typ',
                                    'Year'                  => 'Schuljahr',
                                    'activeDivision'        => 'Klasse',
                                    'individualPersonGroup' => 'Personengruppen',
                                ),
                                array(
                                    'order' => array(
                                        array(0, 'asc'),
                                        array(2, 'asc')
                                    ),
                                    "pageLength" => -1,
                                    "responsive" => false,
                                    'columnDefs' => array(
                                        array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(2, 3, 5, 6)),
                                    ),
                                )
                            )
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param string|null $YearId
     *
     * @return Stage
     */
    public function frontendStudentArchive(?string $YearId = null): Stage
    {

        $Stage = new Stage('Auswertung', 'Ehemalige Schüler');
        // aktuelle Schuljahre herausfiltern
        $yearList = array();
        if (($tblYearAll = Term::useService()->getYearAll())
            && ($tblYearListByNow = Term::useService()->getYearByNow())
        ) {
            foreach ($tblYearAll as $tblYear) {
                $isAdd = true;
                foreach ($tblYearListByNow as $tblYearNow) {
                    if ($tblYear->getId() == $tblYearNow->getId()) {
                        $isAdd = false;
                        break;
                    }
                }

                if ($isAdd) {
                    $yearList[] = $tblYear;
                }
            }
        } else {
            $yearList = $tblYearAll;
        }

        $Stage->setContent(
            (new SelectBox('YearId', 'Letztes Schuljahr der Schüler', array('{{ DisplayName }}' =>
                $yearList)))->setRequired()->ajaxPipelineOnChange(ApiStandard::pipelineLoadStudentArchiveContent($YearId))
            . ApiStandard::receiverBlock(
                new Warning('Bitte wählen Sie ein Schuljahr aus!'),
                'StudentArchiveContent'
            )
        );

        return $Stage;
    }

    public function frontendClassTeacher(?string $YearId = null): Stage
    {

        $Stage = new Stage('Auswertung', 'Klassenlehrer');
        $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        $Stage->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Standard/Person/DivisionTeacherList/Download', new Download())
        );

        list($TableContent, $headers) = Person::useService()->createDivisionTeacherList();

        $Stage->setContent(
            new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null, $headers,


                                array(
                                    'columnDefs' => array(
                                        array('type' => 'natural', 'targets' => array(0)),
                                        array("orderable" => false, "targets"   => -1),
                                    ),
                                    'order' => array(
                                      array(0, 'asc')
                                    ),
                                    'responsive' => false

                                ))
                            , 12)
                    ), new Title(new Listing() . ' Übersicht')
                )
            ));
        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return string
     */
    public function getStudentArchiveContent(TblYear $tblYear): string
    {
        if (($personList = Division::useService()->getLeaveStudents($tblYear))) {
            $dataList = Person::useService()->createStudentArchiveList($personList);

            return
                (new Primary('Herunterladen','/Api/Reporting/Standard/Person/StudentArchive/Download',
                    new Download(), array('YearId' => $tblYear->getId())))
                . (new Danger('Die dauerhafte Speicherung des Excel-Exports
                        ist datenschutzrechtlich nicht zulässig!', new Exclamation()))
                . (new TableData($dataList, null,
                    array(
                        'LastDivision' =>       'Abgangsklasse',
                        'LastName' =>           'Name',
                        'FirstName' =>          'Vorname',
                        'Gender' =>             'Geschlecht',
                        'Birthday' =>           'Geburtsdatum',
                        'Custody1Salutation' => 'Anrede Sorg1',
                        'Custody1FirstName' =>  'Vorname Sorg1',
                        'Custody1LastName' =>   'Nachname Sorg1',
                        'Custody2Salutation' => 'Anrede Sorg2',
                        'Custody2FirstName' =>  'Vorname Sorg2',
                        'Custody2LastName' =>   'Nachname Sorg2',
                        'Street' =>             'Straße',
                        'ZipCode' =>            'PLZ',
                        'City' =>               'Ort',
                        'LastSchool' =>         'Abgebende Schule',
                        'NewSchool' =>          'Aufnehmende Schule',
                        'LeaveDate' =>          'Abmeldedatum'
                    ),
                    array(
                        'order' => array(
                            array(0, 'asc'),
                            array(1, 'asc'),
                        ),
                        "pageLength" => -1,
                        "responsive" => false,
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 0),
                            array('type' => 'de_date', 'targets' => 16),
                                // dann ist die Tabelle leer, Api bringt Fehler
//                            array(
//                                'type' => Consumer::useService()->getGermanSortBySetting(),
//                                'targets' => array(1, 2)
//                            ),
                        ),
                    )
                ));
        }

        return new Warning('Für das Schuljahr: ' . $tblYear->getDisplayName(). ' wurden keine Abgänger gefunden.', new Exclamation());
    }

    /**
     * @param TblDivision $tblDivision
     *
     * @return string
     */
    private function getDivisionPanelContent(TblDivision $tblDivision)
    {

        $DivisionPanelContent = new Bold($tblDivision->getDisplayName());
        $DivisionPanelContent .= '<br/>';
        if(($tblDivisionTeacherList = Division::useService()->getDivisionTeacherAllByDivision($tblDivision))){
            $TeacherArray = array();
            foreach($tblDivisionTeacherList as $tblDivisionTeacher){
                if($tblPerson = $tblDivisionTeacher->getServiceTblPerson()){
                    $Description = $tblDivisionTeacher->getDescription();
                    $TeacherArray[] = $tblPerson->getFullName() . ($Description ? ' ' . new Muted($Description) : '');
                }
            }
            if(!empty($TeacherArray)){
                $DivisionPanelContent .= new Bold('Klassenlehrer: ').implode(', ', $TeacherArray);
                $DivisionPanelContent .= '<br/>';
            }
        }
        if(($tblDivisionRepresentationList = Division::useService()->getDivisionRepresentativeByDivision($tblDivision))){
            $Representation = array();
            foreach($tblDivisionRepresentationList as $tblDivisionRepresentation){
                $tblRepresentation = $tblDivisionRepresentation->getServiceTblPerson();
                $Description = $tblDivisionRepresentation->getDescription();
                $Representation[] = $tblRepresentation->getFirstSecondName().' '.$tblRepresentation->getLastName().' '.new Muted($Description);
            }
            $DivisionPanelContent .= new Bold('Klassensprecher: ').implode(', ', $Representation);
        }
        return $DivisionPanelContent;
    }

    /**
     * @param false|array $tblPersonList
     *
     * @return LayoutGroup
     */
    public function getGenderLayoutGroup($tblPersonList): LayoutGroup
    {

        if(false === $tblPersonList){
            $tblPersonList = array();
        }

        $Divers = Person::countDiversGenderByPersonList($tblPersonList);
        $DiversColumn = new LayoutColumn(
            new Panel('Divers', array(
                'Anzahl: ' . $Divers,
            ), Panel::PANEL_TYPE_INFO)
            , 2);
        $Other = Person::countOtherGenderByPersonList($tblPersonList);
        $OtherColumn = new LayoutColumn(
            new Panel('Ohne Angabe', array(
                'Anzahl: ' . $Other,
            ), Panel::PANEL_TYPE_INFO)
            , 2);

        return new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(
                    new Panel('Weiblich', array(
                        'Anzahl: ' . Person::countFemaleGenderByPersonList($tblPersonList),
                    ), Panel::PANEL_TYPE_INFO)
                    , 2),
                new LayoutColumn(
                    new Panel('Männlich', array(
                        'Anzahl: ' . Person::countMaleGenderByPersonList($tblPersonList),
                    ), Panel::PANEL_TYPE_INFO)
                    , 2),
                ($Divers ? $DiversColumn : ''),
                ($Other ? $OtherColumn : ''),
                new LayoutColumn(
                    new Panel('Gesamt', array(
                        'Anzahl: '.($tblPersonList ? count($tblPersonList) : 0),
                    ), Panel::PANEL_TYPE_INFO)
                    , 2)
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
        ));
    }

    /**
     * @param $Data
     *
     * @return Stage
     */
    public function frontendStudentAgreement($Data = array()) {
        $Stage = new Stage('Auswertung - Schüler', 'Einverständniserklärung');
        $FilterForm = $this->getPersonStudentFilterForm();

        $tblYear = $tblGroup = $tblType = false;
        if(!empty($Data['Year'])){
            $tblYear = Term::useService()->getYearById($Data['Year']);
        }
        if(!empty($Data['Group'])){
            $tblGroup = Group::useService()->getGroupById($Data['Group']);
        }
        if(!empty($Data['Type'])){
            $tblType = Type::useService()->getTypeById($Data['Type']);
        }
        $Level = !empty($Data['Level']) ? $Data['Level'] : '';
        $Division = !empty($Data['Division']) ? $Data['Division'] : '';
        $tblPersonList = null;
        $TableContent = array();
        if($tblYear){
            $tblPersonList = Individual::useService()->getStudentPersonListByFilter($tblYear, $tblGroup, $tblType,
                $Level, $Division);
            if($tblPersonList && !empty($tblPersonList)){
                $TableContent = Person::useService()->createAgreementList($tblPersonList);
            }
        }

        $ColumnHead = array(
            'FirstName'                => 'Vorname',
            'LastName'                 => 'Nachname',
            'Address'                  => '<div style="min-width: 160px">Anschrift</div>',
            'Birthday'                 => 'Geburtstag',
        );
        //Agreement Head
        if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
            foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                if (($tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory))) {
                    foreach ($tblAgreementTypeList as $tblAgreementType) {
                        $ColumnHead['AgreementType' . $tblAgreementType->getId()] = '<div style="min-width: 120px">' . $tblAgreementType->getName() . '</div>';
                    }
                }
            }
        }

        $tableData = new TableData($TableContent, null, $ColumnHead, array(
                'order'      => array(array(1, 'asc'), array(0, 'asc')),
                'columnDefs' => array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0,1)),
                array('type' => 'de_date', 'targets' => 3),
                'pageLength' => -1,
                'responsive' => false
            ));

        if ($tblAgreementCategoryAll) {
            $headTableColumnList = array();
            $headTableColumnList[] = new TableColumn('',4);
            foreach ($tblAgreementCategoryAll as $tblAgreementCategory) {
                if(($tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory))) {
                    $headTableColumnList[] = new TableColumn($tblAgreementCategory->getName(), count($tblAgreementTypeList));
                }
            }
            $tableData->prependHead(
                new TableHead(
                    new TableRow(
                        $headTableColumnList
                    )
                )
            );
        }

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
                            (null === $tblPersonList
                            ? new Warning('Bitte führen Sie die gewünschte Filterung aus')
                            : (false === $tblPersonList
                                ? new Danger('Filterung enthält keine Personen')
                                : new Primary('Download Einverständniserklärung', '/Api/Reporting/Standard/Person/AgreementStudentList/Download', new Download(),
                                    array('Data' => $Data))
                                )
                            )
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            ($TableContent && !empty($TableContent)
                                ? $tableData
                                : ''
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
    private function getPersonStudentFilterForm()
    {
        $tblLevelShowList = array(0 => '');

        if(($tblLevelList = Division::useService()->getLevelAll())){
            foreach($tblLevelList as $tblLevel) {
                if($tblLevel->getName()){
                    $tblLevelShowList[$tblLevel->getName()] = $tblLevel->getName();
                }
            }
        }
        $tblGroupList = Group::useService()->getGroupByNotLocked();

        if(!isset($_POST['Data']['Year'])){
            $tblYearList = Term::useService()->getYearByNow();
            if($tblYearList && count($tblYearList) == 1){
                $_POST['Data']['Year'] = current($tblYearList)->getId();
            }
        }

        return new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Bildung', array(
                            (new SelectBox('Data[Year]', 'Schuljahr',
                                array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                                ->setRequired(),
                            new SelectBox('Data[Type]', 'Schulart',
                                array('Name' => Type::useService()->getTypeAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Klasse', array(
                            new SelectBox('Data[Level]', 'Stufe', $tblLevelShowList),
                            new AutoCompleter('Data[Division]', 'Gruppe',
                                'Klasse: Gruppe', array('Name' => Division::useService()->getDivisionAll()))
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                    new FormColumn(
                        new Panel('Gruppe',
                            new SelectBox('Data[Group]', 'Personengruppe', array('{{ Name }}' => $tblGroupList))
                            , Panel::PANEL_TYPE_INFO)
                        , 4)
                )),
                new FormRow(
                    new FormColumn(
                        new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Filtern')
                    )
                )
            ))
        );
    }

    /**
     * @param $Data
     *
     * @return Stage
     */
    public function frontendAgreement($Data = array()) {
        $Stage = new Stage('Auswertung - Mitarbeiter', 'Einverständniserklärung');

        $TableContent = false;
        $tblGroup = \SPHERE\Application\People\Group\Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
        $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        if($tblPersonList && !empty($tblPersonList)){
            $TableContent = Person::useService()->createPersonAgreementList($tblPersonList);
        }

        $ColumnHead = array(
            'FirstName'                => 'Vorname',
            'LastName'                 => 'Nachname',
            'Address'                  => '<div style="min-width: 160px">Anschrift</div>',
            'Birthday'                 => 'Geburtstag',
        );
        //Agreement Head
        if(($tblAgreementCategoryAll = Agreement::useService()->getPersonAgreementCategoryAll())){
            foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                if (($tblAgreementTypeList = Agreement::useService()->getPersonAgreementTypeAllByCategory($tblAgreementCategory))) {
                    foreach ($tblAgreementTypeList as $tblAgreementType) {
                        $ColumnHead['AgreementType' . $tblAgreementType->getId()] = '<div style="min-width: 120px">' . $tblAgreementType->getName() . '</div>';
                    }
                }
            }
        }

        $tableData = new TableData($TableContent, null, $ColumnHead, array(
            'order'      => array(array(1, 'asc'), array(0, 'asc')),
            'columnDefs' => array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => array(0,1)),
            array('type' => 'de_date', 'targets' => 3),
            'pageLength' => -1,
            'responsive' => false
        ));

        if ($tblAgreementCategoryAll) {
            $headTableColumnList = array();
            $headTableColumnList[] = new TableColumn('',4);
            foreach ($tblAgreementCategoryAll as $tblAgreementCategory) {
                if(($tblAgreementTypeList = Agreement::useService()->getPersonAgreementTypeAllByCategory($tblAgreementCategory))) {
                    $headTableColumnList[] = new TableColumn($tblAgreementCategory->getName(), count($tblAgreementTypeList));
                }
            }
            $tableData->prependHead(
                new TableHead(
                    new TableRow(
                        $headTableColumnList
                    )
                )
            );
        }

        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            ($TableContent && !empty($TableContent)
                                ? new Primary('Download Einverständniserklärung', '/Api/Reporting/Standard/Person/AgreementPersonList/Download', new Download())
                                : ''
                            )
                        )
                    ),
                    new LayoutRow(
                        new LayoutColumn(
                            ($TableContent && !empty($TableContent)
                                ? $tableData
                                : ''
                            )
                        )
                    )
                ))
            )
        );

        return $Stage;
    }
}
