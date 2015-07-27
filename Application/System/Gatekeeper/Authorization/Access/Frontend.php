<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Access;

use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access
 */
class Frontend
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage( 'Rechteverwaltung' );
        return $Stage;
    }
}
