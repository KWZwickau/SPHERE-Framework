<?php
namespace SPHERE\Application\Reporting\Custom\Chemnitz\Person;

use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Reporting\Custom\Chemnitz\Person
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendPerson()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $View;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendClassList($DivisionId = null)
    {

        $View = new Stage('ESZC Auswertung', 'Klassenliste');
        if (null !== $DivisionId) {
            $View->addButton(new Standard('Zurück', '/Reporting/Custom/Chemnitz/Person/ClassList', new ChevronLeft()));
        }
        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $studentList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $studentList = Person::useService()->createClassList($tblDivision);
                if ($studentList) {
                    $View->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Chemnitz/Common/ClassList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
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
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Chemnitz/Person/ClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        $View->setContent(
            ( $DivisionId === null ?
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
                                        'Option'   => '',))
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                ) : '' )
            .( $DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    ( $tblDivision->getServiceTblYear() ?
                        new LayoutColumn(
                            new Panel('Jahr', $tblDivision->getServiceTblYear()->getName(),
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
                )))))
                .
                new TableData($studentList, null,
                    array(
                        'Salutation'   => 'Anrede',
                        'Father'       => 'Vorname Sorgeberechtigter 1',
                        'Mother'       => 'Vorname Sorgeberechtigter 2',
                        'LastName'     => 'Name',
                        'Denomination' => 'Konfession',
                        'Address'      => 'Adresse',
                        'FirstName'    => 'Schüler',
                        'Birthday'     => 'Geburtsdatum',
                        'Birthplace'   => 'Geburtsort',
                    ),
                    null
                ) : '' )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendStaffList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Liste der Mitarbeiter');

        $staffList = Person::useService()->createStaffList();
        if ($staffList) {
            $View->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Custom/Chemnitz/Common/StaffList/Download', new Download())
            );
            $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }

        $View->setContent(
            new TableData($staffList, null,
                array(
                    'Salutation' => 'Anrede',
                    'FirstName'  => 'Vorname',
                    'LastName'   => 'Name',
                    'Birthday'   => 'Geburtsdatum',
                    'Division'   => 'Unterbereich',
                    'Address'    => 'Adresse',
//                    'StreetName'         => 'Straße',
//                    'StreetNumber'         => 'Hausnr.',
//                    'Code'         => 'PLZ',
//                    'City'         => 'Ort',
                    'Phone1'     => 'Telefon 1',
                    'Phone2'     => 'Telefon 2',
                    'Mail'       => 'Mail',
                ),
                null
            )
        );

        return $View;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendMedicList($DivisionId = null)
    {

        $View = new Stage('ESZC Auswertung', 'Arztliste');
        if (null !== $DivisionId) {
            $View->addButton(new Standard('Zurück', '/Reporting/Custom/Chemnitz/Person/MedicList', new ChevronLeft()));
        }

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $studentList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $studentList = Person::useService()->createMedicList($tblDivision);
                if ($studentList) {
                    $View->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Chemnitz/Common/MedicList/Download', new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
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
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Chemnitz/Person/MedicList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        $View->setContent(
            ( $DivisionId === null ?
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
                                        'Option'   => '',))
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                ) : '' )
            .( $DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    ( $tblDivision->getServiceTblYear() ?
                        new LayoutColumn(
                            new Panel('Jahr', $tblDivision->getServiceTblYear()->getName(),
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
                )))))
                .
                new TableData($studentList, null,
                    array(
                        'LastName'  => 'Name',
                        'FirstName' => 'Vorname',
                        'Birthday'  => 'Geburtsdatum',
                        'Address'   => 'Adresse',
                    ),
                    null
                ) : '' )
        );

        return $View;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendParentTeacherConferenceList($DivisionId = null)
    {

        $View = new Stage('ESZC Auswertung', 'Liste für Elternabende');
        if (null !== $DivisionId) {
            $View->addButton(new Standard('Zurück', '/Reporting/Custom/Chemnitz/Person/ParentTeacherConferenceList', new ChevronLeft()));
        }

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $studentList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $studentList = Person::useService()->createParentTeacherConferenceList($tblDivision);
                if ($studentList) {
                    $View->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Chemnitz/Common/ParentTeacherConferenceList/Download',
                            new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
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
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Chemnitz/Person/ParentTeacherConferenceList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        $View->setContent(
            ( $DivisionId === null ?
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
                                        'Option'   => '',))
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                ) : '' )
            .( $DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    ( $tblDivision->getServiceTblYear() ?
                        new LayoutColumn(
                            new Panel('Jahr', $tblDivision->getServiceTblYear()->getName(),
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
                )))))
                .
                new TableData($studentList, null,
                    array(
                        'LastName'   => 'Name',
                        'FirstName'  => 'Vorname',
                        'Attendance' => 'Anwesenheit',
                    ),
                    null
                ) : '' )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendClubMemberList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Liste der Vereinsmitglieder');

        $clubMemberList = Person::useService()->createClubMemberList();
        if ($clubMemberList) {
            $View->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Custom/Chemnitz/Common/ClubMemberList/Download', new Download())
            );
            $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }

        $View->setContent(
            new TableData($clubMemberList, null,
                array(
                    'Salutation'  => 'Anrede',
                    'FirstName'   => 'Vorname',
                    'LastName'    => 'Name',
                    'Address'     => 'Adresse',
//                    'StreetName'         => 'Straße',
//                    'StreetNumber'         => 'Hausnr.',
//                    'Code'         => 'PLZ',
//                    'City'         => 'Ort',
                    'Phone'       => 'Telefon',
                    'Mail'        => 'Mail',
                    'Directorate' => 'Vorstand'
                ),
                null
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendInterestedPersonList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Neuanmeldungen/Interessenten');

        $interestedPersonList = Person::useService()->createInterestedPersonList();
        if ($interestedPersonList) {
            $View->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Custom/Chemnitz/Common/InterestedPersonList/Download', new Download())
            );
            $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }

        $View->setContent(
            new TableData($interestedPersonList, null,
                array(
                    'RegistrationDate' => 'Anmeldedatum',
                    'FirstName'        => 'Vorname',
                    'LastName'         => 'Name',
                    'SchoolYear'       => 'Schuljahr',
                    'DivisionLevel'    => 'Klassenstufe',
                    'TypeOptionA'      => 'Schulart 1',
                    'TypeOptionB'      => 'Schulart 2',
                    'Address'          => 'Adresse',
//                    'StreetName'         => 'Straße',
//                    'StreetNumber'         => 'Hausnummer',
//                    'Code'         => 'PLZ',
//                    'City'         => 'Ort',
                    'Birthday'         => 'Geburtsdatum',
                    'Birthplace'       => 'Geburtsort',
                    'Nationality'      => 'Staatsangeh.',
                    'Denomination'     => 'Bekenntnis',
                    'Siblings'         => 'Geschwister',
                    'Hoard'            => 'Hort',
                    'Father'           => 'Sorgeberechtigter 1',
//                    'FatherSalutation'         => 'Anrede V',
//                    'FatherLastName'         => 'Name V',
//                    'FatherFirstName'         => 'Vorname V',
                    'Mother'           => 'Sorgeberechtigter 2',
//                    'MotherSalutation'         => 'Anrede M',
//                    'MotherLastName'         => 'Name M',
//                    'MotherFirstName'         => 'Vorname M',
                ),
                null
            )
        );

        return $View;
    }

    /**
     * @return Stage
     */
    public function frontendSchoolFeeList()
    {

        $View = new Stage();
        $View->setTitle('ESZC Auswertung');
        $View->setDescription('Schulgeldliste');

        $View->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/Custom/Chemnitz/Common/SchoolFeeList/Download', new Download())
        );
        $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));

        $studentList = Person::useService()->createSchoolFeeList();

        $View->setContent(
            new TableData($studentList, null,
                array(
                    'DebtorNumber'  => 'Deb.-Nr.',
                    'Reply'         => 'Bescheid geschickt',
                    'Father'        => 'Sorgeberechtigter 1',
//                    'FatherSalutation'     => 'Anrede V',
//                    'FatherLastName'         => 'Name V',
//                    'FatherFirstName'         => 'Vorname V',
                    'Mother'        => 'Sorgeberechtigter 2',
//                    'MotherSalutation'         => 'Anrede M',
//                    'MotherLastName'         => 'Name M',
//                    'MotherFirstName'         => 'Vorname M',
                    'Records'       => 'Unterlagen eingereicht',
                    'LastSchoolFee' => 'SG Vorjahr',
                    'Remarks'       => 'Bemerkungen',
                    'Address'       => 'Adresse',
//                    'StreetName'         => 'Straße',
//                    'StreetNumber'         => 'Hausnummer',
//                    'Code'         => 'PLZ',
//                    'City'         => 'Ort',
                ),
                null
            )
        );

        return $View;
    }

    /**
     * @param $DivisionId
     *
     * @return Stage
     */
    public function frontendPrintClassList($DivisionId = null)
    {

        $View = new Stage('ESZC Auswertung', 'Klassenliste zum Ausdrucken');
        if (null !== $DivisionId) {
            $View->addButton(new Standard('Zurück', '/Reporting/Custom/Chemnitz/Person/PrintClassList', new ChevronLeft()));
        }

        $tblDivisionAll = Division::useService()->getDivisionAll();
        $tblDivision = new TblDivision();
        $studentList = array();

        if ($DivisionId !== null) {

            $Global = $this->getGlobal();
            if (!$Global->POST) {
                $Global->POST['Select']['Division'] = $DivisionId;
                $Global->savePost();
            }

            $tblDivision = Division::useService()->getDivisionById($DivisionId);
            if ($tblDivision) {
                $studentList = Person::useService()->createPrintClassList($tblDivision);
                if ($studentList) {
                    $View->addButton(
                        new Primary('Herunterladen',
                            '/Api/Reporting/Custom/Chemnitz/Common/PrintClassList/Download',
                            new Download(),
                            array('DivisionId' => $tblDivision->getId()))
                    );
                    $View->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
        }

        $tableData = ( $tableData = new TableData($studentList, null,
            array(
                'DisplayName'  => 'Name',
                'Birthday'     => 'Geb.-Datum',
                'Address'      => 'Adresse',
                'PhoneNumbers' => 'Telefonnummer',
                'Education'    => 'Bildungsgang',
                'Orientation'  => 'NK',
            ),
            null
        ) );

        $TableContent = array();
        if ($tblDivisionAll) {
            array_walk($tblDivisionAll, function (TblDivision $tblDivision) use (&$TableContent) {

                $Item['Year'] = '';
                $Item['Division'] = $tblDivision->getDisplayName();
                $Item['Type'] = $tblDivision->getTypeName();
                if ($tblDivision->getServiceTblYear()) {
                    $Item['Year'] = $tblDivision->getServiceTblYear()->getName();
                }
                $Item['Option'] = new Standard('', '/Reporting/Custom/Chemnitz/Person/PrintClassList', new EyeOpen(),
                    array('DivisionId' => $tblDivision->getId()));
                $Item['Count'] = Division::useService()->countDivisionStudentAllByDivision($tblDivision);
                array_push($TableContent, $Item);
            });
        }

        $View->setContent(
            ( $DivisionId === null ?
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
                                        'Option'   => '',))
                                , 12)
                        ), new Title(new Listing().' Übersicht')
                    )
                ) : '' )
            .( $DivisionId !== null ?
                (new Layout(new LayoutGroup(new LayoutRow(array(
                    ( $tblDivision->getServiceTblYear() ?
                        new LayoutColumn(
                            new Panel('Jahr', $tblDivision->getServiceTblYear()->getName(),
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
                )))))
                .$tableData
                : '' )
        );

//        if ($DivisionId !== null) {
//            /** @var DomPdf $Document */
//            $Document = Document::getDocument('Roadmap.pdf');
//            $Document->setContent(
//                Template::getTwigTemplateString($tableData)
//            );
//
//            $Document->saveFile();
//        }

        return $View;
    }
}
