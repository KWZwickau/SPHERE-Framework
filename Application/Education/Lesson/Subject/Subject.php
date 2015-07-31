<?php
namespace SPHERE\Application\Education\Lesson\Subject;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class Subject implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Fächer' ) )
        );
        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ) );
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

}
