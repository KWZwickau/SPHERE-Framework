<?php
namespace SPHERE\Application\People\Meta\Student;

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
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Heart;
use SPHERE\Common\Frontend\Icon\Repository\Lock;
use SPHERE\Common\Frontend\Icon\Repository\Medicine;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Sheriff;
use SPHERE\Common\Frontend\Icon\Repository\Stethoscope;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Student
 */
class Frontend implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendMeta( TblPerson $tblPerson = null, $Meta = array() )
    {

        $Stage = new Stage();

        $Stage->setContent( ( new Form( array(
            new FormGroup( array(
                new FormRow( array(
                        new FormColumn(
                            new Panel( 'Krankenakte', array(
                                new TextArea( 'Meta[MedicalRecord][Disease]', 'Krankheiten / Allergien',
                                    'Krankheiten / Allergien', new Heart() ),
                                new TextArea( 'Meta[MedicalRecord][Medication]', 'Mediakamente', 'Mediakamente',
                                    new Medicine() ),
                                new SelectBox( 'Meta[MedicalRecord][AttendingDoctor]', 'Behandelnder Arzt', array(),
                                    new Stethoscope() ),
                                new SelectBox( 'Meta[MedicalRecord][InsuranceState]', 'Versicherungsstatus', array(
                                    0 => '',
                                    1 => 'Pflicht',
                                    2 => 'Freiwillig',
                                    3 => 'Privat',
                                    4 => 'Familie Vater',
                                    5 => 'Familie Mutter',
                                ), new Lock() ),
                                new AutoCompleter( 'Meta[MedicalRecord][Insurance]', 'Krankenkasse', 'Krankenkasse',
                                    array(), new Sheriff() ),
                            ), Panel::PANEL_TYPE_INFO ), 4 ),
                        new FormColumn(
                            new Panel( 'Schulbeförderung', array(
                                new TextField( 'Meta[Transport][Route]', 'Buslinie', 'Buslinie' ),
                                new TextField( 'Meta[Transport][Station][Entrance]', 'Einstiegshaltestelle',
                                    'Einstiegshaltestelle' ),
                                new TextField( 'Meta[Transport][Station][Exit]', 'Ausstiegshaltestelle',
                                    'Ausstiegshaltestelle' ),
                                new TextArea( 'Meta[Transport][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil() ),
                            ), Panel::PANEL_TYPE_INFO ), 4 ),
                        new FormColumn(
                            new Panel( 'Erlaubnis zur Nutzung des Schüler-Fotos', array(
                                new CheckBox( 'Meta[PicturePermission][Intern]', 'Schulschriften', 1 ),
                                new CheckBox( 'Meta[PicturePermission][Extern]', 'Veröffentlichungen', 1 ),
                                new CheckBox( 'Meta[PicturePermission][Internet]', 'Internetpräsenz', 1 ),
                                new CheckBox( 'Meta[PicturePermission][Facebook]', 'Facebookseite', 1 ),
                                new CheckBox( 'Meta[PicturePermission][Press]', 'Druckpresse', 1 ),
                                new CheckBox( 'Meta[PicturePermission][Multimedia]', 'Ton/Video/Film', 1 ),
                                new CheckBox( 'Meta[PicturePermission][Promotion]', 'Werbung in eigener Sache', 1 ),
                            ), Panel::PANEL_TYPE_INFO ), 4 ),
                        new FormColumn(
                            new Panel( 'Integration', array(
                                new CheckBox( 'Meta[Integration][CoachingRequired]', 'Förderbedarf', 1 ),
                                new Aspect( 'Förderschwerpunkte:' ),
                                new CheckBox( 'Meta[Integration][PracticeModule][1]', 'Schwerpunkt A', 1 ),
                                new CheckBox( 'Meta[Integration][PracticeModule][2]', 'Schwerpunkt B', 1 ),
                                new CheckBox( 'Meta[Integration][PracticeModule][3]', 'Schwerpunkt C', 1 ),
                                new Aspect( 'Teilleistungsstörungen:' ),
                                new CheckBox( 'Meta[Integration][Disorder][1]', 'Störung A', 1 ),
                                new CheckBox( 'Meta[Integration][Disorder][2]', 'Störung B', 1 ),
                                new CheckBox( 'Meta[Integration][Disorder][3]', 'Störung C', 1 ),
                                new CheckBox( 'Meta[Integration][Disorder][4]', 'Störung D', 1 ),
                                new CheckBox( 'Meta[Integration][Disorder][5]', 'Störung E', 1 ),
                                new DatePicker( 'Meta[Integration][CoachingCounselDate]', 'Förderantrag Beratung',
                                    'Förderantrag Beratung',
                                    new Calendar()
                                ),
                                new DatePicker( 'Meta[Integration][CoachingRequestDate]', 'Förderantrag',
                                    'Förderantrag',
                                    new Calendar()
                                ),
                                new DatePicker( 'Meta[Integration][CoachingDecisionDate]', 'Förderbescheid SBA',
                                    'Förderbescheid SBA',
                                    new Calendar()
                                ),
                                new SelectBox( 'Meta[Integration][3]', 'Förderschule', array() ),
                                new SelectBox( 'Meta[Integration][3]', 'Schulbegleitung', array() ),
                                new NumberField( 'Meta[Integration][3]', 'Stundenbedarf pro Woche',
                                    'Stundenbedarf pro Woche' ),
                                new SelectBox( 'Meta[Integration][3]', 'Behandelnder Arzt', array() ),
                                new TextArea( 'Meta[Integration][Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil() ),

                            ), Panel::PANEL_TYPE_INFO ), 4 ),
                    )
                ),
            ),
                new Title( 'Termine' )
            ),
        ),
            new Primary( 'Informationen speichern' )
        )
        )->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert.' ) );

        return $Stage;
    }
}
