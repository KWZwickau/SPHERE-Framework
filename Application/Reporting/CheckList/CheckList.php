<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:29
 */

namespace SPHERE\Application\Reporting\CheckList;


use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class CheckList implements IApplicationInterface, IModuleInterface
{
    public static function registerApplication()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Check-Listen'))
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__ ,
                __NAMESPACE__ . '\Frontend::frontendList')
        );
    }

    public static function registerModule()
    {
        // TODO: Implement registerModule() method.
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('Reporting', 'CheckList', null, null, Consumer::useService()->getConsumerBySession()),
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