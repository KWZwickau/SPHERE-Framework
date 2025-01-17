<?php
namespace SPHERE\Application\Reporting\Standard\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Reporting\AbstractModule;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Person
 *
 * @package SPHERE\Application\Reporting\Standard\Person
 */
class Person extends AbstractModule implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/ClassList'), new Link\Name('Klassenlisten')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/ExtendedClassList'), new Link\Name('Klassenlisten Erweitert')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/ElectiveClassList'), new Link\Name('Klassenlisten Wahlfächer')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/BirthdayClassList'), new Link\Name('Klassenlisten Geburtstag')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/MedicalInsuranceClassList'),
            new Link\Name('Klassenlisten Krankenkasse')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/InterestedPersonList'), new Link\Name('Interessenten')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/GroupList'), new Link\Name('Personengruppenlisten')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/MetaDataComparison'), new Link\Name('Stammdatenabfrage')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/Absence'), new Link\Name('Fehlzeiten')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/Club'), new Link\Name('Fördervereinsmitgliedschaft')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/StudentArchive'), new Link\Name('Ehemalige Schüler')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/StudentAgreement'), new Link\Name('Schüler Einverständniserklärung')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/PersonAgreement'),
            new Link\Name('Mitarbeiter Einverständniserklärung')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/ClassTeacher'), new Link\Name('Klassenlehrer')));
        Main::getDisplay()->addModuleNavigation(new Link(new Link\Route(__NAMESPACE__.'/Representative'), new Link\Name('Elternsprecher-Klassensprecher')));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__, __NAMESPACE__.'\Frontend::frontendPerson'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/ClassList', __NAMESPACE__.'\Frontend::frontendClassList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/ExtendedClassList',
            __NAMESPACE__.'\Frontend::frontendExtendedClassList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/BirthdayClassList',
            __NAMESPACE__.'\Frontend::frontendBirthdayClassList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/MedicalInsuranceClassList',
            __NAMESPACE__.'\Frontend::frontendMedicalInsuranceClassList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/GroupList', __NAMESPACE__.'\Frontend::frontendGroupList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/InterestedPersonList',
            __NAMESPACE__.'\Frontend::frontendInterestedPersonList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/ElectiveClassList',
            __NAMESPACE__.'\Frontend::frontendElectiveClassList'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/MetaDataComparison',
            __NAMESPACE__.'\Frontend::frontendMetaDataComparison'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/Absence', __NAMESPACE__.'\Frontend::frontendAbsence'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/Club', __NAMESPACE__.'\Frontend::frontendClub'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/StudentArchive',
            __NAMESPACE__.'\Frontend::frontendStudentArchive'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/StudentAgreement',
            __NAMESPACE__.'\Frontend::frontendStudentAgreement'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/PersonAgreement',
            __NAMESPACE__.'\Frontend::frontendAgreement'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/ClassTeacher',
            __NAMESPACE__.'\Frontend::frontendClassTeacher'));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(__NAMESPACE__.'/Representative',
            __NAMESPACE__.'\Frontend::frontendRepresentative'));
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
