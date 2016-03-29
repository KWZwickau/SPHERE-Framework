<?php
namespace SPHERE\Application\Api\Education\Graduation;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class Graduation extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Certificate', __NAMESPACE__.'\Certificate\Creator::createPdf'
        ));
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
