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
 * Class Privilege
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Frontend
 */
class Privilege
{

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendCreatePrivilege( $Name )
    {

        $Stage = new Stage( 'Berechtigungen', 'Privilegien' );

        $tblPrivilegeAll = Authorization::useService()->getPrivilegeAll();

        $Stage->setContent(
            ( $tblPrivilegeAll
                ? new TableData( $tblPrivilegeAll, new Title( 'Bestehende Privilegien' ) )
                : new Warning( 'Keine Privilegien vorhanden' )
            )
            .Authorization::useService()->createPrivilege(
                new Form(
                    new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField( 'Name', 'Name', 'Name' )
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Privileg anlegen' ) )
                    , new Primary( 'Hinzufügen' )
                ), $Name
            )
        );
        return $Stage;
    }
}
