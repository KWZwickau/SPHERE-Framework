<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer;

use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Consumer
 */
class Frontend
{

    /**
     * @param string $ConsumerAcronym
     * @param string $ConsumerName
     *
     * @return Stage
     */
    public static function frontendConsumer($ConsumerAcronym, $ConsumerName)
    {

        $Stage = new Stage('Mandanten');
        $tblConsumerAll = Consumer::useService()->getConsumerAll();
        $Stage->setContent(
            new TableData($tblConsumerAll, new Title('Bestehende Mandanten'), array(
                'Acronym' => 'Mandanten-Kürzel',
                'Name'    => 'Mandanten-Name'
            ))
            .Consumer::useService()->createConsumer(
                new Form(new FormGroup(
                        new FormRow(array(
                            new FormColumn(
                                new TextField(
                                    'ConsumerAcronym', 'Kürzel des Mandanten', 'Kürzel des Mandanten'
                                )
                                , 4),
                            new FormColumn(
                                new TextField(
                                    'ConsumerName', 'Name des Mandanten', 'Name des Mandanten'
                                )
                                , 8),
                        )), new \SPHERE\Common\Frontend\Form\Repository\Title('Mandant anlegen'))
                    , new Primary('Hinzufügen')
                ), $ConsumerAcronym, $ConsumerName
            )
        );
        return $Stage;
    }
}
