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
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferArrive;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferEnrollment;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferLeave;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferProcess;
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
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
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
     * @param array     $Meta
     *
     * @return Stage
     */
    public function frontendMeta(TblPerson $tblPerson = null, $Meta = array())
    {

        $Stage = new Stage();

        $Stage->setMessage(
            new Warning(
                new Danger(
                    new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Speichern der Schülerakte in aktueller Demo-Version noch nicht möglich'
                )
            )
            .new Danger(
                new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

        $Stage->setContent((new Form(array(
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
     * @param array          $Meta
     *
     * @return FormGroup
     */
    private function formGroupTransfer(TblPerson $tblPerson = null, $Meta = array())
    {

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Meta'] )) {
                /** @var TblStudent $tblStudent */
                $tblStudent = Student::useService()->getStudentByPerson($tblPerson);
                if ($tblStudent) {
                    /** @var TblStudentTransfer $tblStudentTransfer */
                    $tblStudentTransfer = $tblStudent->getTblStudentTransfer();
                    if ($tblStudentTransfer) {
                        /** @var TblStudentTransferEnrollment $tblStudentTransferEnrollment */
                        $tblStudentTransferEnrollment = $tblStudentTransfer->getTblStudentTransferEnrollment();
                        if ($tblStudentTransferEnrollment) {
                            $Global->POST['Meta']['Transfer']['Enrollment']['School'] = (
                            $tblStudentTransferEnrollment->getServiceTblCompany()
                                ? $tblStudentTransferEnrollment->getServiceTblCompany()->getId()
                                : 0
                            );
                            $Global->POST['Meta']['Transfer']['Enrollment']['Date'] = $tblStudentTransferEnrollment->getEnrollmentDate();
                            $Global->POST['Meta']['Transfer']['Enrollment']['Remark'] = $tblStudentTransferEnrollment->getRemark();
                        }
                        /** @var TblStudentTransferArrive $tblStudentTransferArrive */
                        $tblStudentTransferArrive = $tblStudentTransfer->getTblStudentTransferArrive();
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
                            $Global->POST['Meta']['Transfer']['Arrive']['Date'] = $tblStudentTransferArrive->getArriveDate();
                            $Global->POST['Meta']['Transfer']['Arrive']['Remark'] = $tblStudentTransferArrive->getRemark();
                        }
                        /** @var TblStudentTransferLeave $tblStudentTransferLeave */
                        $tblStudentTransferLeave = $tblStudentTransfer->getTblStudentTransferLeave();
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
                            $Global->POST['Meta']['Transfer']['Leave']['Date'] = $tblStudentTransferLeave->getLeaveDate();
                            $Global->POST['Meta']['Transfer']['Leave']['Remark'] = $tblStudentTransferLeave->getRemark();
                        }
                        /** @var TblStudentTransferProcess $tblStudentTransferProcess */
                        $tblStudentTransferProcess = $tblStudentTransfer->getTblStudentTransferProcess();
                        if ($tblStudentTransferProcess) {
                            $Global->POST['Meta']['Transfer']['Process']['Remark'] = $tblStudentTransferProcess->getRemark();
                        }
                        $Global->savePost();
                    }
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

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Ersteinschulung', array(
                        new SelectBox('Meta[Transfer][Enrollment][School]', 'Schule', array(
                            '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
                        ), new Education()),
                        new DatePicker('Meta[Transfer][Enrollment][Date]', 'Datum', 'Datum', new Calendar()),
                        new TextArea('Meta[Transfer][Enrollment][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schüler - Aufnahme', array(
                        new TextField('Meta[Transfer][Arrive][Identifier]', 'Schülernummer',
                            new Danger(new Info().' Schülernummer')),
                        new SelectBox('Meta[Transfer][Arrive][Type]', 'Letzte Schulart', array(
                            '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                        ), new Education()),
                        new SelectBox('Meta[Transfer][Arrive][School]', 'Abgebende Schule', array(
                            '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
                        ), new Education()),
                        new DatePicker('Meta[Transfer][Arrive][Date]', 'Datum', 'Datum', new Calendar()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schüler - Abgabe', array(
                        new SelectBox('Meta[Transfer][Leave][Type]', 'Letzte Schulart', array(
                            '{{ Name }} {{ Description }}' => $tblSchoolTypeAll
                        ), new Education()),
                        new SelectBox('Meta[Transfer][Leave][School]', 'Aufnehmende Schule', array(
                            '{{ Name }} {{ Description }}' => $tblCompanyAllSchool
                        ), new Education()),
                        new DatePicker('Meta[Transfer][Leave][Date]', 'Datum', 'Datum', new Calendar()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
            )),
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Schulverlauf', array(
                        new SelectBox('Meta[Transfer][Process][Type]', 'Aktuelle Schulart', array(
                            '{{ Name }} {{ Description }}' => $tblCompanyAllSchool,
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
                        new Listing(array(
                            new Bold('Aktuelle Klasse 10b'),
                            '2016/2017 Klasse 9b',
                            '2015/2016 Klasse 8a',
                        ))
                        .new Warning(
                            'Vom System erkannte Besuche.<br/>Wird bei Klassen&shy;zuordnung in Schuljahren erzeugt'
                        ),
                    ), Panel::PANEL_TYPE_DEFAULT),
                ), 3),
                new FormColumn(array(
                    // TODO:
                    new Panel('Aktuelle Schuljahrwiederholungen', array(
                        new Listing(array(
                            '2015/2016 Klassenstufe 8',
                            '2017/2018 Klassenstufe 10'
                        ))
                        .new Warning(
                            'Vom System erkannte Schuljahr&shy;wiederholungen.<br/>Wird bei wiederholter Klassen&shy;zuordnung in verschiedenen Schuljahren erzeugt'
                        ),
                    ), Panel::PANEL_TYPE_DEFAULT),
                ), 3),
            )),
        ), new Title('Schülertransfer',
            new Warning(
                new Danger(
                    new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Es können im Moment nur fest vorgegebene Schularten/Bildungsgänge in der aktuellen Demo-Version verwendet werden'
                )
            )
        ));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array          $Meta
     *
     * @return FormGroup
     */
    private function formGroupGeneral(TblPerson $tblPerson = null, $Meta = array())
    {

        if (null !== $tblPerson) {
            $Global = $this->getGlobal();
            if (!isset( $Global->POST['Meta'] )) {
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

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel(new Hospital().' Krankenakte', array(
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
                new FormColumn(array(
                    new Panel('Einverständniserklärung zur Datennutzung', array(
                        new Aspect('Fotos des Schülers'),
                        new CheckBox('Meta[PicturePermission][Internal]', 'in Schulschriften', 1),
                        new CheckBox('Meta[PicturePermission][External]', 'in Veröffentlichungen', 1),
                        new CheckBox('Meta[PicturePermission][Internet]', 'auf Internetpräsenz', 1),
                        new CheckBox('Meta[PicturePermission][Facebook]', 'auf Facebookseite', 1),
                        new CheckBox('Meta[PicturePermission][Press]', 'für Druckpresse', 1),
                        new CheckBox('Meta[PicturePermission][Multimedia]', 'durch Ton/Video/Film', 1),
                        new CheckBox('Meta[PicturePermission][Promotion]', 'für Werbung in eigener Sache', 1),
                    ), Panel::PANEL_TYPE_INFO),
                ), 3),
            )),
        ), new Title('Allgemeines'));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array          $Meta
     *
     * @return FormGroup
     */
    private function formGroupSubject(TblPerson $tblPerson = null, $Meta = array())
    {

        // Orientation
        $tblSubjectOrientation = Subject::useService()->getSubjectOrientationAll();
        if ($tblSubjectOrientation) {
            array_push($tblSubjectOrientation, new TblSubject());
        } else {
            $tblSubjectOrientation = array();
        }

        // Advanced
        $tblSubjectAdvanced = Subject::useService()->getSubjectAdvancedAll();
        if ($tblSubjectAdvanced) {
            array_push($tblSubjectAdvanced, new TblSubject());
        } else {
            $tblSubjectAdvanced = array();
        }

        // Elective
        $tblSubjectElective = Subject::useService()->getSubjectElectiveAll();
        if ($tblSubjectElective) {
            array_push($tblSubjectElective, new TblSubject());
        } else {
            $tblSubjectElective = array();
        }

        // Profile
        $tblSubjectProfile = Subject::useService()->getSubjectProfileAll();
        if ($tblSubjectProfile) {
            array_push($tblSubjectProfile, new TblSubject());
        } else {
            $tblSubjectProfile = array();
        }

        // Religion
        $tblSubjectReligion = Subject::useService()->getSubjectReligionAll();
        if ($tblSubjectReligion) {
            array_push($tblSubjectReligion, new TblSubject());
        } else {
            $tblSubjectReligion = array();
        }

        // ForeignLanguage
        $tblSubjectForeignLanguage = Subject::useService()->getSubjectForeignLanguageAll();
        if ($tblSubjectForeignLanguage) {
            array_push($tblSubjectForeignLanguage, new TblSubject());
        } else {
            $tblSubjectForeignLanguage = array();
        }

        $tblSubjectAll = Subject::useService()->getSubjectAll();
        array_push($tblSubjectAll, new TblSubject());

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Kurse / Profile / Religionsunterricht', array(
                        new SelectBox('Meta[Subject][Orientation]', 'Neigungskurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectOrientation),
                            new Education())
                        .new Panel('Historie', array(
                            'Klasse 8: Philosophie',
                            'Klasse 9: Mathematik'
                        ))
                        .new Warning(
                            'Vom System erkannte Fachklassen<br/>Wird bei Zensurenvergabe im entsprechenden Fach erzeugt'
                        ),
                        new SelectBox('Meta[Subject][Advanced]', 'Vertiefungskurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAdvanced),
                            new Education())
                        .new Panel('Historie', array(
                            'Klasse 10: Germanistik',
                            'Klasse 10: Mathematik'
                        ))
                        .new Warning(
                            'Vom System erkannte Fachklassen<br/>Wird bei Zensurenvergabe im entsprechenden Fach erzeugt'
                        ),
                        new SelectBox('Meta[Subject][Profile]', 'Profil',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectProfile),
                            new Education()),
                        new SelectBox('Meta[Subject][Religion]', 'Religion',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectReligion),
                            new Education()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Fremdsprachen', array(
                        new SelectBox('Meta[Subject][ForeignLanguage][First]', '1. Fremdsprache',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectForeignLanguage),
                            new Education()),
                        new SelectBox('Meta[Subject][ForeignLanguage][Second]', '2. Fremdsprache',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectForeignLanguage),
                            new Education()),
                        new SelectBox('Meta[Subject][ForeignLanguage][Third]', '3. Fremdsprache',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectForeignLanguage),
                            new Education()),
                        new SelectBox('Meta[Subject][ForeignLanguage][Fourth]', '4. Fremdsprache',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectForeignLanguage),
                            new Education()),
                    ), Panel::PANEL_TYPE_INFO),
                    new Panel('Wahlfächer', array(
                        new SelectBox('Meta[Subject][Elective][First]', '1. Wahlfach',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectElective),
                            new Education()),
                        new SelectBox('Meta[Subject][Elective][Second]', '2. Wahlfach',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectElective),
                            new Education()),
                    ), Panel::PANEL_TYPE_INFO),
                    new Panel('Arbeitsgemeinschaften', array(
                        new SelectBox('Meta[Subject][Team][First]', '1. Arbeitsgemeinschaft',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Team][Second]', '2. Arbeitsgemeinschaft',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Team][Third]', '3. Arbeitsgemeinschaft',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Leistungskurs / Grundkurs', array(
                        new SelectBox('Meta[Subject][Track][Intensive][First]', new Education().' 1. Leistungskurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Intensive][Second]', new Education().' 2. Leistungskurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Basic][0]', 'Grundkurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Basic][1]', 'Grundkurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Basic][2]', 'Grundkurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Basic][3]', 'Grundkurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Basic][4]', 'Grundkurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Basic][5]', 'Grundkurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Basic][6]', 'Grundkurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Basic][7]', 'Grundkurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                        new SelectBox('Meta[Subject][Track][Basic][8]', 'Grundkurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                            new Education()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
            )),
        ), new Title('Unterrichtsfächer',
            new Warning(
                new Danger(
                    new \SPHERE\Common\Frontend\Icon\Repository\Warning().' Es können im Moment nur fest vorgegebene Fächer in der aktuellen Demo-Version verwendet werden'
                )
            )
        ));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array          $Meta
     *
     * @return FormGroup
     */
    private function formGroupIntegration(TblPerson $tblPerson = null, $Meta = array())
    {

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        array_push($tblCompanyAllSchool, new TblCompany());

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
                            'Schulbegleitung '.new Small(new Muted('Integrationsbeauftragter')), array(), new Person()),
                        new NumberField('Meta[Integration][3]', 'Stundenbedarf pro Woche',
                            'Stundenbedarf pro Woche', new Clock()),
                        new TextArea('Meta[Integration][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),

                    ), Panel::PANEL_TYPE_INFO), 3),
                new FormColumn(
                // TODO::
                    new Panel('Förderbedarf: Schwerpunkte', array(
                        new CheckBox('Meta[Integration][PracticeModule][1]', 'Sprache', 1),
                        new CheckBox('Meta[Integration][PracticeModule][1]', 'Körperlich-motorische Entwicklung', 1),
                        new CheckBox('Meta[Integration][PracticeModule][1]', 'Sozial-emotionale Entwicklung', 1),
                        new CheckBox('Meta[Integration][PracticeModule][1]', 'Hören', 1),
                        new CheckBox('Meta[Integration][PracticeModule][1]', 'Sehen', 1),
                        new CheckBox('Meta[Integration][PracticeModule][1]', 'Geistige Entwicklung', 1),
                        new CheckBox('Meta[Integration][PracticeModule][1]', 'Lernen', 1),
                    ), Panel::PANEL_TYPE_INFO), 3),
                new FormColumn(
                // TODO::
                    new Panel('Förderbedarf: Teilleistungsstörungen', array(
                        new CheckBox('Meta[Integration][Disorder][5]', 'LRS', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Gehörschwierigkeiten', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Augenleiden', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Sprachfehler', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Dyskalkulie', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Autismus', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'ADS / ADHS', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Rechenschwäche', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Hochbegabung', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Konzentrationsstörung', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Körperliche Beeinträchtigung', 1),
                    ), Panel::PANEL_TYPE_INFO), 3),
            )),
        ), new Title('Integration'));
    }
}
