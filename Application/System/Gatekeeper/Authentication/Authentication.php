<?php
namespace SPHERE\Application\System\Gatekeeper\Authentication;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Gatekeeper\Authentication\Identification\Identification;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Authentication
 *
 * @package SPHERE\Application\System\Gatekeeper\Authentication
 */
class Authentication implements IModuleInterface
{

    public static function registerModule()
    {

        Identification::registerModule();
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( '', __CLASS__.'::frontendWelcome' )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

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
