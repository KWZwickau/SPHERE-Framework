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
 * Class Access
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Frontend
 */
class Access
{

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendCreateAccess( $Name )
    {

        $Stage = new Stage( 'Berechtigungen', 'Zugriffslevel' );

        $tblAccessAll = Authorization::useService()->getAccessAll();

        $Stage->setContent(
            ( $tblAccessAll
                ? new TableData( $tblAccessAll, new Title( 'Bestehende Zugriffslevel' ) )
                : new Warning( 'Keine Zugriffslevel vorhanden' )
            )
            .Authorization::useService()->createAccess(
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
