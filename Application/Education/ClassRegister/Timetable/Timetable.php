<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Timetable
 *
 * @package SPHERE\Application\Education\ClassRegister\Timetable
 */
class Timetable extends Extension implements IModuleInterface
{
    public static function registerModule()
    {
//        Main::getDisplay()->addModuleNavigation(
//            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Stundenplan'),
//                new Link\Icon(new Calendar()))
//        );
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__, __CLASS__ . '::frontendDashboard'
//        ));
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(
            new Identifier('Education', 'Application', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Stundenplan', 'Dashboard');
        return $Stage;
    }
}
