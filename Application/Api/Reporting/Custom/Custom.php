<?php
namespace SPHERE\Application\Api\Reporting\Custom;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;

/**
 * Class Custom
 *
 * @package SPHERE\Application\Api\Reporting\Custom
 */
class Custom implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Chemnitz/Common/ClassList/Download', __NAMESPACE__.'\Chemnitz\Common::downloadClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Chemnitz/Common/StaffList/Download', __NAMESPACE__.'\Chemnitz\Common::downloadStaffList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Chemnitz/Common/SchoolFeeList/Download',
            __NAMESPACE__.'\Chemnitz\Common::downloadSchoolFeeList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Chemnitz/Common/MedicList/Download', __NAMESPACE__.'\Chemnitz\Common::downloadMedicList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Chemnitz/Common/InterestedPersonList/Download',
            __NAMESPACE__.'\Chemnitz\Common::downloadInterestedPersonList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Chemnitz/Common/ParentTeacherConferenceList/Download',
            __NAMESPACE__.'\Chemnitz\Common::downloadParentTeacherConferenceList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Chemnitz/Common/ClubMemberList/Download',
            __NAMESPACE__.'\Chemnitz\Common::downloadClubMemberList'
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
