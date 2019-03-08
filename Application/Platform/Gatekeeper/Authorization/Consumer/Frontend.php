<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
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
     * @param $ConsumerAlias
     *
     * @return Stage
     */
    public static function frontendConsumer($ConsumerAcronym, $ConsumerName, $ConsumerAlias)
    {

        $Stage = new Stage('Mandanten');
        $tblConsumerAll = Consumer::useService()->getConsumerAll();
        $TableContent = array();
        array_walk($tblConsumerAll, function(TblConsumer $tblConsumer) use (&$TableContent){
            $Item['Acronym'] = $tblConsumer->getAcronym();
            $Item['Name'] = $tblConsumer->getName();
            $Item['Alias'] = $tblConsumer->getAlias();
            array_push($TableContent, $Item);
        });
        $Stage->setContent(
            new TableData($TableContent, new Title('Bestehende Mandanten'), array(
                'Acronym' => 'Mandanten-Kürzel',
                'Name'    => 'Mandanten-Name',
                'Alias' => 'Mandanten-Alias'
            ))
            .Consumer::useService()->createConsumer(
                new Form(new FormGroup(
                        new FormRow(array(
                            new FormColumn(
                                new TextField(
                                    'ConsumerAcronym', 'Kürzel des Mandanten', 'Kürzel des Mandanten'
                                )
                                , 2),
                            new FormColumn(
                                new TextField(
                                    'ConsumerName', 'Name des Mandanten', 'Name des Mandanten'
                                )
                                , 6),
                            new FormColumn(
                                new TextField(
                                    'ConsumerAlias', 'Alias des Mandanten', 'Alias des Mandanten'
                                )
                                , 4),
                        )), new \SPHERE\Common\Frontend\Form\Repository\Title('Mandant anlegen'))
                    , new Primary('Hinzufügen')
                ), $ConsumerAcronym, $ConsumerName, $ConsumerAlias
            )
        );
        return $Stage;
    }
}
