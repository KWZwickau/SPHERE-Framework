<?php
namespace SPHERE\Application\People\Group;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\Icon\Repository\Group as GroupIcon;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Group
 *
 * @package SPHERE\Application\People\Group
 */
class Group implements IApplicationInterface, IModuleInterface
{

    public static function registerApplication()
    {

        self::registerModule();

        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'Personengruppen' ),
                new Link\Icon( new GroupIcon() )
            )
        );
    }

    public static function registerModule()
    {

        // TODO: Implement registerModule() method.
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {

        return new Service(
            new Identifier( 'People', 'Group', null, null, Consumer::useService()->getConsumerBySession() ),
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
