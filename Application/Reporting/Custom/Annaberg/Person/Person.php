<?php
namespace SPHERE\Application\Reporting\Custom\Annaberg\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 *
 * @package SPHERE\Application\Reporting\Custom\Annaberg\Person
 */
class Person extends AbstractModule implements IModuleInterface
{
    /**
     * @return void
     */
    public static function registerModule(): void
    {
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__ . '/PrintClassList'), new Link\Name('Druckbare Klassenlisten')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__ . '/Export'), new Link\Name('SchulAPP')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__ . '/StudentExport'), new Link\Name('SchulApp - Schüler')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__ . '/StudentCustodyExport'), new Link\Name('SchulApp - Schüler und Sorgeberechtigte')));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/PrintClassList', __NAMESPACE__ . '\Frontend::frontendPrintClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Export', __NAMESPACE__ . '\Frontend::frontendExport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StudentExport', __NAMESPACE__ . '\Frontend::frontendExportStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StudentCustodyExport', __NAMESPACE__ . '\Frontend::frontendExportStudentCustody'
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
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}