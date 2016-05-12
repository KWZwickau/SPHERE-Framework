<?php
namespace SPHERE\Application\Platform\Assistance\Support;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Main;

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

        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                'Frontend::frontendTicket'
            )
                ->setParameterDefault('TicketSubject', null)
                ->setParameterDefault('TicketMessage', null)
        );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
