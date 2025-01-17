<?php
namespace SPHERE\Application\Reporting\Custom\Gersdorf\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 *
 * @package SPHERE\Application\Reporting\Custom\Gersdorf\Person
 */
class Person extends AbstractModule implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/ClassList'), new Link\Name('Klassenliste')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/SignList'), new Link\Name('Unterschriften Liste')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/ElectiveList'), new Link\Name('Klassenliste Wahlfächer')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/ClassPhoneList'), new Link\Name('Telefonlisten')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/TeacherList'), new Link\Name('Mitarbeiterliste')));


        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/ClassList', __NAMESPACE__.'\Frontend::frontendClassList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/SignList', __NAMESPACE__.'\Frontend::frontendSignList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/ElectiveList', __NAMESPACE__.'\Frontend::frontendElectiveClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/ClassPhoneList', __NAMESPACE__.'\Frontend::frontendClassPhoneList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/TeacherList', __NAMESPACE__.'\Frontend::frontendTeacherList'));
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service();
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}