<?php
namespace SPHERE\Application\Reporting\Custom\Chemnitz\Person;

use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Reporting\Standard\Person\Person as PersonStandard;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
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

        $Stage = new Stage();
        $Stage->setTitle('ESZC Auswertung');
        $Stage->setDescription('Bitte wählen Sie eine Liste zur Auswertung');

        return $Stage;
    }

    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendClassList(?int $DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('ESZC Auswertung', 'Klassenlisten');
        $Route = '/Reporting/Custom/Chemnitz/Person/ClassList';
        if($DivisionCourseId === null) {
            if($All) {
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent(PersonStandard::useFrontend()->getChooseDivisionCourse($Route, $All));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
        if(!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
        }
        if(!($tblPersonList = $tblDivisionCourse->getStudents())){
            return $Stage->setContent(new Warning('Keine Schüler hinterlegt.'));
        }
        $TableContent = Person::useService()->createClassList($tblPersonList);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Chemnitz/Common/ClassList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId()))
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(
                PersonStandard::useFrontend()->getDivisionHeadOverview($tblDivisionCourse),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number'       => '#',
                            'Salutation'   => 'Anrede',
                            'FirstNameS1'  => 'Vorname Sorgeberechtigter 1',
                            'FirstNameS2'  => 'Vorname Sorgeberechtigter 2',
                            'LastName'     => 'Name',
                            'Denomination' => 'Konfession',
                            'Address'      => 'Adresse',
                            'FirstName'    => 'Schüler',
                            'Birthday'     => 'Geburtsdatum',
                            'Birthplace'   => 'Geburtsort',
                        ),
                        array(
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 4),
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 7),
                            ),
                            "pageLength" => -1,
                            "responsive" => false
                        )
                    )
                ))),
                PersonStandard::useFrontend()->getGenderFooter($tblDivisionCourse)
            ))
        );
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendStaffList()
    {

        $Stage = new Stage('ESZC Auswertung', 'Liste der Mitarbeiter');
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STAFF);
        $tblPersonList = $tblGroup->getPersonList();
        $TableContent = Person::useService()->createStaffList();
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Chemnitz/Common/StaffList/Download', new Download()));
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null,
                    array(
                        'Salutation'   => 'Anrede',
                        'Title'        => 'Titel',
                        'FirstName'    => 'Vorname',
                        'LastName'     => 'Name',
                        'Birthday'     => 'Geburtsdatum',
                        'Division'     => 'Unterbereich',
                        'Address'      => 'Adresse',
                        'Phone1'       => 'Telefon 1',
                        'Phone2'       => 'Telefon 2',
                        'Mail'         => 'Mail',
                    ),
                    array(
                        'order' => array(
                            array(3, 'asc'),
                            array(2, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2, 3),
                        ),
                        "pageLength" => -1,
                        "responsive" => false
                    )
                )
            ))), PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)))
        );
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendSchoolFeeList()
    {

        $Stage = new Stage('ESZC Auswertung', 'Schulgeldliste');
        $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Chemnitz/Common/SchoolFeeList/Download', new Download()));
        $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_STUDENT);
        $tblPersonList = $tblGroup->getPersonList();
        $TableContent = Person::useService()->createSchoolFeeList($tblPersonList);
        $Stage->setContent(
            new Layout(array(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null,
                    array(
                        'DebtorNumber'  => 'Deb.-Nr.',
                        'Reply'         => 'Bescheid geschickt',
                        'FullNameS1'    => 'Sorgeberechtigter 1',
                        'FullNameS2'    => 'Sorgeberechtigter 2',
                        'Records'       => 'Unterlagen eingereicht',
                        'LastSchoolFee' => 'SG Vorjahr',
                        'Remarks'       => 'Bemerkungen',
                        'Address'       => 'Adresse',
                    ),
                    array(
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                        ),
                        "pageLength" => -1,
                        "responsive" => false
                    )
                )
            ))), PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)))
        );
        return $Stage;
    }

    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendMedicList(?int $DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('ESZC Auswertung', 'Arztliste');
        $Route = '/Reporting/Custom/Chemnitz/Person/MedicList';
        if($DivisionCourseId === null) {
            if($All) {
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent(PersonStandard::useFrontend()->getChooseDivisionCourse($Route, $All));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
        if(!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
        }
        if(!($tblPersonList = $tblDivisionCourse->getStudents())){
            return $Stage->setContent(new Warning('Keine Schüler hinterlegt.'));
        }
        $TableContent = Person::useService()->createMedicList($tblDivisionCourse);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Chemnitz/Common/MedicList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId()))
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(
                PersonStandard::useFrontend()->getDivisionHeadOverview($tblDivisionCourse),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number' => '#',
                            'LastName' => 'Name',
                            'FirstName' => 'Vorname',
                            'Birthday' => 'Geburtsdatum',
                            'Address' => 'Adresse',
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
                ))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
            ))
        );
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendInterestedPersonList()
    {

        $Stage = new Stage('ESZC Auswertung', 'Neuanmeldungen/Interessenten');
        $tblGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_PROSPECT);
        $tblPersonList = $tblGroup->getPersonList();
        $TableContent = Person::useService()->createInterestedPersonList($tblPersonList);
        if (!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Chemnitz/Common/InterestedPersonList/Download', new Download()));
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null,
                    array(
                        'RegistrationDate' => 'Anmeldedatum',
                        'FirstName'        => 'Vorname',
                        'LastName'         => 'Name',
                        'SchoolYear'       => 'Schuljahr',
                        'DivisionLevel'    => 'Klassenstufe',
                        'TypeOptionA'      => 'Schulart 1',
                        'TypeOptionB'      => 'Schulart 2',
                        'Address'          => 'Adresse',
                        'Birthday'         => 'Geburtsdatum',
                        'Birthplace'       => 'Geburtsort',
                        'Nationality'      => 'Staatsangeh.',
                        'Denomination'     => 'Bekenntnis',
                        'Siblings'         => 'Geschwister',
                        'Hoard'            => 'Hort',
                        'FullNameS1'       => 'Sorgeberechtigter 1',
                        'FullNameS2'       => 'Sorgeberechtigter 2',
                        'Phone'            => 'Telefon Interessent',
                        'PhoneGuardian'    => 'Telefon Sorgeb.',
                    ),
                    array(
                        'order' => array(
                            array(2, 'asc'),
                            array(1, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1, 2, 12, 14, 15),
                        ),
                        "pageLength" => -1,
                        "responsive" => false
                    )
                )
            ))), PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)))
        );

        return $Stage;
    }

    /**
     * @param int|null $DivisionCourseId
     * @param null     $All
     *
     * @return Stage
     */
    public function frontendParentTeacherConferenceList(?int $DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('ESZC Auswertung', 'Liste für Elternabende');
        $Route = '/Reporting/Custom/Chemnitz/Person/ParentTeacherConferenceList';
        if($DivisionCourseId === null) {
            if($All) {
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent(PersonStandard::useFrontend()->getChooseDivisionCourse($Route, $All));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
        if(!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
        }
        if(!($tblPersonList = $tblDivisionCourse->getStudents())){
            return $Stage->setContent(new Warning('Keine Schüler hinterlegt.'));
        }
        $TableContent = Person::useService()->createParentTeacherConferenceList($tblDivisionCourse);
        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Chemnitz/Common/ParentTeacherConferenceList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId()))
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(
                PersonStandard::useFrontend()->getDivisionHeadOverview($tblDivisionCourse),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number' => '#',
                            'LastName' => 'Name',
                            'FirstName' => 'Vorname',
                            'Attendance' => 'Anwesenheit',
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
                ))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
            ))
        );
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendClubMemberList()
    {

        $Stage = new Stage('ESZC Auswertung', 'Liste der Vereinsmitglieder');
        $tblPersonList = array();
        $PersonList = array();
        if (($tblClubGroup = Group::useService()->getGroupByMetaTable(TblGroup::META_TABLE_CLUB))) {
            $tblPersonList = $tblClubGroup->getPersonList();
        }
        if(!empty($tblPersonList)){
            $PersonList = Person::useService()->createClubMemberList($tblPersonList);
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Chemnitz/Common/ClubMemberList/Download', new Download()));
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $Stage->setContent(
            new Layout(array(new LayoutGroup(new LayoutRow(new LayoutColumn(
                new TableData($PersonList, null,
                    array(
                        'Salutation'   => 'Anrede',
                        'Title'        => 'Titel',
                        'FirstName'    => 'Vorname',
                        'LastName'     => 'Name',
                        'Address'      => 'Adresse',
                        'Phone'        => 'Telefon',
                        'Mail'         => 'Mail',
                        'Directorate'  => 'Vorstand'
                    ),
                    array(
                        'order' => array(
                            array(2, 'asc'),
                            array(1, 'asc'),
                        ),
                        'columnDefs' => array(
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                        ),
                        "pageLength" => -1,
                        "responsive" => false
                    )
                )))), PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)))
        );
        return $Stage;
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Stage
     */
    public function frontendPrintClassList(?int $DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('ESZC Auswertung', 'Klassenliste zum Ausdrucken');
        $Route = '/Reporting/Custom/Chemnitz/Person/PrintClassList';
        if($DivisionCourseId === null) {
            if($All) {
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent(PersonStandard::useFrontend()->getChooseDivisionCourse($Route, $All));
            return $Stage;
        }
        $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
        if(!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
            return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
        }
        if(!($tblPersonList = $tblDivisionCourse->getStudents())){
            return $Stage->setContent(new Warning('Keine Schüler hinterlegt.'));
        }


        $TableContent = Person::useService()->createPrintClassList($tblDivisionCourse);

        if(!empty($TableContent)) {
            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Chemnitz/Common/PrintClassList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId())));
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exportsist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }


