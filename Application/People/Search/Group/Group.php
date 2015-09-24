<?php
namespace SPHERE\Application\People\Search\Group;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Group
 *
 * @package SPHERE\Application\People\Search\Group
 */
class Group implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addApplicationNavigation(new Link(
            new Link\Route(__NAMESPACE__), new Link\Name('Suche'), new Link\Icon(new Question())
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendSearch'
        ));
    }

    /**
     * @return \SPHERE\Application\People\Group\Service
     */
    public static function useService()
    {

        return \SPHERE\Application\People\Group\Group::useService();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }

}
