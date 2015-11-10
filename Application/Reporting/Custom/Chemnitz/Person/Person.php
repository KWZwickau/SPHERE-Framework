<?php
namespace SPHERE\Application\Reporting\Custom\Chemnitz\Person;

use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
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
            new Link(new Link\Route(__NAMESPACE__ . '/ClassList'), new Link\Name('Klassenlisten'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/StaffList'), new Link\Name('Mitarbeiterliste'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/SchoolFeeList'), new Link\Name('Schulgeldliste'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/MedicList'), new Link\Name('Arztliste'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/InterestedPersonList'), new Link\Name('Interessenten'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/ParentTeacherConferenceList'), new Link\Name('Elternabende'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '/ClubMemberList'), new Link\Name('Vereinsmitglieder'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__ . '\Frontend::frontendPerson'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/ClassList', __NAMESPACE__ . '\Frontend::frontendClassList'
        )
            ->setParameterDefault('DivisionId', null)
            ->setParameterDefault('Select', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StaffList', __NAMESPACE__ . '\Frontend::frontendStaffList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/SchoolFeeList', __NAMESPACE__ . '\Frontend::frontendSchoolFeeList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/MedicList', __NAMESPACE__ . '\Frontend::frontendMedicList'
        )
            ->setParameterDefault('DivisionId', null)
            ->setParameterDefault('Select', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/InterestedPersonList', __NAMESPACE__ . '\Frontend::frontendInterestedPersonList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/ParentTeacherConferenceList',
            __NAMESPACE__ . '\Frontend::frontendParentTeacherConferenceList'
        )
            ->setParameterDefault('DivisionId', null)
            ->setParameterDefault('Select', null)
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/ClubMemberList', __NAMESPACE__ . '\Frontend::frontendClubMemberList'
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
