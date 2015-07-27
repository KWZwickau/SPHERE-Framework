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
 * Class Level
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access\Frontend
 */
class Level
{

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendCreateLevel( $Name )
    {

        $Stage = new Stage( 'Berechtigungen', 'Zugriffslevel' );

        $tblAccessAll = Access::useService()->getLevelAll();

        $Stage->setContent(
            ( $tblAccessAll
                ? new TableData( $tblAccessAll, new Title( 'Bestehende Zugriffslevel' ) )
                : new Warning( 'Keine Zugriffslevel vorhanden' )
            )
            .Access::useService()->createLevel(
                new Form(
                    new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField( 'Name', 'Name', 'Name' )
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Zugriffslevel anlegen' ) )
                    , new Primary( 'Hinzufügen' )
                ), $Name
            )
        );
        return $Stage;
    }
}
