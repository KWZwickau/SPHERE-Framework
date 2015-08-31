<?php
namespace SPHERE\Application\Platform\Assistance\Support;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Repository\Title;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Assistance\Support\Ticket
 */
class Frontend implements IFrontendInterface
{

    /**
     * @param $TicketSubject
     * @param $TicketMessage
     *
     * @return Stage
     */
    public function frontendTicket($TicketSubject, $TicketMessage)
    {

        $Stage = new Stage('Support', 'Ticket erstellen');
        $Stage->setContent((new Service())->executeCreateTicket(
            new Form(array(
                new FormGroup(
                    new FormRow(
                        new FormColumn(
                            new TextField(
                                'TicketSubject', 'Thema', 'Thema'
                            )
                        )
                    ), new Title('Problembeschreibung')
                ),
                new FormGroup(array(
                        new FormRow(
                            new FormColumn(
                                new TextArea(
                                    'TicketMessage', 'Mitteilung', 'Mitteilung'
                                )
                            )
                        ),
                        new FormRow(
                            new FormColumn(array(
                                new Warning(
                                    'Bitte teilen Sie uns so genau wie möglich mit wie es zu diesem Problem kam'
                                ),
                                new Danger(
                                    'Sollte Ihr Problem bereits gemeldet worden sein, eröffnen Sie bitte kein neues Ticket'
                                )
                            ))
                        )
                    )
                )
            ), new Primary('Ticket eröffnen')
            ), $TicketSubject, $TicketMessage
        ));
        return $Stage;
    }
}
