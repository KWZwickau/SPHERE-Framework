<?php
namespace SPHERE\Application\Api\Reporting\Standard;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Api\Reporting\Standard
 */
class Standard implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/ClassList/Download', __NAMESPACE__.'\Person\Person::downloadClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/ExtendedClassList/Download', __NAMESPACE__.'\Person\Person::downloadExtendedClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/BirthdayClassList/Download', __NAMESPACE__.'\Person\Person::downloadBirthdayClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/MedicalInsuranceClassList/Download', __NAMESPACE__.'\Person\Person::downloadMedicalInsuranceClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/GroupList/Download', __NAMESPACE__.'\Person\Person::downloadGroupList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Company/GroupList/Download', __NAMESPACE__.'\Company\Company::downloadGroupList'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

}
