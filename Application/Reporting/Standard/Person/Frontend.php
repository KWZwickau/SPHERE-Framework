<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use DateTime;
use SPHERE\Application\Api\Reporting\Standard\ApiMetaDataComparison;
use SPHERE\Application\Api\Reporting\Standard\ApiStandard;
use SPHERE\Application\Education\Certificate\Generator\Repository\Element\Ruler;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseMemberType;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourseType;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\Reporting\Standard\Person\Person as ReportingPerson;
use SPHERE\Application\Setting\Consumer\Consumer;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Filter;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Listing;
use SPHERE\Common\Frontend\Icon\Repository\Time;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
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
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
     * @param $Route
     * @param $All
     *
     * @return Layout
     */
    public function getChooseDivisionCourse($Route = '', $All = null)
    {

        if($All){
            $tblDivisionCourseReportingList = DivisionCourse::useService()->getDivisionCourseAll('', true);
        } else {
            $tblDivisionCourseReportingList = array();
            if(($tblYearList = Term::useService()->getYearByNow())){
                foreach($tblYearList as $tblYear){
                    if(($tblDivisionCourseList = DivisionCourse::useService()->getDivisionCourseListByYear($tblYear, true))){
                        $tblDivisionCourseReportingList = array_merge($tblDivisionCourseReportingList, $tblDivisionCourseList);
                    }
                }
            }
        }
        $TableContent = array();
        if ($tblDivisionCourseReportingList) {
            array_walk($tblDivisionCourseReportingList, function (TblDivisionCourse $tblDivisionCourse) use (&$TableContent, $Route) {

                $Item['Year'] = $tblDivisionCourse->getYearName();
                $Item['DivisionCourse'] = $tblDivisionCourse->getDisplayName();
                $Item['CourseType'] = $tblDivisionCourse->getTypeName();
                $Item['SchoolType'] = $tblDivisionCourse->getSchoolTypeListFromStudents(true);
                $Item['Option'] = new Standard('', $Route, new EyeOpen(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId()), 'Anzeigen');
                $Item['Count'] = $tblDivisionCourse->getCountStudents();
                array_push($TableContent, $Item);
            });
        }
        $Content = new Layout(new LayoutGroup(
            new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null,
                    array(
                        'Year' => 'Jahr',
                        'DivisionCourse' => 'Kursname',
                        'CourseType' => 'Typ',
                        'SchoolType' => 'Schulart',
                        'Count' => 'Schüler',
                        'Option' => '',
                    ), array(
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => array(1,3)),
                            array("orderable" => false, "targets"   => -1),
                            array('searchable' => false, 'targets' => array(-1, -2)),
                        ),
                        'order' => array(
                            array(0, 'desc'),
                            array(2, 'asc'),
                            array(1, 'asc')
                        )
                    ))
                , 12))
            , new Title(new Listing() . ' Übersicht')));
        return $Content;
    }

    /**
     * @param null|int $DivisionCourseId
     *
     * @return Stage|string
     */
    public function frontendClassList(?int $DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten');
        $Route = '/Reporting/Standard/Person/ClassList';
        if ($DivisionCourseId === null) {
            if($All){
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent($this->getChooseDivisionCourse($Route, $All));
        } else {
            $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
            if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
                return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
            }
            $tblPersonList = $tblDivisionCourse->getStudents();
            if(!$tblPersonList){
                return $Stage->setContent(new Warning('Keine Schüler hinterlegt.'));
            }
            $TableContent = array();
            if (($tblYear = $tblDivisionCourse->getServiceTblYear())) {
                $hasSchoolAttendanceYear = false;
                $TableContent = Person::useService()->createClassList($tblDivisionCourse, $tblPersonList, $tblYear, $hasSchoolAttendanceYear);
            }
            if (!empty($TableContent)) {
                $Stage->addButton(
                    new Primary('Herunterladen', '/Api/Reporting/Standard/Person/ClassList/Download', new Download(),
                        array('DivisionCourseId' => $DivisionCourseId))
                );
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }
            $HeadList = array(
                'Number'           => '#',
                'LastName'         => 'Name',
                'FirstName'        => 'Vorname',
                'Gender'           => 'Geschlecht',
                'Denomination'     => 'Konfession',
                'Birthday'         => 'Geburtsdatum',
                'Birthplace'       => 'Geburtsort',
                'Address'          => 'Adresse',
                'Phone'            => new ToolTip('Telefon '.new Info(),
                    'p=Privat; g=Geschäftlich; n=Notfall; f=Fax; Bev.=Bevollmächtigt; Vorm.=Vormund; NK=Notfallkontakt'),
                'Level'            => 'Stufe',
                'Division'         => 'Klasse',
                'DivisionTeacher'  => 'Klassenlehrer',
                'CoreGroup'        => 'Stammgruppe',
                'Tudor'            => 'Tutor',
                'ForeignLanguage1' => 'Fremdsprache 1',
                'ForeignLanguage2' => 'Fremdsprache 2',
                'ForeignLanguage3' => 'Fremdsprache 3',
                'Religion'         => 'Religion',
            );
            if($hasSchoolAttendanceYear){
                $HeadList['SBJ'] = 'SBJ';
            }
            $LevelList = array();
            foreach($tblPersonList as $tblPerson){
                if($tblYear = $tblDivisionCourse->getServiceTblYear()){
                    $tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear);
                    if($tblStudentEducation && ($tblSchoolType = $tblStudentEducation->getServiceTblSchoolType())){
                        $LevelList[$tblSchoolType->getName()][$tblStudentEducation->getLevel()] = $tblStudentEducation->getLevel();
                    }
                }
            }
            // Profil
            if(isset($LevelList[TblType::IDENT_GYMNASIUM])
                && (in_array('8', $LevelList[TblType::IDENT_GYMNASIUM])
                    || in_array('9', $LevelList[TblType::IDENT_GYMNASIUM])
                    || in_array('10', $LevelList[TblType::IDENT_GYMNASIUM])
                )
            ){
                $HeadList['Profile'] = 'Profil';
            }
            // Wahlbereich
            if(isset($LevelList[TblType::IDENT_OBER_SCHULE])
                && (in_array('7', $LevelList[TblType::IDENT_OBER_SCHULE])
                    || in_array('8', $LevelList[TblType::IDENT_OBER_SCHULE])
                    || in_array('9', $LevelList[TblType::IDENT_OBER_SCHULE])
                )
            ){
                $HeadList['Orientation'] = 'Wahlbereich';
            }
            // Wahlfach
            if(isset($LevelList[TblType::IDENT_OBER_SCHULE])
                && in_array('10', $LevelList[TblType::IDENT_OBER_SCHULE])
            ){
                $HeadList['Elective'] = 'Wahlfächer';
            }
            $Stage->setContent(
                new Layout(array(
                    $this->getDivisionHeadOverview($tblDivisionCourse),
                    new LayoutGroup(new LayoutRow(array(
                        new LayoutColumn(new TableData($TableContent, null,
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
                    $this->getGenderFooter($tblDivisionCourse)
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null|int $DivisionCourseId
     * @param null $All
     *
     * @return Stage
     */
    public function frontendExtendedClassList(?int $DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('Auswertung', 'erweiterte Klassenlisten');
        $Route = '/Reporting/Standard/Person/ExtendedClassList';
        if ($DivisionCourseId === null) {
            if($All){
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent($this->getChooseDivisionCourse($Route, $All));
        } else {
            $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
            if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
                return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
            }
            $IsGuardian3 = false;
            $IsAuthorized = false;
            $TableContent = Person::useService()->createExtendedClassList($tblDivisionCourse);
            if (!empty($TableContent)) {
                foreach($TableContent as $Row){
                    if($Row['Authorized']){
                        $IsAuthorized = true;
                    }
                    if($Row['Guardian3']){
                        $IsGuardian3 = true;
                    }
                }
                $Stage->addButton(
                    new Primary('Herunterladen',
                        '/Api/Reporting/Standard/Person/ExtendedClassList/Download', new Download(),
                        array('DivisionCourseId' => $tblDivisionCourse->getId()))
                );
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }
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
            );
            if($IsGuardian3){
                $tableHead['Guardian3'] = 'Sorgeberechtigter 3';
                $tableHead['PhoneGuardian3'] = 'Tel. Sorgeber. 3 '.
                    new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax');
            }
            if($IsAuthorized){
                $tableHead['Authorized'] = 'Bevollmächtigte(r)';
                $tableHead['PhoneAuthorized'] = 'Tel. Bevollm. '.
                    new ToolTip(new Info(), 'p=Privat; g=Geschäftlich; n=Notfall; f=Fax');
            }
            $tableHead['AuthorizedToCollect'] = 'Abholberechtigte';
            $Stage->setContent(
                new Layout(array(
                    $this->getDivisionHeadOverview($tblDivisionCourse),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new TableData($TableContent, null, $tableHead,
                            array(
                                "pageLength" => -1,
                                "responsive" => false,
                                'columnDefs' => array(
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                                ),
                            )
                        )
                    ))),
                    $this->getGenderFooter($tblDivisionCourse)
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null $DivisionCourseId
     * @param null $All
     *
     * @return Stage
     */
    public function frontendElectiveClassList($DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('Auswertung', 'Wahlfächer in Klassenlisten');
        $Route = '/Reporting/Standard/Person/ElectiveClassList';
        if ($DivisionCourseId === null) {
            if($All){
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent($this->getChooseDivisionCourse($Route, $All));
        } else {
            $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
            if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
                return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
            }
            $TableContent = Person::useService()->createElectiveClassList($tblDivisionCourse);
            if(!empty($TableContent)){
                $Stage->addButton(
                    new Primary('Herunterladen',
                        '/Api/Reporting/Standard/Person/ElectiveClassList/Download', new Download(),
                        array('DivisionCourseId' => $tblDivisionCourse->getId()))
                );
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }
            $Stage->setContent(
                new Layout(array(
                    $this->getDivisionHeadOverview($tblDivisionCourse),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new TableData($TableContent, null,
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
                    ))),
                    $this->getGenderFooter($tblDivisionCourse)
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null $All
     *
     * @return Stage
     */
    public function frontendBirthdayClassList(?int $DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten Geburtstag');
        $Route = '/Reporting/Standard/Person/BirthdayClassList';
        if ($DivisionCourseId === null) {
            if($All){
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent($this->getChooseDivisionCourse($Route, $All));
        } else {
            $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
            if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
                return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
            }
            $TableContent = Person::useService()->createBirthdayClassList($tblDivisionCourse);
            if (!empty($TableContent)) {
                $Stage->addButton(new Primary('Herunterladen', '/Api/Reporting/Standard/Person/BirthdayClassList/Download', new Download(),
                    array('DivisionCourseId' => $tblDivisionCourse->getId())));
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }
            $Stage->setContent(
                new Layout(array(
                    $this->getDivisionHeadOverview($tblDivisionCourse),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new TableData($TableContent, null,
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
                    ))),
                    $this->getGenderFooter($tblDivisionCourse)
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null $DivisionCourseId
     * @param null $All
     *
     * @return Stage
     */
    public function frontendMedicalInsuranceClassList($DivisionCourseId = null, $All = null)
    {

        $Stage = new Stage('Auswertung', 'Klassenlisten Krankenkasse');
        $Route = '/Reporting/Standard/Person/MedicalInsuranceClassList';
        if ($DivisionCourseId === null) {
            if($All){
                $Stage->addButton(new Standard('aktuelles Schuljahr', $Route));
                $Stage->addButton(new Standard(new InfoText(new Bold('Alle Schuljahre')), $Route, null, array('All' => 1)));
            } else {
                $Stage->addButton(new Standard(new InfoText(new Bold('aktuelles Schuljahr')), $Route));
                $Stage->addButton(new Standard('Alle Schuljahre', $Route, null, array('All' => 1)));
            }
            $Stage->setContent($this->getChooseDivisionCourse($Route, $All));
        } else {
            $Stage->addButton(new Standard('Zurück', $Route, new ChevronLeft()));
            if (!($tblDivisionCourse = DivisionCourse::useService()->getDivisionCourseById($DivisionCourseId))) {
                return $Stage->setContent(new Warning('Klasse nicht verfügbar.'));
            }
            $TableContent = Person::useService()->createMedicalInsuranceClassList($tblDivisionCourse);
            if(!empty($TableContent)){
                $Stage->addButton(
                    new Primary('Herunterladen',
                        '/Api/Reporting/Standard/Person/MedicalInsuranceClassList/Download', new Download(),
                        array('DivisionCourseId' => $tblDivisionCourse->getId()))
                );
                $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
            }
            $Stage->setContent(
                new Layout(array(
                    $this->getDivisionHeadOverview($tblDivisionCourse),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new TableData($TableContent, null,
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
                    ))),
                    $this->getGenderFooter($tblDivisionCourse)
                ))
            );
        }
        return $Stage;
    }

    /**
     * @return Stage
     */
    public function frontendInterestedPersonList()
    {

        $Stage = new Stage('Auswertung', 'Neuanmeldungen/Interessenten');
        $tblPersonList = false;
        if(($tblGroup = Group::useService()->getGroupByMetaTable('PROSPECT'))){
            $tblPersonList = Group::useService()->getPersonAllByGroup($tblGroup);
        }
        $hasGuardian = false;
        $hasAuthorizedPerson = false;
        $TableContent = Person::useService()->createInterestedPersonList($hasGuardian, $hasAuthorizedPerson);
        if (!empty($TableContent)) {
            $Stage->addButton(
                new Primary('Herunterladen',
                    '/Api/Reporting/Standard/Person/InterestedPersonList/Download', new Download())
            );
            $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        }
        $columns = array();
        $columns['FirstName'] = 'Vorname';
        $columns['LastName'] = 'Name';
        $columns['RegistrationDate'] = 'Anmeldedatum';
        $columns['InterviewDate'] = 'Aufnahmegespräch ';
        $columns['TrialDate'] = 'Schnuppertag ';
        $columns['SchoolYear'] = 'Schuljahr';
        $columns['Level'] = 'Klassenstufe';
        $columns['TypeOptionA'] = 'Schulart 1';
        $columns['TypeOptionB'] = 'Schulart 2';
        $columns['TransferCompany'] = 'Abgebende Schule / Kita';
        $columns['TransferStateCompany'] = 'Staatliche Stammschule';
        $columns['TransferType'] = 'Letzte Schulart';
        $columns['TransferCourse'] = 'Letzter Bildungsgang';
        $columns['TransferDate'] = 'Aufnahme Datum';
        $columns['TransferRemark'] = 'Aufnahme Bemerkung';
        $columns['Address'] = 'Adresse';
        $columns['Birthday'] = 'Geburtsdatum';
        $columns['Birthplace'] = 'Geburtsort';
        $columns['Nationality'] = 'Staatsangeh.';
        $columns['Denomination'] = 'Bekenntnis';
        $columns['Siblings'] = 'Geschwister';
        $columns['Custody1'] = 'Sorgeberechtigter 1';
        $columns['Custody2'] = 'Sorgeberechtigter 2';
        $columns['Custody3'] = 'Sorgeberechtigter 3';
        if ($hasGuardian) {
            $columns['Guardian'] = 'Vormund';
        }
        if ($hasAuthorizedPerson) {
            $columns['AuthorizedPerson'] = 'Bevollmächtigter';
        }
        $columns['Phone'] = 'Telefon Interessent';
        $columns['Mail'] = 'E-Mail Interessent';
        $columns['PhoneGuardianString'] = 'Telefon Sorgeberechtigte';
        $columns['MailGuardian'] = 'E-Mail Sorgeberechtigte';
        $columns['Remark'] = 'Bemerkung';
        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData($TableContent, null,
                        $columns,
                        array(
                            'order' => array(
                                array(4, 'asc'),
                                array(3, 'asc')
                            ),
                            "pageLength" => -1,
                            "responsive" => false,
                            'columnDefs' => array(
                                array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 0, 1, 21, 22, 23),
                            ),
                        )
                    )))),
                $this->getGenderLayoutGroup($tblPersonList)
            ))
        );
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
        $TableContent = array();
        if ($GroupId === null) {
            if (($tblGroupAll = Group::useService()->getGroupAll())) {
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
                    // Gruppe "Alle" ignorieren
                    if ($tblGroup->getMetaTable() != 'COMMON') {
                        array_push($TableContent, $Item);
                    }
                });
            }
            $Stage->setContent(
                new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
                    new TableData(
                        $TableContent, null,
                        array(
                            'Name'   => 'Name',
                            'Count'  => 'Personen',
                            'Option' => ''
                        ),
                        array(
                            'columnDefs' => array(
                                array('type' => 'natural', 'targets' => 1),
                                array("orderable" => false, "targets" => -1),
                            ),
                            'order'      => array(
                                array(0, 'asc'),
                            )
                        )
                    )
                ))))
            );
        } else {
            $tblGroup = Group::useService()->getGroupById($GroupId);
            $Stage->addButton(
                new Standard('Zurück', '/Reporting/Standard/Person/GroupList', new ChevronLeft())
            );
            $ColumnDefAdd = array();
            $columnHead = array();
            if ($tblGroup) {
                $columnHead['Number'] = 'lfd. Nr.';
                $columnHead['Salutation'] = 'Anrede';
                $columnHead['Title'] = 'Titel';
                $columnHead['FirstName'] = 'Vorname';
                $columnHead['LastName'] = 'Nachname';
                $columnHead['Address'] = 'Anschrift';
                $columnHead['PhoneNumber'] = 'Telefon Festnetz';
                $columnHead['MobilPhoneNumber'] = 'Telefon Mobil';
                $columnHead['Mail'] = 'E-mail';
                $columnHead['Birthday'] = 'Geburtstag';
                $columnHead['BirthPlace'] = 'Geburtsort';
                $columnHead['Gender'] = 'Geschlecht';
                $columnHead['Nationality'] = 'Staatsangehörigkeit';
                $columnHead['Religion'] = 'Konfession';
                $columnHead['Division'] = 'aktuelle Klasse/ Stammgruppe';
                $columnHead['ParticipationWillingness'] = 'Mitarbeitsbereitschaft';
                $columnHead['ParticipationActivities'] = 'Mitarbeitsbereitschaft - Tätigkeiten';
                $columnHead['RemarkFrontend'] = 'Bemerkungen';
                if ($tblGroup->getMetaTable() == 'PROSPECT') {
                    $columnHead['ReservationDate'] = 'Eingangsdatum';
                    $columnHead['InterviewDate'] = 'Aufnahmegespräch';
                    $columnHead['TrialDate'] = 'Schnuppertag';
                    $columnHead['ReservationYear'] = 'Voranmeldung Schuljahr';
                    $columnHead['ReservationDivision'] = 'Voranmeldung Stufe';
                    $columnHead['SchoolTypeA'] = 'Voranmeldung Schulart A';
                    $columnHead['SchoolTypeB'] = 'Voranmeldung Schulart B';
                    $ColumnDefAdd = array(
                        array('type' => 'de_date', 'targets' => 17),
                        array('type' => 'de_date', 'targets' => 18),
                        array('type' => 'de_date', 'targets' => 19),
                    );
                }
                if ($tblGroup->getMetaTable() == 'STUDENT') {
                    $columnHead['Identifier'] = 'Schülernummer';
                    $columnHead['School'] = 'Schule';
                    $columnHead['SchoolType'] = 'Schulart';
                    $columnHead['SchoolCourse'] = 'Bildungsgang';
                    $columnHead['Division'] = 'aktuelle Klasse/ Stammgruppe';
                    //Agreement Head
                    if(($tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll())){
                        foreach($tblAgreementCategoryAll as $tblAgreementCategory){
                            if(($tblAgreementTypeList = Student::useService()->getStudentAgreementTypeAllByCategory($tblAgreementCategory))){
                                foreach($tblAgreementTypeList as $tblAgreementType){
                                    $column['AgreementType'.$tblAgreementType->getId()] = $tblAgreementType->getName();
                                }
                            }
                        }
                    }
                }
                if ($tblGroup->getMetaTable() == 'CUSTODY') {
                    $columnHead['Occupation'] = 'Beruf';
                    $columnHead['Employment'] = 'Arbeitsstelle';
                    $columnHead['Remark'] = 'Bemerkung Sorgeberechtigter';
                }
                if ($tblGroup->getMetaTable() == 'TEACHER') {
                    $columnHead['TeacherAcronym'] = 'Lehrerkürzel';
                }
                if ($tblGroup->getMetaTable() == 'CLUB') {
                    $columnHead['ClubIdentifier'] = 'Mitgliedsnummer';
                    $columnHead['EntryDate'] = 'Eintrittsdatum';
                    $columnHead['ExitDate'] = 'Austrittsdatum';
                    $columnHead['ClubRemark'] = 'Bemerkung Vereinsmitglied';
                    $ColumnDefAdd = array(
                        array('type' => 'de_date', 'targets' => 18),
                        array('type' => 'de_date', 'targets' => 19),
                    );
                }
                // TableData standard sort definition
                $ColumnDef = array(
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 2),
                    array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 3),
                    array('type' => 'de_date', 'targets' => 8)
                );
                // merge definition
                if (!empty($ColumnDefAdd)) {
                    $ColumnDef = array_merge($ColumnDef, $ColumnDefAdd);
                }
                $TableContent = Person::useService()->createGroupList($tblGroup);
                if (!empty($TableContent)) {
                    $Stage->addButton(
                        new Primary('Herunterladen', '/Api/Reporting/Standard/Person/GroupList/Download', new Download(),
                            array('GroupId' => $tblGroup->getId()))
                    );
                    $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
                    ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
                }
            }
            $Stage->setContent(
                new Layout(array(
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        (new Well('Gruppe: '.new Bold($tblGroup->getName()).' '.new Small(new Muted($tblGroup->getDescription(true))).
                            ($tblGroup->getRemark() ? new Container($tblGroup->getRemark()) : '')))->setPadding('5px')->setMarginBottom('10px')
                    ))),
                    new LayoutGroup(new LayoutRow(new LayoutColumn(
                        new TableData($TableContent, null,
                            $columnHead,
                            array(
                                'order'      => array(
                                    array(4, 'asc'),
                                    array(3, 'asc')
                                ),
                                'columnDefs' => $ColumnDef,
                                'pageLength' => -1,
                                'responsive' => false
                            )
                        )
                    ))),
                    $this->getGenderLayoutGroup(Group::useService()->getPersonAllByGroup($tblGroup))
                ))
            );
        }
        return $Stage;
    }

    /**
     * @param null|array $Data
     *
     * @return Stage
     */
    public function frontendMetaDataComparison(?array $Data = null)
    {

        $Stage = new Stage('Auswertung', 'Stammdatenabfrage');
        $FilterForm = $this->getStudentFilterForm(true);
        $PersonService = Person::useService(); // necessary for MetaComparisonList
        $tblPersonList = $PersonService->getStudentFilterResult($Data);
        $TableContent = $PersonService->getStudentTableContent($tblPersonList, $Data);
        $MetaComparisonList = $PersonService->getMetaComparisonList();
        $TableHead = array();
        $TableHead['Level'] = 'Stufe';
        $TableHead['DivisionCourse'] = 'Klasse';
        $TableHead['CoreGroup'] = 'Stammgruppe';
        $TableHead['StudentNumber'] = 'Schülernummer';
        $TableHead['FirstName'] = 'Vorname';
        $TableHead['LastName'] = 'Nachname';
        $TableHead['Gender'] = 'Geschlecht';
        $TableHead['Birthday'] = 'Geburtsdatum';
        $TableHead['BirthPlace'] = 'Geburtsort';
        $TableHead['CourseType'] = 'Bildungsgang';
        $TableHead['School'] = 'Schule';
        $TableHead['SchoolType'] = 'Schulart';
        $TableHead['Nationality'] = 'Staatsangehörigkeit';
        $TableHead['Address'] = 'Adresse';
        $TableHead['Medication'] = 'Medikamente';
        $TableHead['InsuranceState'] = 'Versicherungsstatus';
        $TableHead['Insurance'] = 'Krankenkasse';
        $TableHead['Denomination'] = 'Konfession';
        $TableHead['PhoneFixedPrivate'] = 'Festnetz (Privat)';
        $TableHead['PhoneFixedWork'] = 'Festnetz (Geschäftl.)';
        $TableHead['PhoneFixedEmergency'] = 'Festnetz (Notfall)';
        $TableHead['PhoneMobilePrivate'] = 'Mobil (Privat)';
        $TableHead['PhoneMobileWork'] = 'Mobil (Geschäftl.)';
        $TableHead['PhoneMobileEmergency'] = 'Mobil (Notfall)';
        $TableHead['MailPrivate'] = 'E-Mail Privat';
        $TableHead['MailWork'] = 'E-Mail Geschäftlich';
        $TableHead['Sibling_1'] = 'Geschwister1';
        $TableHead['Sibling_2'] = 'Geschwister2';
        $TableHead['Sibling_3'] = 'Geschwister3';

        $ColumnDef = array(
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 4),
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 5),
            // Sibling
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 25),
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 26),
            array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => 27),
        );

        $SortCount = 0;
        if(!empty($MetaComparisonList)){
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
                        $ColumnDef[] = array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (31 + (16 * $SortCount)));
                        $ColumnDef[] = array('type' => Consumer::useService()->getGermanSortBySetting(), 'targets' => (32 + (16 * $SortCount)));
                        $SortCount++;
                    }
                }
            }
        }

        $Table = new TableData($TableContent, null, $TableHead,
            array(
                'order'      => array(array(1, 'asc'), array(5, 'asc')),
                'columnDefs' => $ColumnDef,
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
                                        array('Data' => $Data))
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
    private function getStudentFilterForm($IsSibling = false)
    {

        $YearId = false;
        if(!isset($_POST['Data']['YearId'])
        && ($tblYearList = Term::useService()->getYearByNow())){
            $YearId = current($tblYearList)->getId();
            $_POST['Data']['YearId'] = $YearId;
        } elseif(isset($_POST['Data']['YearId'])){
            $YearId = $_POST['Data']['YearId'];
        }
        $LevelList = DivisionCourse::useService()->getStudentEducationLevelListForSelectbox();
        $FirstLoadContent = $this->getDivisionCourseSelectBox($YearId);
        $DivisionCourseSelectBox = ApiMetaDataComparison::receiverBlock($FirstLoadContent, 'reloadCourseSelectbox');
        $FilterColumnList[] = new FormColumn(
            new Panel('Bildung', array(
                (new SelectBox('Data[YearId]', 'Schuljahr', array('{{ Name }} {{ Description }}' => Term::useService()->getYearAllSinceYears(1))))
                    ->ajaxPipelineOnChange(ApiMetaDataComparison::pipelineReloadCourseSelectbox())->setRequired(),
                new SelectBox('Data[TypeId]', 'Schulart', array('{{ Name }}' => Type::useService()->getTypeAll()))), Panel::PANEL_TYPE_INFO)
            , 4);
        $FilterColumnList[] = new FormColumn(
            new Panel('Klasse/Stammgruppe', array(
                new SelectBox('Data[Level]', 'Stufe', $LevelList, null, false), $DivisionCourseSelectBox), Panel::PANEL_TYPE_INFO)
            , 4);
        if($IsSibling){
            $FilterColumnList[] = new FormColumn(
                new Panel('Geschwister', array(new CheckBox('Data[Sibling]', 'ehemalige Geschwister mit anzeigen', '1')), Panel::PANEL_TYPE_INFO)
                , 4);
        }
        return new Form(new FormGroup(array(
            new FormRow($FilterColumnList),
            new FormRow(new FormColumn(
                new \SPHERE\Common\Frontend\Form\Repository\Button\Primary('Filtern')
            ))
        )));
    }

    /**
     * @param string $YearId
     *
     * @return string
     *
     */
    public function getDivisionCourseSelectBox($YearId)
    {

        if(!($tblYear = Term::useService()->getYearById($YearId))){
            return new Warning('Schuljahr nicht gefunden', null, false, 15, 11);
        }
//        if(!$tblYear){
//            // fallback if no Id exists
//            $tblYearList = Term::useService()->getYearByNow();
//            $tblYear = current($tblYearList);
//        }
        $tblDivisionCourseList = array();
        if(($tempList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_DIVISION))) {
            $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tempList);
        }
        if(($tempList = DivisionCourse::useService()->getDivisionCourseListBy($tblYear, TblDivisionCourseType::TYPE_CORE_GROUP))) {
            $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tempList);
        }
        return new SelectBox('Data[DivisionCourseId]', 'Klasse/Stammgruppe SJ'.$tblYear->getYear()
            , array('{{ Name }} {{ Description }}' => $tblDivisionCourseList));
    }

    /**
     * @param null $Data
     *
     * @return Stage
     */
    public function frontendAbsence($Data = null): Stage
    {
        $stage = new Stage('Auswertung', 'Fehlzeiten');

        $stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));

        if ($Data == null) {
            $global = $this->getGlobal();
            $global->POST['Data']['Date'] = (new DateTime('now'))->format('d.m.Y');
            $global->savePost();
        }

        $receiverContent = ApiStandard::receiverBlock((new ApiStandard())->reloadAbsenceContent(), 'AbsenceContent');

        $certificateRelevantList = array(0 => '', 1 => 'ja', 2 => 'nein');
        $tblDivisionCourseList = array();
        if(($tblDivisionCourseListDivision = DivisionCourse::useService()->getDivisionCourseAll(TblDivisionCourseType::TYPE_DIVISION, true))) {
            $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListDivision);
        }
        if(($tblDivisionCourseListCoreGroup = DivisionCourse::useService()->getDivisionCourseAll(TblDivisionCourseType::TYPE_CORE_GROUP, true))){
            $tblDivisionCourseList = array_merge($tblDivisionCourseList, $tblDivisionCourseListCoreGroup);
        }

        $button = (new Primary('Filtern', '', new Filter()))->ajaxPipelineOnClick(ApiStandard::pipelineReloadAbsenceContent());
        $stage->setContent(
            new Form(new FormGroup(new FormRow(array(new FormColumn(new Panel(
                'Filter',
                new Layout (new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new DatePicker('Data[Date]', '', 'Datum von', new Calendar()), 4
                        ),
                        new LayoutColumn(
                            new DatePicker('Data[ToDate]', '', 'Datum bis', new Calendar()), 4
                        ),
                        new LayoutColumn(
                            new SelectBox('Data[IsCertificateRelevant]', 'Fehlzeit zeugnisrelevant', $certificateRelevantList), 4
                        ),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new SelectBox('Data[Type]', 'Schulart', array('Name' => Type::useService()->getTypeAll())), 4
                        ),
                        new LayoutColumn(
                            new AutoCompleter('Data[DivisionName]', 'Klasse/Stammgruppe', '', array('Name' => $tblDivisionCourseList)), 4
                        ),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            new CheckBox('Data[IsAbsenceOnline]', 'Nur unbearbeitete Online Fehlzeiten von Eltern/Schülern anzeigen', 1)
                            .'<div style="padding-bottom: 8px;"></div>'
                        ),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            $button
                        ),
                    ))
                ))),
                Panel::PANEL_TYPE_INFO
            ))))))
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
        $TableContent = Person::useService()->createClubList();
        if (!empty($TableContent)) {
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
                            new TableData($TableContent, null,
                                array(
                                    'Number'                => 'Mitgliedsnummer',
                                    'Title'                 => 'Titel',
                                    'LastName'              => 'Sorgeberechtigt Name',
                                    'FirstName'             => 'Sorgeberechtigt Vorname',
                                    'StudentLastName'       => 'Schüler / Interessent Name',
                                    'StudentFirstName'      => 'Schüler / Interessent Vorname',
                                    'Type'                  => 'Typ',
                                    'Year'                  => 'Schuljahr',
                                    'DivisionCourse'        => 'Klasse/Stammgruppe',
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

    /**
     * @return Stage
     */
    public function frontendClassTeacher(): Stage
    {

        $Stage = new Stage('Auswertung', 'Klassenlehrer');
        $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports
            ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        $Stage->addButton(
            new Primary('Herunterladen', '/Api/Reporting/Standard/Person/DivisionTeacherList/Download', new Download())
        );
        list($TableContent, $headers) = Person::useService()->createDivisionTeacherList();
        $Stage->setContent(new Layout(new LayoutGroup(
            new LayoutRow(new LayoutColumn(
                new TableData($TableContent, null, $headers,
                    array(
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 0),
                        ),
                        'order' => array(
                            array(0, 'asc')
                        ),
                        'responsive' => false
                    )
                )
            , 12)), new Title(new Listing().' Übersicht')
        )));
        return $Stage;
    }

    /**
     * @param TblYear $tblYear
     *
     * @return string
     */
    public function getStudentArchiveContent(TblYear $tblYear): string
    {

        if (!empty(($personList = DivisionCourse::useService()->getLeaveStudents($tblYear)))) {
            $dataList = Person::useService()->createStudentArchiveList($personList);
            return
                (new Primary('Herunterladen','/Api/Reporting/Standard/Person/StudentArchive/Download',
                    new Download(), array('YearId' => $tblYear->getId())))
                .(new Danger('Die dauerhafte Speicherung des Excel-Exports
                        ist datenschutzrechtlich nicht zulässig!', new Exclamation()))
                .(new TableData($dataList, null,
                    array(
                        'LastDivisionCourse' => 'Abgangsklasse',
                        'StudentNumber'      => 'Schüler Nr.',
                        'LastName'           => 'Name',
                        'FirstName'          => 'Vorname',
                        'Gender'             => 'Geschlecht',
                        'Birthday'           => 'Geburtsdatum',
                        'Custody1Salutation' => 'Anrede Sorg1',
                        'Custody1FirstName'  => 'Vorname Sorg1',
                        'Custody1LastName'   => 'Nachname Sorg1',
                        'Custody2Salutation' => 'Anrede Sorg2',
                        'Custody2FirstName'  => 'Vorname Sorg2',
                        'Custody2LastName'   => 'Nachname Sorg2',
                        'Street'             => 'Straße',
                        'ZipCode'            => 'PLZ',
                        'City'               => 'Ort',
                        'LastSchool'         => 'Abgebende Schule',
                        'NewSchool'          => 'Aufnehmende Schule',
                        'LeaveDate'          => 'Abmeldedatum'
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
                        ),
                    )
                ));
        }
        return new Warning('Für das Schuljahr: ' . $tblYear->getDisplayName(). ' wurden keine Abgänger gefunden.', new Exclamation());
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return LayoutGroup
     */
    public function getDivisionHeadOverview(TblDivisionCourse $tblDivisionCourse)
    {

        return new LayoutGroup(
            new LayoutRow(array(
                new LayoutColumn(
                    (new Well($this->getDivisionCourseLayout($tblDivisionCourse)))->setPadding('5px')->setMarginBottom('10px')
                    , 6),
                ($inActivePanel = $this->getInActiveStudentPanel($tblDivisionCourse))
                    ? new LayoutColumn($inActivePanel, 6)
                    : null
            )),
        );
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return bool|Panel
     */ // ToDO weiter verfolgen
    public function getInActiveStudentPanel(TblDivisionCourse $tblDivisionCourse, bool $hasAbsenceButton = false, string $BasicRoute = '', string $ReturnRoute = '')
    {
        $inActiveStudentList = array();
        if (($tblDivisionCourseMemberList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_STUDENT, true, false))) {
            $now = new DateTime('now');
            foreach ($tblDivisionCourseMemberList as $tblDivisionCourseMember) {
                if ($tblDivisionCourseMember->getLeaveDateTime() !== null && $now > $tblDivisionCourseMember->getLeaveDateTime()
                    && ($tblPerson = $tblDivisionCourseMember->getServiceTblPerson())
                    && ($tblYear = $tblDivisionCourse->getServiceTblYear())
                ) {
                    $text = $tblPerson->getLastFirstName().' (Deaktivierung: ' . $tblDivisionCourseMember->getLeaveDateTime()->format('d.m.Y').')';

                    if ($hasAbsenceButton) {
                        $currentMainDivisionCourses = DivisionCourse::useService()->getCurrentMainCoursesByPersonAndYear($tblPerson, $tblYear);

                        $inActiveStudentList[] = new Layout(new LayoutGroup(new LayoutRow(array(
                            new LayoutColumn($text, 6),
                            new LayoutColumn(
                                $currentMainDivisionCourses
                                    ?
                                    : new PullRight((new Standard(
                                        '', '/Education/ClassRegister/Digital/AbsenceStudent', new Time(),
                                        array(
                                            'DivisionCourseId' => $tblDivisionCourse->getId(),
                                            'PersonId'   => $tblPerson->getId(),
                                            'BasicRoute' => $BasicRoute,
                                            'ReturnRoute'=> $ReturnRoute
                                        ),
                                        'Fehlzeiten des Schülers verwalten'
                                    )))
                                , 6)
                        ))));
                    } else {
                        $inActiveStudentList[] = $text;
                    }
                }
            }
        }
        return empty($inActiveStudentList) ? false : new Panel('Ehemaliger Schüler dieser Klasse', $inActiveStudentList, Panel::PANEL_TYPE_WARNING);
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     * @return Layout
     */
    private function getDivisionCourseLayout(TblDivisionCourse $tblDivisionCourse)
    {

        $RowList[] = new LayoutRow(array(
            new LayoutColumn(new Bold($tblDivisionCourse->getTypeName().':'), 3),
            new LayoutColumn($tblDivisionCourse->getDisplayName(), 5),
            new LayoutColumn(new PullRight(new Bold($tblDivisionCourse->getYearName())), 3),
            new LayoutColumn(new Ruler())
        ));
        if(($tblPersonTeacherList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_DIVISION_TEACHER, false, false))){
            $TeacherArray = array();
            foreach($tblPersonTeacherList as $tblPersonTeacher){
                if($tblPerson = $tblPersonTeacher->getServiceTblPerson()){
                    $Description = $tblPersonTeacher->getDescription();
                    $TeacherArray[] = $tblPerson->getFullName() . ($Description ? ' ' . new Muted($Description) : '');
                }
            }
            if(!empty($TeacherArray)){
                $RowList[] = new LayoutRow(array(
                    new LayoutColumn(new Bold($tblDivisionCourse->getDivisionTeacherName().':'), 3),
                    new LayoutColumn(implode(', ', $TeacherArray), 9),
                ));
            }
        }
        if(($tblPersonRepresentativeList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_REPRESENTATIVE, false, false))){
            $RepresentationArray = array();
            foreach($tblPersonRepresentativeList as $tblPersonRepresentative){
                if($tblPerson = $tblPersonRepresentative->getServiceTblPerson()){
                    $Description = $tblPersonRepresentative->getDescription();
                    $RepresentationArray[] = $tblPerson->getFirstSecondName().' '.$tblPerson->getLastName().($Description ? ' ' . new Muted($Description) : '');
                }
            }
            if(!empty($RepresentationArray)){
                $RowList[] = new LayoutRow(array(
                    new LayoutColumn(new Bold('Klassensprecher:'), 3),
                    new LayoutColumn(implode(', ', $RepresentationArray), 9),
                ));
            }
        }

        if(($tblPersonCustodyList = DivisionCourse::useService()->getDivisionCourseMemberListBy($tblDivisionCourse, TblDivisionCourseMemberType::TYPE_CUSTODY, false, false))){
            $CustodyArray = array();
            foreach($tblPersonCustodyList as $tblPersonCustody){
                if($tblPerson = $tblPersonCustody->getServiceTblPerson()){
                    $Description = $tblPersonCustody->getDescription();
                    $CustodyArray[] = $tblPerson->getFullName() . ($Description ? ' ' . new Muted($Description) : '');
                }
            }
            if(!empty($CustodyArray)){
                $RowList[] = new LayoutRow(array(
                    new LayoutColumn(new Bold('Elternsprecher:'), 3),
                    new LayoutColumn(implode(', ', $CustodyArray), 9),
                ));
            }
        }

        return new Layout(new LayoutGroup($RowList));
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     *
     * @return LayoutGroup
     */
    public function getGenderFooter(TblDivisionCourse $tblDivisionCourse): LayoutGroup
    {

        return $this->getGenderLayoutGroup($tblDivisionCourse->getStudents());
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
    public function frontendStudentAgreement($Data = array())
    {

        $Stage = new Stage('Auswertung - Schüler', 'Einverständniserklärung');
        $TableContent = array();;
        if(!empty(($tblPersonList = Person::useService()->getStudentFilterResult($Data)))){
            $TableContent = Person::useService()->createAgreementList($tblPersonList);
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
        $TableData = new TableData($TableContent, null, $ColumnHead, array(
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
            $TableData->prependHead(new TableHead(new TableRow(
                $headTableColumnList
            )));
        }
        $FilterForm = $this->getStudentFilterForm();
        $Stage->setContent(
            new Layout(
                new LayoutGroup(array(
                    new LayoutRow(new LayoutColumn(new Well(
                        $FilterForm
                    ))),
                    new LayoutRow(new LayoutColumn(
                        (empty($tblPersonList)
                        ? new Warning('Bitte führen Sie die gewünschte Filterung aus')
                        : (false === $tblPersonList
                            ? new Danger('Filterung enthält keine Personen')
                            : new Primary('Download Einverständniserklärung', '/Api/Reporting/Standard/Person/AgreementStudentList/Download', new Download(),
                                array('Data' => $Data)))
                        )
                    )),
                    new LayoutRow(new LayoutColumn(
                        ($TableContent && !empty($TableContent)
                        ? $TableData
                        : '')
                    ))
                ))
            )
        );
        return $Stage;
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

    /**
     * @return Stage
     */
    public function frontendRepresentative(): Stage
    {

        $Stage = new Stage('Auswertung', 'Elternsprecher / Klassensprecher');
        $Stage->setMessage(new Danger('Die dauerhafte Speicherung des Excel-Exports ist datenschutzrechtlich nicht zulässig!', new Exclamation()));
        $Stage->addButton(
            new Primary('Herunterladen', '/Api/Reporting/Standard/Person/RepresentativeList/Download', new Download())
        );

        list($dataList, $headers) = ReportingPerson::useService()->createRepresentativeList(false);
        $Stage->setContent(new Layout(new LayoutGroup(
            new LayoutRow(new LayoutColumn(
                new TableData($dataList, null, $headers,
                    array(
                        'columnDefs' => array(
                            array('type' => 'natural', 'targets' => 0),
                        ),
                        'order' => array(
                            array(0, 'asc'),
                            array(2, 'asc')
                        ),
                        'responsive' => false
                    )
                )
                , 12)), new Title(new Listing().' Übersicht')
        )));

        return $Stage;
    }
}
