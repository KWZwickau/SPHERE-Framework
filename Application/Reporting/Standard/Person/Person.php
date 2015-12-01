<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 *
 * @package SPHERE\Application\Reporting\Standard\Person
 */
class Person implements IModuleInterface
{

    public static function registerModule()
    {
//        Main::getDisplay()->addApplicationNavigation(
//            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Personen (allgemein)'))
//        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/ClassList'), new Link\Name('Klassenlisten'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/ExtendedClassList'), new Link\Name('Klassenlisten Erweitert'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/BirthdayClassList'), new Link\Name('Klassenlisten Geburtstag'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/MedicalInsuranceClassList'), new Link\Name('Klassenlisten Krankenkasse'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/GroupList'), new Link\Name('Personengruppenlisten'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendPerson'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/ClassList', __NAMESPACE__.'\Frontend::frontendClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/ExtendedClassList', __NAMESPACE__.'\Frontend::frontendExtendedClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/BirthdayClassList', __NAMESPACE__.'\Frontend::frontendBirthdayClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/MedicalInsuranceClassList', __NAMESPACE__.'\Frontend::frontendMedicalInsuranceClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/GroupList', __NAMESPACE__.'\Frontend::frontendGroupList'
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