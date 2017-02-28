<?php

namespace SPHERE\Application\Reporting\Standard\Company;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Company
 *
 * @package SPHERE\Application\Reporting\Standard\Company
 */
class Company implements IModuleInterface
{

    public static function registerModule()
    {

//        Main::getDisplay()->addApplicationNavigation(
//            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Firmen'))
//        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/GroupList'), new Link\Name('Institutionengruppenlisten'))
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
