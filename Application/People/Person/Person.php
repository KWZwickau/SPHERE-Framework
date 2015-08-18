<?php
namespace SPHERE\Application\People\Person;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Person
 *
 * @package SPHERE\Application\People\Person
 */
class Person implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Person anlegen' ),
                new Link\Icon( new \SPHERE\Common\Frontend\Icon\Repository\Person() )
            )
        );
    }

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute( Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendPerson'
        ) );
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(
            new Identifier( 'People', 'Person', null, null, Consumer::useService()->getConsumerBySession() ),
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
