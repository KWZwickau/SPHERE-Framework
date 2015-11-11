<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 *
 * @package SPHERE\Application\Reporting\Custom\Chemnitz\Person
 */
class Person implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/ClassList'), new Link\Name('Klassenlisten'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/ExtendedClassList'), new Link\Name('Klassenliste Erweitert'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/BirthdayClassList'), new Link\Name('Klassenliste Geburtstag'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/MedicalInsuranceClassList'), new Link\Name('Klassenliste Krankenkasse'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__.'/EmployeeList'), new Link\Name('Mitarbeiterliste'))
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
            __NAMESPACE__.'/EmployeeList', __NAMESPACE__.'\Frontend::frontendEmployeeList'
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