//        $TableContent = Person::useService()->createParentTeacherConferenceList($tblDivisionCourse);
//        if(!empty($TableContent)) {
//            $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Custom/Chemnitz/Common/ParentTeacherConferenceList/Download', new Download(),
//                    array('DivisionCourseId' => $tblDivisionCourse->getId()))
//            );
//            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
//        }
//        $Stage->setContent(
//            new Layout(array(
//                PersonStandard::useFrontend()->getDivisionHeadOverview($tblDivisionCourse),
//                new LayoutGroup(new LayoutRow(new LayoutColumn(
//                    new TableData($TableContent, null,
//                        array(
//                            'Number' => '#',
//                            'LastName' => 'Name',
//                            'FirstName' => 'Vorname',
//                            'Attendance' => 'Anwesenheit',
//                        ),
//                        array(
//                            'columnDefs' => array(
//                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 1),
//                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
//                            ),
//                            "pageLength" => -1,
//                            "responsive" => false
//                        )
//                    )
//                ))),
//                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList)
//            ))
//        );
//        return $Stage;
        $CustodyString = '';
        /** @var TblPerson $tblCustody */
        if (!empty($tblPersonCustodyList = $tblDivisionCourse->getCustody())) {
            foreach ($tblPersonCustodyList as $tblCustody) {
                if (!empty($CustodyString)) {
                    $CustodyString .= ', '.$tblCustody->getFullName();
                } else {
                    $CustodyString .= $tblCustody->getFullName();
                }
            }
        }

        $IntegrationStudent = false;
        $CounterDivisionGroup1 = 0;
        $CounterDivisionGroup2 = 0;
        $OrientationList = array();
        $FrenchCounter = 0;
        $EducationList = array();
        if($TableContent) {
            foreach($TableContent as $PersonData) {
                if(isset($PersonData['Integration']) && $PersonData['Integration'] == 1) {
                    $IntegrationStudent = true;
                }
                if(!empty($PersonData['Group'])) {
                    if($PersonData['Group1']) {
                        $CounterDivisionGroup1++;
                    }
                    if($PersonData['Group2']) {
                        $CounterDivisionGroup2++;
                    }
                }
                if(!empty($PersonData['Orientation'])) {
                    if(isset($OrientationList[$PersonData['Orientation']])) {
                        $OrientationList[$PersonData['Orientation']] += 1;
                    } else {
                        $OrientationList[$PersonData['Orientation']] = 1;
                    }
                }
                if(!empty($PersonData['French'])) {
                    $FrenchCounter++;
                }
                if(!empty($PersonData['Education'])) {
                    if(isset($EducationList[$PersonData['Education']])) {
                        $EducationList[$PersonData['Education']] += 1;
                    } else {
                        $EducationList[$PersonData['Education']] = 1;
                    }
                }
            }
        }
        $GroupCountString = '';
        if(!empty($CounterDivisionGroup1)) {
            $GroupCountString .= 'Anzahl Gruppe 1: '.$CounterDivisionGroup1;
        }
        if(!empty($CounterDivisionGroup2)) {
            if(!empty($GroupCountString)) {
                $GroupCountString .= '<br/>Anzahl Gruppe 2: '.$CounterDivisionGroup2;
            } else {
                $GroupCountString .= 'Anzahl Gruppe 2: '.$CounterDivisionGroup2;
            }
        }
        $FrenchCountString = '';
        if(!empty($FrenchCounter)) {
            $FrenchCountString = 'Anzahl: '.$FrenchCounter;
        }
        $EducationCountString = '';
        if(!empty($EducationList)) {
            foreach($EducationList as $Education => $Count) {
                if(!empty($EducationCountString)) {
                    $EducationCountString .= '<br/>Anzahl '.$Education.': '.$Count;
                } else {
                    $EducationCountString .= 'Anzahl '.$Education.': '.$Count;
                }
            }
        }
        $OrientationCountString = '';
        if(!empty($OrientationList)) {
            foreach($OrientationList as $Orientation => $Count) {
                if(!empty($OrientationCountString)) {
                    $OrientationCountString .= '<br/>Anzahl '.$Orientation.': '.$Count;
                } else {
                    $OrientationCountString .= 'Anzahl '.$Orientation.': '.$Count;
                }
            }
        }
        $LayoutColumnCounterList = array();
        if(!empty($EducationCountString)) {
            $LayoutColumnCounterList[] = new LayoutColumn(new Panel('Bildungsgänge', $EducationCountString, Panel::PANEL_TYPE_INFO), 3);
        }
        if(!empty($OrientationCountString)) {
            $LayoutColumnCounterList[] = new LayoutColumn(new Panel('Wahlbereiche', $OrientationCountString, Panel::PANEL_TYPE_INFO), 3);
        }
        if(!empty($GroupCountString)) {
            $LayoutColumnCounterList[] = new LayoutColumn(new Panel('Gruppen', $GroupCountString, Panel::PANEL_TYPE_INFO), 3);
        }
        if(!empty($FrenchCountString)) {
            $LayoutColumnCounterList[] = new LayoutColumn(new Panel('Französisch', $FrenchCountString, Panel::PANEL_TYPE_INFO), 3);
        }

        $LayoutRowList = array();
        $LayoutRowCount = 0;
        $LayoutRow = null;
        /**
         * @var LayoutColumn $tblRelationship
         */
        foreach ($LayoutColumnCounterList as $Column) {
            if ($LayoutRowCount % 4 == 0) {
                $LayoutRow = new LayoutRow(array());
                $LayoutRowList[] = $LayoutRow;
            }
            $LayoutRow->addColumn($Column);
            $LayoutRowCount++;
        }

        $Stage->setContent(
            new Layout(array(
                PersonStandard::useFrontend()->getDivisionHeadOverview($tblDivisionCourse),
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        array(
                            'Number'               => '#',
                            'DisplayName'          => 'Name',
                            'Birthday'             => 'Geb.-Datum',
                            'Address'              => 'Adresse',
                            'PhoneNumbers'         => 'Telefonnummer',
                            'Group'                => 'Schüler&shy;gruppe',
                            'OrientationAndFrench' => 'WB/Profil/FR',
                            'Education'            => 'Bildungsgang',
                            'Elective'             => 'Wahlfach',
                        ),
                        array(
                            "pageLength" => -1,
                            "responsive" => false
                        )
                    )
                ))),
                PersonStandard::useFrontend()->getGenderLayoutGroup($tblPersonList),
                new LayoutGroup($LayoutRowList),
                new LayoutGroup(array(new LayoutRow(new LayoutColumn(
                    (Person::countMissingGenderByPersonList($tblPersonList) >= 1 ?
                        new Warning(new Child() . ' Die abweichende Anzahl der Geschlechter gegenüber der Gesamtanzahl
                        entsteht durch unvollständige Datenpflege. Bitte aktualisieren Sie die Angabe des Geschlechtes
                        in den Stammdaten der Personen.') :
                        null)
                ))))
            ))
        );
        return $Stage;
    }
}
