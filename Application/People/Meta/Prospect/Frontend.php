<?php
namespace SPHERE\Application\People\Meta\Prospect;

use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\DatePicker;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Calendar;
use SPHERE\Common\Frontend\Icon\Repository\Info;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\People\Meta\Prospect
 */
class Frontend implements IFrontendInterface
{

    /**
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

        $Stage->setContent((new Form(array(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel('Termine', array(
                            new DatePicker('Meta[ReservationDate]', 'Eingangsdatum', 'Eingangsdatum',
                                new Calendar()
                            ),
                            new DatePicker('Meta[InterviewDate]', 'Aufnahmegespräch', 'Aufnahmegespräch',
                                new Calendar()
                            ),
                            new DatePicker('Meta[TrialDate]', 'Schnuppertag', 'Schnuppertag',
                                new Calendar()
                            ),
                        ), Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel('Voranmeldung für', array(
                            new TextField('Meta[ReservationYear]', 'Schuljahr', 'Schuljahr'),
                            new TextField('Meta[ReservationDivision]', 'Klassenstufe', 'Klassenstufe'),
                        ), Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        new Panel('Sonstiges', array(
                            new TextArea('Meta[Remark]', 'Bemerkungen', 'Bemerkungen', new Pencil()),
                        ), Panel::PANEL_TYPE_INFO
                        )),
                )),
            )),
        ), new Primary('Informationen speichern'))
        )->setConfirm('Eventuelle Änderungen wurden noch nicht gespeichert.'));

        return $Stage;
    }
}
