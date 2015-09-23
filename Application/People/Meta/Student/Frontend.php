<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Corporation\Group\Group;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblCategory;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
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
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Frontend implements IFrontendInterface
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

        $tblCompanyAllSchool = Group::useService()->getCompanyAllByGroup(
            Group::useService()->getGroupByMetaTable('SCHOOL')
        );
        array_push($tblCompanyAllSchool, new TblCompany());

        // Orientation
        $tblCategoryOrientation = Subject::useService()->getGroupByIdentifier('ORIENTATION')->getTblCategoryAll();
        array_walk($tblCategoryOrientation, function (TblCategory &$tblCategory) {

            $tblCategory = $tblCategory->getTblSubjectAll();
        });
        $tblSubjectOrientation = array();
        array_walk_recursive($tblCategoryOrientation, function ($tblSubject) use (&$tblSubjectOrientation) {

            $tblSubjectOrientation[] = $tblSubject;
        });
        array_push($tblSubjectOrientation, new TblSubject());

        // Advanced
        $tblCategoryAdvanced = Subject::useService()->getGroupByIdentifier('ADVANCED')->getTblCategoryAll();
        array_walk($tblCategoryAdvanced, function (TblCategory &$tblCategory) {

            $tblCategory = $tblCategory->getTblSubjectAll();
        });
        $tblSubjectAdvanced = array();
        array_walk_recursive($tblCategoryAdvanced, function ($tblSubject) use (&$tblSubjectAdvanced) {

            $tblSubjectAdvanced[] = $tblSubject;
        });
        array_push($tblSubjectAdvanced, new TblSubject());

        // Profile
        $tblCategoryProfile = Subject::useService()->getGroupByIdentifier('STANDARD')->getTblCategoryByIdentifier('PROFILE');
        $tblCategoryProfile = $tblCategoryProfile->getTblSubjectAll();
        $tblSubjectProfile = array();
        array_walk_recursive($tblCategoryProfile, function ($tblSubject) use (&$tblSubjectProfile) {

            $tblSubjectProfile[] = $tblSubject;
        });
        array_push($tblSubjectProfile, new TblSubject());

        // Religion
        $tblCategoryReligion = Subject::useService()->getGroupByIdentifier('STANDARD')->getTblCategoryByIdentifier('RELIGION');
        $tblCategoryReligion = $tblCategoryReligion->getTblSubjectAll();
        $tblSubjectReligion = array();
        array_walk_recursive($tblCategoryReligion, function ($tblSubject) use (&$tblSubjectReligion) {

            $tblSubjectReligion[] = $tblSubject;
        });
        array_push($tblSubjectReligion, new TblSubject());

        // ForeignLanguage
        $tblCategoryForeignLanguage = Subject::useService()->getGroupByIdentifier('STANDARD')->getTblCategoryByIdentifier('FOREIGNLANGUAGE');
        $tblCategoryForeignLanguage = $tblCategoryForeignLanguage->getTblSubjectAll();
        $tblSubjectForeignLanguage = array();
        array_walk_recursive($tblCategoryForeignLanguage, function ($tblSubject) use (&$tblSubjectForeignLanguage) {

            $tblSubjectForeignLanguage[] = $tblSubject;
        });
        array_push($tblSubjectForeignLanguage, new TblSubject());





        $tblSubjectAll = Subject::useService()->getSubjectAll();
        array_push($tblSubjectAll, new TblSubject());

        $tblSubjectAllForeignLanguage = Subject::useService()->getSubjectAllByCategory(
            Subject::useService()->getCategoryById(2)
        );
        array_push($tblSubjectAllForeignLanguage, new TblSubject());
        $tblSubjectAllReligion = Subject::useService()->getSubjectAllByCategory(
            Subject::useService()->getCategoryById(6)
        );
        array_push($tblSubjectAllReligion, new TblSubject());

        $Stage->setMessage(
            new Danger(
                new Info().' Es dürfen ausschließlich für die Schulverwaltung notwendige Informationen gespeichert werden.'
            )
        );

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
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Ersteinschulung', array(
                            new SelectBox('Meta[Transfer][1]', 'Schule',
                                array('{{ Name }} {{ Description }}' => $tblCompanyAllSchool),
                                new Education()),
                            new DatePicker('Meta[Transfer][2]', 'Datum', 'Datum', new Calendar()),
                            new TextArea('Meta[Transfer][1]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                    new FormColumn(array(
                        new Panel('Schülertransfer - Aufnahme', array(
                            new SelectBox('Meta[Transfer][0]', 'Letzte Schulart', array()),
                            new SelectBox('Meta[Transfer][1]', 'Abgebende Schule',
                                array('{{ Name }} {{ Description }}' => $tblCompanyAllSchool),
                                new Education()),
                            new DatePicker('Meta[Transfer][2]', 'Datum', 'Datum', new Calendar()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                    new FormColumn(array(
                        new Panel('Schülertransfer - Abgabe', array(
                            new SelectBox('Meta[Transfer][0]', 'Letzte Schulart', array()),
                            new SelectBox('Meta[Transfer][1]', 'Aufnehmende Schule',
                                array('{{ Name }} {{ Description }}' => $tblCompanyAllSchool),
                                new Education()),
                            new DatePicker('Meta[Transfer][2]', 'Datum', 'Datum', new Calendar()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Schülertransfer - Schulverlauf', array(
                            new TextArea('Meta[Transfer][1]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO),
                    )),
                )),
                new FormRow(array(
                    new FormColumn(array(
                        new Panel('Unterrichtsfächer - Profil', array(
                            new SelectBox('Meta[Subject][1]', 'Neigungskurs',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectOrientation),
                                new Education())
                            .new Listing(array(
                                'Klasse 8: Philosophie',
                                'Klasse 9: Mathematik'
                            ))
                        ,
                            new SelectBox('Meta[Subject][1]', 'Vertiefungskurs',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAdvanced),
                                new Education())
                            .new Listing(array(
                                'Klasse 10: Germanistik',
                                'Klasse 10: Mathematik'
                            ))
                        ,
                            new SelectBox('Meta[Subject][1]', 'Profil',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectProfile),
                                new Education()),
                            new SelectBox('Meta[Subject][1]', 'Religion',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectReligion),
                                new Education()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                    new FormColumn(array(
                        new Panel('Unterrichtsfächer - Fremdsprachen', array(
                            new SelectBox('Meta[Subject][1]', '1. Fremdsprache',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectForeignLanguage),
                                new Education()),
                            new SelectBox('Meta[Subject][2]', '2. Fremdsprache',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectForeignLanguage),
                                new Education()),
                            new SelectBox('Meta[Subject][3]', '3. Fremdsprache',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectForeignLanguage),
                                new Education()),
                            new SelectBox('Meta[Subject][4]', '4. Fremdsprache',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectForeignLanguage),
                                new Education()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                    new FormColumn(array(
                        new Panel('Unterrichtsfächer - Wahlpflichtfächer', array(
                            new SelectBox('Meta[Subject][1]', '1. Wahlpflichtfach',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                                new Education()),
                            new SelectBox('Meta[Subject][2]', '2. Wahlpflichtfach',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                                new Education()),
                            new SelectBox('Meta[Subject][3]', '3. Wahlpflichtfach',
                                array('{{ Acronym }} - {{ Name }} {{ Description }}' => $tblSubjectAll),
                                new Education()),
                        ), Panel::PANEL_TYPE_INFO),
                    ), 4),
                )),

            )),
        ),
            new Primary('Informationen speichern')
        )
        )->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.'));

        return $Stage;
    }
}
