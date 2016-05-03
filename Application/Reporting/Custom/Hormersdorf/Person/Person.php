<?php

namespace SPHERE\Application\Reporting\Custom\Hormersdorf\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 * @package SPHERE\Application\Reporting\Custom\Hormersdorf\Person
 */
class Person extends AbstractModule implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/ClassList'), new Link\Name('Klassenlisten'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/StaffList'), new Link\Name('Mitarbeiterliste (Geburtstage)'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendPerson'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/ClassList', __NAMESPACE__ . '\Frontend::frontendClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StaffList', __NAMESPACE__ . '\Frontend::frontendStaffList'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service();
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
