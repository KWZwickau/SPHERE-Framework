<?php
namespace SPHERE\Application\System\Assistance\Support;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Support
 *
 * @package SPHERE\Application\System\Assistance\Support
 */
class Support implements IModuleInterface
{

    public static function registerModule()
    {

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Support' ) )
        );
        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__.'/Ticket' ), new Link\Name( 'Ticket' ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__,
                'Support::frontendSupport'
            )
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__.'/Ticket',
                'Frontend::frontendTicket'
            )
                ->setParameterDefault( 'TicketSubject', null )
                ->setParameterDefault( 'TicketMessage', null )
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     *
     * @return Stage
     */
    public function frontendSupport()
    {

        $Stage = new Stage( 'Support', '' );

        return $Stage;
    }
}
