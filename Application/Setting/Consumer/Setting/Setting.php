<?php

namespace SPHERE\Application\Setting\Consumer\Setting;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Extension\Extension;

/**
 * Class Setting
 *
 * @package SPHERE\Application\Setting\Consumer\Setting
 */
class Setting extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Mandanteinstellungen'), new Link\Icon(new Cog()))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__,
                __NAMESPACE__.'/Frontend::frontendSettings'
            )
        );
    }

    /**
     * @return Service
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