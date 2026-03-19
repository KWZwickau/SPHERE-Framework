<?php
namespace SPHERE\Application\Reporting\Custom\Kreuzgymnasium\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 *
 * @package SPHERE\Application\Reporting\Custom\Kreuzgymnasium\Person
 */
class Person extends AbstractModule implements IModuleInterface
{
    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/SignList'), new Link\Name('Unterschriften Liste')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/StudentCount'), new Link\Name('Schülerzahlen')));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/SignList', __NAMESPACE__.'\Frontend::frontendSignList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/StudentCount', __NAMESPACE__.'\Frontend::frontendStudentCount'));
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