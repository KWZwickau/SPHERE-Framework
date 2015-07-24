<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Frontend;

use SPHERE\Application\System\Gatekeeper\Authorization\Authorization;
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
 * Class Role
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Frontend
 */
class Role
{

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendCreateRole( $Name )
    {

        $Stage = new Stage( 'Berechtigungen', 'Rollen' );

        $tblRoleAll = Authorization::useService()->getRoleAll();

        $Stage->setContent(
            ( $tblRoleAll
                ? new TableData( $tblRoleAll, new Title( 'Bestehende Rollen' ) )
                : new Warning( 'Keine Rollen vorhanden' )
            )
            .Authorization::useService()->createRole(
                new Form(
                    new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField( 'Name', 'Name', 'Name' )
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Rolle anlegen' ) )
                    , new Primary( 'Hinzufügen' )
                ), $Name
            )
        );
        return $Stage;
    }
}
