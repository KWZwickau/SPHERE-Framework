<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Access\Frontend;

use SPHERE\Application\System\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Right
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access\Frontend
 */
class Right
{

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendCreateRight( $Name )
    {

        $Stage = new Stage( 'Berechtigungen', 'Rechte' );

        $tblRightAll = Access::useService()->getRightAll();

        $Stage->setContent(
            ( $tblRightAll
                ? new TableData( $tblRightAll, new Title( 'Bestehende Rechte', 'Routen' ) )
                : new Warning( 'Keine Routen vorhanden' )
            )
            .Access::useService()->createRight(
                new Form(
                    new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField( 'Name', 'Name', 'Name' )
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Recht anlegen', 'Route' ) )
                    , new Primary( 'Hinzufügen' )
                ), $Name
            )
        );
        return $Stage;
    }
}
