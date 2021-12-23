<?php

namespace SPHERE\Application\Reporting\Custom\Gersdorf\Person;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Gersdorf\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendClassList($DivisionId = null): Stage
    {

        $Stage = new Stage('EVOSG Auswertung', 'Klassenlisten');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Gersdorf/Person/ClassList', new ChevronLeft()));
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
                $PersonList = Person::useService()->createClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Gersdorf/Common/ClassList/Download', new Download(),
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Gersdorf/Person/ClassList', new EyeOpen(),
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
                                        'Option'   => ''
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
                                    )
                                )
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
                            ( $tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ( $tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'       => '#',
                                        'LastName'     => 'Name',
                                        'FirstName'    => 'Vorname',
                                        'Birthday'     => 'Geb.-datum',
                                        'Birthplace'   => 'Geburtsort',
                                        'District'     => 'Ortsteil',
                                        'StreetName'   => 'Straße',
                                        'StreetNumber' => 'Nr.',
                                        'Code'         => 'PLZ',
                                        'City'         => 'Ort',
                                    ),
                                    array(
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
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
                                ( Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null )
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
    public function frontendSignList($DivisionId = null): Stage
    {
        $Stage = new Stage('EVOSG Auswertung', 'Unterschriften Liste');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Gersdorf/Person/SignList', new ChevronLeft()));
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
                $PersonList = Person::useService()->createSignList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Gersdorf/Common/SignList/Download', new Download(),
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Gersdorf/Person/SignList', new EyeOpen(),
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
                                        'Option'   => ''
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
                                    )
                                )
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
                            ( $tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ( $tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'        => '#',
                                        'LastName'  => 'Name',
                                        'FirstName' => 'Vorname',
//                                        'Empty'     => 'Unterschrift',
                                    ),
                                    array(
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
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
                                ( Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null )
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
    public function frontendElectiveClassList($DivisionId = null): Stage
    {

        $Stage = new Stage('Auswertung', 'Wahlfächer in Klassenlisten');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Standard/Person/ElectiveClassList',
                new ChevronLeft()));
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Gersdorf/Person/ElectiveList', new EyeOpen(),
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
            $PersonList = array();
            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createElectiveClassList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Gersdorf/Common/ElectiveClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }

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
//                                        'Profile'          => 'Profil',
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
     * @param null $DivisionId
     *
     * @return Stage
     */
    public function frontendClassPhoneList($DivisionId = null)
    {

        $Stage = new Stage('EVOSG Auswertung', 'Telefonlisten');
        if (null !== $DivisionId) {
            $Stage->addButton(new Standard('Zurück', '/Reporting/Custom/Gersdorf/Person/ClassPhoneList', new ChevronLeft()));
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
                $Item['Option'] = new Standard('', '/Reporting/Custom/Gersdorf/Person/ClassPhoneList', new EyeOpen(),
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
                                        'Option'   => ''
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
                                    )
                                )
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                )
            );
        } else {
            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }
            $PersonList = array();
            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $PersonList = Person::useService()->createClassPhoneList($tblDivision);
                if ($PersonList) {
                    $Stage->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Gersdorf/Common/ClassPhoneList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
            $tblPersonList = Division::useService()->getStudentAllByDivision($tblDivision);

            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(array(
                            ( $tblDivision->getServiceTblYear() ?
                                new LayoutColumn(
                                    new Panel('Jahr', $tblDivision->getServiceTblYear()->getDisplayName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                            new LayoutColumn(
                                new Panel('Klasse', $tblDivision->getDisplayName(),
                                    Panel::PANEL_TYPE_SUCCESS), 4
                            ),
                            ( $tblDivision->getTypeName() ?
                                new LayoutColumn(
                                    new Panel('Schulart', $tblDivision->getTypeName(),
                                        Panel::PANEL_TYPE_SUCCESS), 4
                                ) : '' ),
                        ))
                    ),
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new TableData($PersonList, null,
                                    array(
                                        'Number'        => '#',
                                        'LastName'     => 'Name',
                                        'FirstName'    => 'Vorname',
                                        'PhoneNumbers' => 'Telefon-Nr.',
                                        'S1PrivatePhoneNumbers'    => 'S1 Privat',
                                        'S1Business'   => 'S1 Geschäftlich',
                                        'S2Private'    => 'S2 Privat',
                                        'S2Business'   => 'S2 Geschäftlich',
                                    ),
                                    array(
                                        'columnDefs' => array(
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
                                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
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
                                ( Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                                    new Warning(new Child().' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                                    entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                                    in den Stammdaten der Personen.') :
                                    null )
                            )
                        )
                    ))
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
}