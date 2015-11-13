<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementCategory;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentAgreementType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentDisorderType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentFocusType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSubject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Aspect;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\NumberField;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Bus;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Child;
use SPHERE\Common\Frontend\Icon\Repository\Clock;
use SPHERE\Common\Frontend\Icon\Repository\Education;
use SPHERE\Common\Frontend\Icon\Repository\Heart;
use SPHERE\Common\Frontend\Icon\Repository\Hospital;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Key;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Medicine;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Shield;
use SPHERE\Common\Frontend\Icon\Repository\Stethoscope;
use SPHERE\Common\Frontend\Icon\Repository\StopSign;
use SPHERE\Common\Frontend\Icon\Repository\TempleChurch;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     * @param array $Meta
     *
     * @return Stage
     */
    public function frontendMeta(TblPerson $tblPerson = null, $Meta = array())
    {

        $Stage = new Stage();

        $Stage->setDescription(
            new Danger(
                new Info() . ' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

        $Stage->setContent((new Form(array(

            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Identifikation', array(
                            new TextField('Meta[Transfer][Student][Identifier]', 'Schülernummer',
                                'Schülernummer')
                        ), Panel::PANEL_TYPE_INFO)
                        , 4),
                ))
            ),
            $this->formGroupTransfer($tblPerson, $Meta),
            $this->formGroupGeneral($tblPerson, $Meta),
            $this->formGroupSubject($tblPerson, $Meta),
            $this->formGroupIntegration($tblPerson, $Meta),
        ),
            new Primary('Informationen speichern')
        )
        )->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.'));

        return $Stage;
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array $Meta
     *
     * @return FormGroup
     */
    private function formGroupTransfer(TblPerson $tblPerson = null, $Meta = array())
    {

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset($Global->POST['Meta'])) {
                /** @var TblStudent $tblStudent */
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    /** @var TblStudentTransfer $tblStudentTransferEnrollment */
                    $tblStudentTransferEnrollment = Student::useService()->getStudentTransferByType(
                        $tblStudent, Student::useService()->getStudentTransferTypeByIdentifier('Enrollment')
                    );
                    if ($tblStudentTransferEnrollment) {
                        $Global->POST['Meta']['Transfer']['Enrollment']['School'] = (
                        $tblStudentTransferEnrollment->getServiceTblCompany()
                            ? $tblStudentTransferEnrollment->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer']['Enrollment']['Date'] = $tblStudentTransferEnrollment->getTransferDate();
                        $Global->POST['Meta']['Transfer']['Enrollment']['Remark'] = $tblStudentTransferEnrollment->getRemark();
                    }
                    /** @var TblStudentTransfer $tblStudentTransferArrive */
                    $tblStudentTransferArrive = Student::useService()->getStudentTransferByType(
                        $tblStudent, Student::useService()->getStudentTransferTypeByIdentifier('Arrive')
                    );
                    if ($tblStudentTransferArrive) {
                        $Global->POST['Meta']['Transfer']['Arrive']['Type'] = (
                        $tblStudentTransferArrive->getServiceTblType()
                            ? $tblStudentTransferArrive->getServiceTblType()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer']['Arrive']['School'] = (
                        $tblStudentTransferArrive->getServiceTblCompany()
                            ? $tblStudentTransferArrive->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer']['Arrive']['Date'] = $tblStudentTransferArrive->getTransferDate();
                        $Global->POST['Meta']['Transfer']['Arrive']['Remark'] = $tblStudentTransferArrive->getRemark();
                    }
                    /** @var TblStudentTransfer $tblStudentTransferLeave */
                    $tblStudentTransferLeave = Student::useService()->getStudentTransferByType(
                        $tblStudent, Student::useService()->getStudentTransferTypeByIdentifier('Leave')
                    );
                    if ($tblStudentTransferLeave) {
                        $Global->POST['Meta']['Transfer']['Leave']['Type'] = (
                        $tblStudentTransferLeave->getServiceTblType()
                            ? $tblStudentTransferLeave->getServiceTblType()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer']['Leave']['School'] = (
                        $tblStudentTransferLeave->getServiceTblCompany()
                            ? $tblStudentTransferLeave->getServiceTblCompany()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['Transfer']['Leave']['Date'] = $tblStudentTransferLeave->getTransferDate();
                        $Global->POST['Meta']['Transfer']['Leave']['Remark'] = $tblStudentTransferLeave->getRemark();
                    }
                    /** @var TblStudentTransfer $tblStudentTransferProcess */
                    $tblStudentTransferProcess = Student::useService()->getStudentTransferByType(
                        $tblStudent, Student::useService()->getStudentTransferTypeByIdentifier('Process')
                    );
                    if ($tblStudentTransferProcess) {
                        $Global->POST['Meta']['Transfer']['Process']['Remark'] = $tblStudentTransferProcess->getRemark();
                    }
                    $Global->savePost();
                }
            }
        }

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        if ($tblCompanyAllSchool) {
            array_push($tblCompanyAllSchool, new TblCompany());
        } else {
            $tblCompanyAllSchool = array(new TblCompany());
        }

        $tblCompanyAllSchoolNursery = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('NURSERY')
        );
        if ($tblCompanyAllSchool) {
            $tblCompanyAllSchoolNursery = array_merge($tblCompanyAllSchool, $tblCompanyAllSchoolNursery);
        }

        $tblSchoolTypeAll = Type::useService()->getTypeAll();
        if ($tblSchoolTypeAll) {
            array_push($tblSchoolTypeAll, new TblType());
        } else {
            $tblSchoolTypeAll = array(new TblType());
        }

        $tblSchoolCourseAll = Course::useService()->getCourseAll();
        if ($tblSchoolCourseAll) {
            array_push($tblSchoolCourseAll, new TblCourse());
        } else {
            $tblSchoolCourseAll = array(new TblCourse());
        }

        $tblStudentTransferTypeEnrollment = Student::useService()->getStudentTransferTypeByIdentifier('Enrollment');
        $tblStudentTransferTypeArrive = Student::useService()->getStudentTransferTypeByIdentifier('Arrive');
        $tblStudentTransferTypeLeave = Student::useService()->getStudentTransferTypeByIdentifier('Leave');

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Ersteinschulung', array(
                        new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeEnrollment->getId() . '][School]',
                            'Schule', array(
                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
                            ), new Education()),
                        new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeEnrollment->getId() . '][Type]',
                            'Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                            ), new Education()),
                        new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeEnrollment->getId() . '][Course]',
                            'Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                            ), new Education()),
                        new DatePicker('Meta[Transfer][' . $tblStudentTransferTypeEnrollment->getId() . '][Date]',
                            'Datum', 'Datum', new Calendar()),
                        new TextArea('Meta[Transfer][' . $tblStudentTransferTypeEnrollment->getId() . '][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schüler - Aufnahme', array(
                        new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeArrive->getId() . '][School]',
                            'Abgebende Schule / Kita', array(
                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchoolNursery
                            ), new Education()),
                        new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeArrive->getId() . '][Type]',
                            'Letzte Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                            ), new Education()),
                        new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeArrive->getId() . '][Course]',
                            'Letzter Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                            ), new Education()),
                        new DatePicker('Meta[Transfer][' . $tblStudentTransferTypeArrive->getId() . '][Date]', 'Datum',
                            'Datum', new Calendar()),
                        new TextArea('Meta[Transfer][' . $tblStudentTransferTypeArrive->getId() . '][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schüler - Abgabe', array(
                        new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeLeave->getId() . '][School]',
                            'Aufnehmende Schule', array(
                                '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
                            ), new Education()),
                        new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeLeave->getId() . '][Type]',
                            'Letzte Schulart', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                            ), new Education()),
                        new SelectBox('Meta[Transfer][' . $tblStudentTransferTypeLeave->getId() . '][Course]',
                            'Letzter Bildungsgang', array(
                                '{{ Name }} {{ Description }}' => $tblSchoolCourseAll
                            ), new Education()),
                        new DatePicker('Meta[Transfer][' . $tblStudentTransferTypeLeave->getId() . '][Date]', 'Datum',
                            'Datum', new Calendar()),
                        new TextArea('Meta[Transfer][' . $tblStudentTransferTypeLeave->getId() . '][Remark]',
                            'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
            )),
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Schulverlauf', array(
                        new SelectBox('Meta[Transfer][Process][Type]', 'Aktuelle Schulart', array(
                            '{{ Name }} {{ Description }}' => $tblSchoolTypeAll,
                        ), new Education()),
                        new SelectBox('Meta[Transfer][Process][Type]', 'Aktueller Bildungsgang', array(
                            '{{ Name }} {{ Description }}' => $tblSchoolCourseAll,
                        ), new Education()),
                        new TextArea('Meta[Transfer][Process][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 6),
                new FormColumn(array(
                    // TODO:
                    new Panel('Besuchte Schulklassen', array(
                        new Bold('Aktuelle Klasse 10b'),
                        '2016/2017 Klasse 9b',
                        '2015/2016 Klasse 8a',
                    ), Panel::PANEL_TYPE_DEFAULT,
                        new Warning(
                            'Vom System erkannte Besuche. Wird bei Klassen&shy;zuordnung in Schuljahren erzeugt'
                        )
                    ),
                ), 3),
                new FormColumn(array(
                    // TODO:
                    new Panel('Aktuelle Schuljahrwiederholungen', array(
                        '2015/2016 Klassenstufe 8',
                        '2017/2018 Klassenstufe 10'
                    ), Panel::PANEL_TYPE_DEFAULT,
                        new Warning(
                            'Vom System erkannte Schuljahr&shy;wiederholungen.'
                            . 'Wird bei wiederholter Klassen&shy;zuordnung in verschiedenen Schuljahren erzeugt'
                        )
                    ),
                ), 3),
            )),
        ), new Title(new TileSmall() . ' Schülertransfer'));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array $Meta
     *
     * @return FormGroup
     */
    private function formGroupGeneral(TblPerson $tblPerson = null, $Meta = array())
    {

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset($Global->POST['Meta'])) {
                /** @var TblStudent $tblStudent */
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    /** @var TblStudentMedicalRecord $tblStudentMedicalRecord */
                    $tblStudentMedicalRecord = $tblStudent->getTblStudentMedicalRecord();
                    if ($tblStudentMedicalRecord) {
                        $Global->POST['Meta']['MedicalRecord']['Disease'] = $tblStudentMedicalRecord->getDisease();
                        $Global->POST['Meta']['MedicalRecord']['Medication'] = $tblStudentMedicalRecord->getMedication();
                        $Global->POST['Meta']['MedicalRecord']['AttendingDoctor'] = (
                        $tblStudentMedicalRecord->getServiceTblPersonAttendingDoctor()
                            ? $tblStudentMedicalRecord->getServiceTblPersonAttendingDoctor()->getId()
                            : 0
                        );
                        $Global->POST['Meta']['MedicalRecord']['InsuranceState'] = $tblStudentMedicalRecord->getInsuranceState();
                        $Global->POST['Meta']['MedicalRecord']['Insurance'] = $tblStudentMedicalRecord->getInsurance();
                    }
                    $Global->savePost();
                }
            }
        }

        /**
         * Panel: Agreement
         */
        $tblAgreementCategoryAll = Student::useService()->getStudentAgreementCategoryAll();
        $AgreementPanel = array();
        array_walk($tblAgreementCategoryAll,
            function (TblStudentAgreementCategory $tblStudentAgreementCategory) use (&$AgreementPanel) {

                array_push($AgreementPanel, new Aspect($tblStudentAgreementCategory->getName()));
                $tblAgreementTypeAll = Student::useService()->getStudentAgreementTypeAllByCategory($tblStudentAgreementCategory);
                array_walk($tblAgreementTypeAll,
                    function (TblStudentAgreementType $tblStudentAgreementType) use (
                        &$AgreementPanel,
                        $tblStudentAgreementCategory
                    ) {

                        array_push($AgreementPanel,
                            new CheckBox('Meta[' . $tblStudentAgreementCategory->getId() . '][' . $tblStudentAgreementType->getId() . ']',
                                $tblStudentAgreementType->getName(), 1)
                        );
                    }
                );
            }
        );
        $AgreementPanel = new Panel('Einverständniserklärung zur Datennutzung', $AgreementPanel,
            Panel::PANEL_TYPE_INFO);

        /**
         * Form
         */
        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel(new Hospital() . ' Krankenakte', array(
                        new TextArea('Meta[MedicalRecord][Disease]', 'Krankheiten / Allergien',
                            'Krankheiten / Allergien', new Heart()),
                        new TextArea('Meta[MedicalRecord][Medication]', 'Mediakamente', 'Mediakamente',
                            new Medicine()),
                        new SelectBox('Meta[MedicalRecord][AttendingDoctor]', 'Behandelnder Arzt', array(),
                            new Stethoscope()),
                        new SelectBox('Meta[MedicalRecord][InsuranceState]', 'Versicherungsstatus', array(
                            0 => '',
                            1 => 'Pflicht',
                            2 => 'Freiwillig',
                            3 => 'Privat',
                            4 => 'Familie Vater',
                            5 => 'Familie Mutter',
                        ), new Lock()),
                        new AutoCompleter('Meta[MedicalRecord][Insurance]', 'Krankenkasse', 'Krankenkasse',
                            array(), new Shield()),
                    ), Panel::PANEL_TYPE_DANGER), 3),
                new FormColumn(array(
                    new Panel('Fakturierung', array(
                        new SelectBox('Meta[MedicalRecord][InsuranceState]', 'Geschwisterkind', array(
                            0 => '',
                            1 => '1. Geschwisterkind',
                            2 => '2. Geschwisterkind',
                            3 => '3. Geschwisterkind',
                            4 => '4. Geschwisterkind',
                            5 => '5. Geschwisterkind',
                            6 => '6. Geschwisterkind',
                        ), new Child()),
                    ), Panel::PANEL_TYPE_INFO),
                    new Panel('Schließfach', array(
                        new TextField('Meta[Additional][Locker][Number]', 'Schließfachnummer', 'Schließfachnummer',
                            new Lock()),
                        new TextField('Meta[Additional][Locker][Location]', 'Schließfach Standort',
                            'Schließfach Standort', new MapMarker()),
                        new TextField('Meta[Additional][Locker][Key]', 'Schlüssel Nummer', 'Schlüssel Nummer',
                            new Key()),
                    ), Panel::PANEL_TYPE_INFO),
                    new Panel('Taufe', array(
                        new DatePicker('Meta[Additional][BaptismDate]', 'Taufdatum', 'Taufdatum',
                            new TempleChurch()
                        ),
                        new TextField('Meta[Additional][BaptismLocation]', 'Taufort', 'Taufort', new MapMarker()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 3),
                new FormColumn(
                    new Panel('Schulbeförderung', array(
                        new TextField('Meta[Transport][Route]', 'Buslinie', 'Buslinie', new Bus()),
                        new TextField('Meta[Transport][Station][Entrance]', 'Einstiegshaltestelle',
                            'Einstiegshaltestelle', new StopSign()),
                        new TextField('Meta[Transport][Station][Exit]', 'Ausstiegshaltestelle',
                            'Ausstiegshaltestelle', new StopSign()),
                        new TextArea('Meta[Transport][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO), 3),
                new FormColumn($AgreementPanel, 3),
            )),
        ), new Title(new TileSmall() . ' Allgemeines'));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array $Meta
     *
     * @return FormGroup
     */
    private function formGroupSubject(TblPerson $tblPerson = null, $Meta = array())
    {

        $tblStudent = Student::useService()->getStudentByPerson($tblPerson);

        if ($tblStudent && empty($Meta)) {
            $tblStudentSubjectAll = Student::useService()->getStudentSubjectAllByStudent($tblStudent);
            if ($tblStudentSubjectAll) {
                $Global = $this->getGlobal();
                array_walk($tblStudentSubjectAll, function (TblStudentSubject $tblStudentSubject) use (&$Global) {

                    $Type = $tblStudentSubject->getTblStudentSubjectType()->getId();
                    $Ranking = $tblStudentSubject->getTblStudentSubjectRanking()->getId();
                    $Subject = $tblStudentSubject->getServiceTblSubject()->getId();
                    $Global->POST['Meta']['Subject'][$Type][$Ranking] = $Subject;
                });
                $Global->savePost();
            }
        }

        // Orientation
        $tblSubjectOrientation = Subject::useService()->getSubjectOrientationAll();
        if ($tblSubjectOrientation) {
            array_push($tblSubjectOrientation, new TblSubject());
        } else {
            $tblSubjectOrientation = array(new TblSubject());
        }

        // Advanced
        $tblSubjectAdvanced = Subject::useService()->getSubjectAdvancedAll();
        if ($tblSubjectAdvanced) {
            array_push($tblSubjectAdvanced, new TblSubject());
        } else {
            $tblSubjectAdvanced = array(new TblSubject());
        }

        // Elective
        $tblSubjectElective = Subject::useService()->getSubjectElectiveAll();
        if ($tblSubjectElective) {
            array_push($tblSubjectElective, new TblSubject());
        } else {
            $tblSubjectElective = array(new TblSubject());
        }

        // Profile
        $tblSubjectProfile = Subject::useService()->getSubjectProfileAll();
        if ($tblSubjectProfile) {
            array_push($tblSubjectProfile, new TblSubject());
        } else {
            $tblSubjectProfile = array(new TblSubject());
        }

        // Religion
        $tblSubjectReligion = Subject::useService()->getSubjectReligionAll();
        if ($tblSubjectReligion) {
            array_push($tblSubjectReligion, new TblSubject());
        } else {
            $tblSubjectReligion = array(new TblSubject());
        }

        // ForeignLanguage
        $tblSubjectForeignLanguage = Subject::useService()->getSubjectForeignLanguageAll();
        if ($tblSubjectForeignLanguage) {
            array_push($tblSubjectForeignLanguage, new TblSubject());
        } else {
            $tblSubjectForeignLanguage = array(new TblSubject());
        }

        // All
        $tblSubjectAll = Subject::useService()->getSubjectAll();
        array_push($tblSubjectAll, new TblSubject());

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    $this->panelSubjectList('RELIGION', 'Religion', 'Religion', $tblSubjectReligion, 1),
                    $this->panelSubjectList('PROFILE', 'Profile', 'Profil', $tblSubjectProfile, 1),
                    $this->panelSubjectList('FOREIGN_LANGUAGE', 'Fremdsprachen', 'Fremdsprache',
                        $tblSubjectForeignLanguage, 4),
                ), 3),
                new FormColumn(array(
                    $this->panelSubjectList('ORIENTATION', 'Neigungskurse', 'Neigungskurs', $tblSubjectOrientation, 1),
                    $this->panelSubjectList('ELECTIVE', 'Wahlfächer', 'Wahlfach', $tblSubjectElective, 2),
                    $this->panelSubjectList('TEAM', 'Arbeitsgemeinschaften', 'Arbeitsgemeinschaft', $tblSubjectAll, 3),
                ), 3),
                new FormColumn(array(
                    $this->panelSubjectList('ADVANCED', 'Vertiefungskurse', 'Vertiefungskurs', $tblSubjectAdvanced, 1),
                    $this->panelSubjectList('TRACK_INTENSIVE', 'Leistungskurse', 'Leistungskurs', $tblSubjectAll, 2),
                ), 3),
                new FormColumn(array(
                    $this->panelSubjectList('TRACK_BASIC', 'Grundkurse', 'Grundkurs', $tblSubjectAll, 8),
                ), 3),
            )),
        ), new Title(new TileSmall() . ' Unterrichtsfächer'));
    }

    /**
     * @param string $Identifier
     * @param string $Title
     * @param string $Label
     * @param TblSubject[] $SubjectList
     * @param int $Count
     *
     * @return Panel
     */
    private function panelSubjectList($Identifier, $Title, $Label, $SubjectList, $Count = 1)
    {

        $tblStudentSubjectType = Student::useService()->getStudentSubjectTypeByIdentifier(strtoupper($Identifier));
        $Panel = array();
        for ($Rank = 1; $Rank <= $Count; $Rank++) {
            $tblStudentSubjectRanking = Student::useService()->getStudentSubjectRankingByIdentifier($Rank);
            array_push($Panel,
                new SelectBox(
                    'Meta[Subject][' . $tblStudentSubjectType->getId() . '][' . $tblStudentSubjectRanking->getId() . ']',
                    ($Count > 1 ? $tblStudentSubjectRanking->getName() . ' ' : '') . $Label,
                    array('{{ Acronym }} - {{ Name }} {{ Description }}' => $SubjectList),
                    new Education()
                )
            );
        }
        return new Panel($Title, $Panel, Panel::PANEL_TYPE_INFO);
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array $Meta
     *
     * @return FormGroup
     */
    private function formGroupIntegration(TblPerson $tblPerson = null, $Meta = array())
    {

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        array_push($tblCompanyAllSchool, new TblCompany());

        $PanelDisorder = array();
        $tblStudentDisorderType = Student::useService()->getStudentDisorderTypeAll();
        $tblStudentDisorderType = $this->getSorter($tblStudentDisorderType)->sortObjectList('Name');
        array_walk($tblStudentDisorderType,
            function (TblStudentDisorderType $tblStudentDisorderType) use (&$PanelDisorder) {

                array_push($PanelDisorder,
                    new CheckBox('Meta[Integration][Disorder][' . $tblStudentDisorderType->getId() . ']',
                        $tblStudentDisorderType->getName(), 1)
                );
            });
        $PanelDisorder = new Panel('Förderbedarf: Teilleistungsstörungen', $PanelDisorder, Panel::PANEL_TYPE_INFO);

        $PanelFocus = array();
        $tblStudentFocusType = Student::useService()->getStudentFocusTypeAll();
        $tblStudentFocusType = $this->getSorter($tblStudentFocusType)->sortObjectList('Name');
        array_walk($tblStudentFocusType,
            function (TblStudentFocusType $tblStudentFocusType) use (&$PanelFocus) {

                array_push($PanelFocus,
                    new CheckBox('Meta[Integration][Focus][' . $tblStudentFocusType->getId() . ']',
                        $tblStudentFocusType->getName(), 1)
                );
            });
        $PanelFocus = new Panel('Förderbedarf: Schwerpunkte', $PanelFocus, Panel::PANEL_TYPE_INFO);

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel('Förderantrag / Förderbescheid', array(
                        new CheckBox('Meta[Integration][CoachingRequired]', 'Förderbedarf', 1),
                        new DatePicker('Meta[Integration][CoachingCounselDate]', 'Förderantrag Beratung',
                            'Förderantrag Beratung',
                            new Calendar()
                        ),
                        new DatePicker('Meta[Integration][CoachingRequestDate]', 'Förderantrag',
                            'Förderantrag',
                            new Calendar()
                        ),
                        new DatePicker('Meta[Integration][CoachingDecisionDate]', 'Förderbescheid SBA',
                            'Förderbescheid SBA',
                            new Calendar()
                        )
                    ), Panel::PANEL_TYPE_INFO), 3),
                new FormColumn(
                    new Panel('Förderschule', array(
                        new SelectBox('Meta[Integration][3]', 'Förderschule',
                            array('{{ Name }} {{ Description }}' => $tblCompanyAllSchool),
                            new Education()),
                        new SelectBox('Meta[Integration][3]',
                            'Schulbegleitung ' . new Small(new Muted('Integrationsbeauftragter')), array(),
                            new Person()),
                        new NumberField('Meta[Integration][3]', 'Stundenbedarf pro Woche',
                            'Stundenbedarf pro Woche', new Clock()),
                        new TextArea('Meta[Integration][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),

                    ), Panel::PANEL_TYPE_INFO), 3),
                new FormColumn($PanelFocus, 3),
                new FormColumn($PanelDisorder, 3),
            )),
        ), new Title(new TileSmall() . ' Integration'));
    }
}
