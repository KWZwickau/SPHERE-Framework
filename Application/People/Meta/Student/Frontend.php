<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
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
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Text\Repository\Danger;
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
            new Danger(
                new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

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

        $Stage->setContent((new Form(array(
            new FormGroup(array(
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
                        new Panel('Sonstiges', array(
                            new DatePicker('Meta[Additional][BaptismDate]', 'Taufdatum', 'Taufdatum',
                                new TempleChurch()
                            ),
                            new TextField('Meta[Additional][BaptismLocation]', 'Taufort', 'Taufort', new MapMarker()),
                            new TextField('Meta[Additional][Locker][Number]', 'Schließfachnummer', 'Schließfachnummer',
                                new Lock()),
                            new TextField('Meta[Additional][Locker][Location]', 'Schließfach Standort',
                                'Schließfach Standort', new MapMarker()),
                            new TextField('Meta[Additional][Locker][Key]', 'Schlüssel Nummer', 'Schlüssel Nummer',
                                new Key()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 3),
                    new FormColumn(array(
                        new Panel('Erlaubnis zur Nutzung des Schüler-Fotos', array(
                            new CheckBox('Meta[PicturePermission][Internal]', 'Schulschriften', 1),
                            new CheckBox('Meta[PicturePermission][External]', 'Veröffentlichungen', 1),
                            new CheckBox('Meta[PicturePermission][Internet]', 'Internetpräsenz', 1),
                            new CheckBox('Meta[PicturePermission][Facebook]', 'Facebookseite', 1),
                            new CheckBox('Meta[PicturePermission][Press]', 'Druckpresse', 1),
                            new CheckBox('Meta[PicturePermission][Multimedia]', 'Ton/Video/Film', 1),
                            new CheckBox('Meta[PicturePermission][Promotion]', 'Werbung in eigener Sache', 1),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 3),
                )),
            )),
            $this->formGroupIntegration($tblPerson, $Meta),
            $this->formGroupTransfer($tblPerson, $Meta),
            $this->formGroupSubject($tblPerson, $Meta),
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
    private function formGroupIntegration(TblPerson $tblPerson = null, $Meta = array())
    {

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        array_push($tblCompanyAllSchool, new TblCompany());

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(
                    new Panel('Integration 1', array(
                        new CheckBox('Meta[Integration][CoachingRequired]', 'Förderbedarf', 1),
                        new Aspect('Förderschwerpunkte:'),
                        new CheckBox('Meta[Integration][PracticeModule][1]', 'Schwerpunkt A', 1),
                        new CheckBox('Meta[Integration][PracticeModule][2]', 'Schwerpunkt B', 1),
                        new CheckBox('Meta[Integration][PracticeModule][3]', 'Schwerpunkt C', 1),
                        new Aspect('Teilleistungsstörungen:'),
                        new CheckBox('Meta[Integration][Disorder][1]', 'Störung A', 1),
                        new CheckBox('Meta[Integration][Disorder][2]', 'Störung B', 1),
                        new CheckBox('Meta[Integration][Disorder][3]', 'Störung C', 1),
                        new CheckBox('Meta[Integration][Disorder][4]', 'Störung D', 1),
                        new CheckBox('Meta[Integration][Disorder][5]', 'Störung E', 1),
                    ), Panel::PANEL_TYPE_INFO), 4),
                new FormColumn(
                    new Panel('Integration 2', array(
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
                    ), Panel::PANEL_TYPE_INFO), 4),
                new FormColumn(
                    new Panel('Integration 3', array(
                        new SelectBox('Meta[Integration][3]', 'Förderschule',
                            array('{{ Name }} {{ Description }}' => $tblCompanyAllSchool),
                            new Education()),
                        new SelectBox('Meta[Integration][3]', 'Schulbegleitung', array(), new Person()),
                        new NumberField('Meta[Integration][3]', 'Stundenbedarf pro Woche',
                            'Stundenbedarf pro Woche', new Clock()),
                        new TextArea('Meta[Integration][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),

                    ), Panel::PANEL_TYPE_INFO), 4),
            )),
        ), new Title('Integration'));
    }

    /**
     * @param TblPerson|null $tblPerson
     * @param array          $Meta
     *
     * @return FormGroup
     */
    private function formGroupTransfer(TblPerson $tblPerson = null, $Meta = array())
    {

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        array_push($tblCompanyAllSchool, new TblCompany());

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Ersteinschulung', array(
                        new SelectBox('Meta[Transfer][Enrollment][School]', 'Schule',
                            array('{{ Name }} {{ Description }}' => $tblCompanyAllSchool),
                            new Education()),
                        new DatePicker('Meta[Transfer][Enrollment][Date]', 'Datum', 'Datum', new Calendar()),
                        new TextArea('Meta[Transfer][Enrollment][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schülertransfer - Aufnahme', array(
                        new SelectBox('Meta[Transfer][Arrive][Type]', 'Letzte Schulart', array()),
                        new SelectBox('Meta[Transfer][Arrive][School]', 'Abgebende Schule',
                            array('{{ Name }} {{ Description }}' => $tblCompanyAllSchool),
                            new Education()),
                        new DatePicker('Meta[Transfer][Arrive][Date]', 'Datum', 'Datum', new Calendar()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Schülertransfer - Abgabe', array(
                        new SelectBox('Meta[Transfer][Leave][Type]', 'Letzte Schulart', array()),
                        new SelectBox('Meta[Transfer][Leave][School]', 'Aufnehmende Schule',
                            array('{{ Name }} {{ Description }}' => $tblCompanyAllSchool),
                            new Education()),
                        new DatePicker('Meta[Transfer][Leave][Date]', 'Datum', 'Datum', new Calendar()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
            )),
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Schülertransfer - Schulverlauf', array(
                        new TextArea('Meta[Transfer][Process][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                    ), Panel::PANEL_TYPE_INFO),
                )),
            )),
        ), new Title('Schülertransfer'));
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

        return new FormGroup(array(
            new FormRow(array(
                new FormColumn(array(
                    new Panel('Unterrichtsfächer - Profil', array(
                        new SelectBox('Meta[Subject][Orientation]', 'Neigungskurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectOrientation),
                            new Education())
                        .new Panel('Historie', array(
                            'Klasse 8: Philosophie',
                            'Klasse 9: Mathematik'
                        ))
                    ,
                        new SelectBox('Meta[Subject][Advanced]', 'Vertiefungskurs',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAdvanced),
                            new Education())
                        .new Panel('Historie', array(
                            'Klasse 10: Germanistik',
                            'Klasse 10: Mathematik'
                        ))
                    ,
                        new SelectBox('Meta[Subject][Profile]', 'Profil',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectProfile),
                            new Education()),
                        new SelectBox('Meta[Subject][Religion]', 'Religion',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectReligion),
                            new Education()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
                new FormColumn(array(
                    new Panel('Unterrichtsfächer - Fremdsprachen', array(
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
                ), 4),
                new FormColumn(array(
                    new Panel('Unterrichtsfächer - Wahlfächer', array(
                        new SelectBox('Meta[Subject][Elective][First]', '1. Wahlfach',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectElective),
                            new Education()),
                        new SelectBox('Meta[Subject][Elective][Second]', '2. Wahlfach',
                            array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectElective),
                            new Education()),
                    ), Panel::PANEL_TYPE_INFO),
                ), 4),
            )),
        ), new Title('Fächer'));
    }
}
