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
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__ . '/PrintClassList'), new Link\Name('Druckbare Klassenlisten')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__ . '/Export'), new Link\Name('SchulAPP')));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Export', __NAMESPACE__ . '\Frontend::frontendExport'
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