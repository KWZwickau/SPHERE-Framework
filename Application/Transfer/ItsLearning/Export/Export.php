<?php
namespace SPHERE\Application\Transfer\ItsLearning\Export;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Export
 * @package SPHERE\Application\Transfer\ItsLearning\Export
 */
class Export implements IModuleInterface
{

    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Benutzer exportieren'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, '/Frontend::frontendDownload'
        ));
    }

    public static function useService()
    {
        return new Service();
    }

    public static function useFrontend()
    {
        return new Frontend();
    }
}