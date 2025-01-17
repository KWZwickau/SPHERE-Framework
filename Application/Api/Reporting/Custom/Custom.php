<?php
namespace SPHERE\Application\Api\Reporting\Custom;

use SPHERE\Application\Api\Reporting\Custom\Gersdorf\Common;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
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
        $tblConsumer = Consumer::useService()->getConsumerBySession();
        if ($tblConsumer && $tblConsumer->getType() == TblConsumer::TYPE_SACHSEN) {

            $consumerAcronym = $tblConsumer->getAcronym();

            /*
             * Chemnitz
             */
            if ($consumerAcronym === 'ESZC' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Chemnitz/Common/ClassList/Download', __NAMESPACE__ . '\Chemnitz\Common::downloadClassList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Chemnitz/Common/PrintClassList/Download',
                    __NAMESPACE__ . '\Chemnitz\Common::downloadPrintClassList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Chemnitz/Common/StaffList/Download', __NAMESPACE__ . '\Chemnitz\Common::downloadStaffList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Chemnitz/Common/SchoolFeeList/Download',
                    __NAMESPACE__ . '\Chemnitz\Common::downloadSchoolFeeList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Chemnitz/Common/MedicList/Download', __NAMESPACE__ . '\Chemnitz\Common::downloadMedicList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Chemnitz/Common/InterestedPersonList/Download',
                    __NAMESPACE__ . '\Chemnitz\Common::downloadInterestedPersonList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Chemnitz/Common/ParentTeacherConferenceList/Download',
                    __NAMESPACE__ . '\Chemnitz\Common::downloadParentTeacherConferenceList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Chemnitz/Common/ClubMemberList/Download',
                    __NAMESPACE__ . '\Chemnitz\Common::downloadClubMemberList'
                ));
            }

            /*
            * Hormersdorf
            */
            if ($consumerAcronym === 'FESH' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Hormersdorf/Person/ClassList/Download',
                    __NAMESPACE__ . '\Hormersdorf\Person::downloadClassList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Hormersdorf/Person/StaffList/Download',
                    __NAMESPACE__ . '\Hormersdorf\Person::downloadStaffList'
                ));
            }

            /*
             * Herrnhut
             */
            if ($consumerAcronym === 'EZSH' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Herrnhut/Common/ProfileList/Download',
                    __NAMESPACE__ . '\Herrnhut\Common::downloadProfileList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Herrnhut/Common/SignList/Download',
                    __NAMESPACE__ . '\Herrnhut\Common::downloadSignList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Herrnhut/Common/LanguageList/Download',
                    __NAMESPACE__ . '\Herrnhut\Common::downloadLanguageList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Herrnhut/Common/ClassList/Download',
                    __NAMESPACE__ . '\Herrnhut\Common::downloadClassList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Herrnhut/Common/ExtendedClassList/Download',
                    __NAMESPACE__ . '\Herrnhut\Common::downloadExtendedClassList'
                ));
            }

            /*
             * Coswig
             */
            if ($consumerAcronym === 'EVSC' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Coswig/Common/ClassList/Download',
                    __NAMESPACE__ . '\Coswig\Common::downloadClassList'
                ));
            }

            /*
             * Schneeberg
             */
            if ($consumerAcronym === 'ESS' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Schneeberg/Person/ClassList/Download',
                    __NAMESPACE__ . '\Schneeberg\Person::downloadClassList'
                ));
            }

            /*
            * Radebeul
            */
            if ($consumerAcronym === 'EVSR' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Radebeul/Person/ParentTeacherConferenceList/Download',
                    __NAMESPACE__ . '\Radebeul\Person::downloadParentTeacherConferenceList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Radebeul/Person/DenominationList/Download',
                    __NAMESPACE__ . '\Radebeul\Person::downloadDenominationList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Radebeul/Person/PhoneList/Download',
                    __NAMESPACE__ . '\Radebeul\Person::downloadPhoneList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Radebeul/Person/KindergartenList/Download',
                    __NAMESPACE__ . '\Radebeul\Person::downloadKindergartenList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Radebeul/Person/RegularSchoolList/Download',
                    __NAMESPACE__ . '\Radebeul\Person::downloadRegularSchoolList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Radebeul/Person/DiseaseList/Download',
                    __NAMESPACE__ . '\Radebeul\Person::downloadDiseaseList'
                ));

                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Radebeul/Person/Nursery/Download',
                    __NAMESPACE__ . '\Radebeul\Person::downloadNursery'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Radebeul/Person/NurseryList/Download',
                    __NAMESPACE__ . '\Radebeul\Person::downloadNurseryList'
                ));
            }

            // Muldental
            if ($consumerAcronym === 'EVAMTL' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Muldental/Common/ClassList/Download',
                    __NAMESPACE__ . '\Muldental\Common::downloadClassList'
                ));
            }

            // Bad Düben
            if ($consumerAcronym === 'ESBD' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/BadDueben/Common/ClassList/Download',
                    __NAMESPACE__ . '\BadDueben\Common::downloadClassList'
                ));
            }

            /*
             * Annaberg
             */
            if ($consumerAcronym === 'EGE' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Annaberg/Common/PrintClassList/Download',
                    __NAMESPACE__ . '\Annaberg\Common::downloadPrintClassList'
                ));
            }

            /*
             * Gersdorf
             */
            if ($consumerAcronym === 'EVOSG' || $consumerAcronym === 'REF') {
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Gersdorf/Common/ClassList/Download',
                    __NAMESPACE__ . '\Gersdorf\Common::downloadClassList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Gersdorf/Common/SignList/Download',
                    __NAMESPACE__ . '\Gersdorf\Common::downloadSignList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Gersdorf/Common/ElectiveClassList/Download',
                    __NAMESPACE__ . '\Gersdorf\Common::downloadElectiveClassList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Gersdorf/Common/ClassPhoneList/Download',
                    __NAMESPACE__ . '\Gersdorf\Common::downloadClassPhoneList'
                ));
                Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
                    __NAMESPACE__ . '/Gersdorf/Common/TeacherList/Download',
                    __NAMESPACE__ . '\Gersdorf\Common::downloadTeacherList'
                ));
            }
        }

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/IndividualClassRegisterDownload', __CLASS__ . '::downloadCustomReporting'
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

    /**
     * @param null $DivisionCourseId
     * @param null $Type
     *
     * @return bool|string
     */
    public function downloadCustomReporting($DivisionCourseId = null, $Type = null)
    {
        switch ($Type) {
            case 'downloadClassList': return (new Common())->downloadClassList($DivisionCourseId);
            case 'downloadSignList': return (new Common())->downloadSignList($DivisionCourseId);
            case 'downloadElectiveClassList': return (new Common())->downloadElectiveClassList($DivisionCourseId);
            case 'downloadClassPhoneList': return (new Common())->downloadClassPhoneList($DivisionCourseId);
            default: return 'Keine entsprechende Auswertung gefunden';
        }
    }
}
