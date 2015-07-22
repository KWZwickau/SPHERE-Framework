<?php
namespace SPHERE\Application\System\Gatekeeper\Authentication;

use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication
 */
class Frontend
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage();
        $Stage->setTitle( 'Willkommen' );
        $Stage->setDescription( 'KREDA Professional' );
        $Stage->setMessage( date( 'd.m.Y - H:i:s' ) );
        return $Stage;

    }
}
