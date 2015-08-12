<?php
namespace SPHERE\Application\People\Meta\Student;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
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
                        new Panel( 'Titel',
                            new DatePicker( 'Meta[ReservationDate]', 'Eingangsdatum', 'Eingangsdatum',
                                new Calendar()
                            )
                        ), 4 ),
                    new FormColumn(
                        new Panel( 'Titel',
                            new DatePicker( 'Meta[InterviewDate]', 'Aufnahmegespräch', 'Aufnahmegespräch',
                                new Calendar()
                            )
                        ), 4 ),
                    new FormColumn(
                        new Panel( 'Titel',
                            new DatePicker( 'Meta[TrialDate]', 'Schnuppertag', 'Schnuppertag',
                                new Calendar()
                            )
                        ), 4 ),
                ) ),
            ), new Title( 'Termine' ) ),
        ), new Primary( 'Informationen speichern' ) )
        )->setConfirm( 'Eventuelle Änderungen wurden noch nicht gespeichert.' ) );

        return $Stage;
    }
}
