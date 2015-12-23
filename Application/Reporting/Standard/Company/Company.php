<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 30.11.2015
 * Time: 15:45
 */

namespace SPHERE\Application\Reporting\Standard\Company;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

class Company implements IModuleInterface
{

    public static function registerModule()
    {

//        Main::getDisplay()->addApplicationNavigation(
//            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Firmen'))
//        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/GroupList'), new Link\Name('Firmengruppenlisten'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/GroupList', __NAMESPACE__.'\Frontend::frontendGroupList'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
