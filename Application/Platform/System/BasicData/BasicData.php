<?php

namespace SPHERE\Application\Platform\System\BasicData;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Document;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class BasicData
 *
 * @package SPHERE\Application\Platform\System\BasicData
 */
class BasicData  extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
        /**
         * Register Navigation
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '\Holiday'), new Link\Name('Grunddaten'), new Link\Icon(new Document()))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Holiday', __NAMESPACE__.'\Frontend::frontendHoliday')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Holiday/Import', __NAMESPACE__.'\Frontend::frontendImportHoliday')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Platform', 'System', 'BasicData'),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}