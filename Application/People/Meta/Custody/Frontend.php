<?php
namespace SPHERE\Application\People\Meta\Custody;

use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonBirthDates;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonInformation;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\AutoCompleter;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\SelectBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\MapMarker;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\TempleChurch;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\Debugger;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Custody
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param TblPerson $tblPerson
     * @param array     $Meta
     *
     * @return Stage
     */
    public function frontendMeta( TblPerson $tblPerson = null, $Meta = array() )
    {
        Debugger::screenDump( __METHOD__, $Meta );
        $Stage = new Stage( 'Sorgeberechtigt', '' );

        $Stage->setContent( ( new Form( array(
            new FormGroup( array(
                new FormRow( array(
                    new FormColumn(
                        new DatePicker( 'Meta[BirthDates][Birthday]', 'Geburtstag', 'Geburtstag',
                            new Calendar()
                        ), 3 ),
                    new FormColumn(
                        new AutoCompleter( 'Meta[BirthDates][Birthplace]', 'Geburtsort', 'Geburtsort',
                            array(), new MapMarker()
                        ), 9 ),
                ) ),
                new FormRow( array(
                    new FormColumn(
                        new SelectBox( 'Meta[BirthDates][Gender]', 'Geschlecht', array(
                            TblCommonBirthDates::VALUE_GENDER_NULL   => '',
                            TblCommonBirthDates::VALUE_GENDER_MALE   => 'Männlich',
                            TblCommonBirthDates::VALUE_GENDER_FEMALE => 'Weiblich'
                        ), new PersonParent()
                        ), 3 ),
                    new FormColumn(
                        new AutoCompleter( 'Meta[BirthDates][Nationality]', 'Staatsangehörigkeit',
                            'Staatsangehörigkeit',
                            array(), new Nameplate()
                        ), 9 ),
                ) ),
            ), new Title( 'Geburtsdaten' ) ),
            new FormGroup( array(
                new FormRow( array(
                    new FormColumn(
                        new AutoCompleter( 'Meta[Information][Denomination]', 'Konfession',
                            'Konfession',
                            array(), new TempleChurch()
                        ) ),
                ) ),
                new FormRow( array(
                    new FormColumn(
                        new SelectBox( 'Meta[Information][IsAssistance]', 'Mitarbeitsbereitschaft', array(
                            TblCommonInformation::VALUE_IS_ASSISTANCE_NULL => '',
                            TblCommonInformation::VALUE_IS_ASSISTANCE_YES  => 'Ja',
                            TblCommonInformation::VALUE_IS_ASSISTANCE_NO   => 'Nein'
                        ), new PersonParent()
                        ), 3 ),
                    new FormColumn(
                        new TextArea( 'Meta[Information][AssistanceActivity]',
                            'Mitarbeitsbereitschaft - Tätigkeiten',
                            'Mitarbeitsbereitschaft - Tätigkeiten'
                        ), 9 ),
                ) ),
            ), new Title( 'Informationen' ) ),
            new FormGroup( array(
                new FormRow( array(
                    new FormColumn(
                        new TextArea( 'Meta[Common][Remark]',
                            'Bemerkungen',
                            'Bemerkungen'
                        ) ),
                ) ),
            ), new Title( 'Sonstiges' ) ),
        ), new Primary( 'Allgemeine Daten speichern' ) )
        )->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert.' ) );

        return $Stage;
    }
}
