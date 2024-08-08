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
            __NAMESPACE__.'/Person/ExtendedClassList/Download',
            __NAMESPACE__.'\Person\Person::downloadExtendedClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/BirthdayClassList/Download',
            __NAMESPACE__.'\Person\Person::downloadBirthdayClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/MedicalInsuranceClassList/Download',
            __NAMESPACE__.'\Person\Person::downloadMedicalInsuranceClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/GroupList/Download', __NAMESPACE__.'\Person\Person::downloadGroupList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/InterestedPersonList/Download', __NAMESPACE__.'\Person\Person::downloadInterestedPersonList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/ElectiveClassList/Download',
            __NAMESPACE__.'\Person\Person::downloadElectiveClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/MetaDataComparison/Download', __NAMESPACE__.'\Person\Person::downloadMetaDataComparison'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Company/GroupList/Download', __NAMESPACE__.'\Company\Company::downloadGroupList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/MedicalRecordClassList/Download',
            __NAMESPACE__.'\Person\Person::downloadMedicalRecordClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/AgreementClassList/Download',
            __NAMESPACE__.'\Person\Person::downloadAgreementClassList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/AgreementStudentList/Download',
            __NAMESPACE__.'\Person\Person::downloadAgreementStudentList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/AgreementPersonList/Download',
            __NAMESPACE__.'\Person\Person::downloadAgreementPersonList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/AbsenceList/Download',
            __NAMESPACE__.'\Person\Person::downloadAbsenceList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/AbsenceBetweenList/Download',
            __NAMESPACE__.'\Person\Person::downloadAbsenceBetweenList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/ClubList/Download',
            __NAMESPACE__.'\Person\Person::downloadClubList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/StudentArchive/Download',
            __NAMESPACE__.'\Person\Person::downloadStudentArchive'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/ClassRegister/Absence/Download',
            __NAMESPACE__.'\Person\Person::downloadClassRegisterAbsence'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/Certificate/Diploma/SerialMail/Download',
            __NAMESPACE__.'\Person\Person::downloadDiplomaSerialMail'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/Certificate/Diploma/Statistic/Download',
            __NAMESPACE__.'\Person\Person::downloadDiplomaStatistic'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/Certificate/CourseGrades/Download',
            __NAMESPACE__.'\Person\Person::downloadCourseGrades'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/DivisionTeacherList/Download', __NAMESPACE__.'\Person\Person::downloadDivisionTeacherList'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/ClassRegister/AbsenceMonthly/Download',
            __NAMESPACE__.'\Person\Person::downloadClassRegisterAbsenceMonthly'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/ClassRegister/AbsenceStudent/Download',
            __NAMESPACE__.'\Person\Person::downloadClassRegisterAbsenceStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Person/RepresentativeList/Download', __NAMESPACE__.'\Person\Person::downloadRepresentativeList'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // Implement useFrontend() method.
    }

}
