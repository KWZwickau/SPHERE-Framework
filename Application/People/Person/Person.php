<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Person
 *
 * @package SPHERE\Application\People\Person
 */
class Person extends Extension implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/Create'), new Link\Name('Person anlegen'),
                new Link\Icon(new \SPHERE\Common\Frontend\Icon\Repository\Person())
            )
        );
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'FrontendReadOnly::frontendPersonReadOnly'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Create', __NAMESPACE__. '\FrontendReadOnly::frontendPersonCreate'
        ));

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__.'/Destroy', __NAMESPACE__.'\Frontend::frontendDestroyPerson')
        );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier('People', 'Person', null, null, Consumer::useService()->getConsumerBySession()),
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
