<?php
namespace SPHERE\Application\Api\Document\Standard;

use SPHERE\Application\Api\Document\Creator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Standard
 *
 * @package SPHERE\Application\Api\Document\Standard
 */
class Standard extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/EnrollmentDocument/Create', __CLASS__ . '::createEnrollmentDocumentPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StudentCard/Create', __CLASS__ . '::createStudentCardPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StudentCardNew/Create', __CLASS__ . '::createStudentCardNewPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StudentCard/CreateMulti', __CLASS__ . '::createStudentCardMultiPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/StudentCardNew/CreateMulti', __CLASS__ . '::createStudentCardMultiNewPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/KamenzReport/Create', 'SPHERE\Application\Api\Document\Creator::createKamenzPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/GradebookOverview/Create', __CLASS__.'::createGradebookOverviewPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/MultiGradebookOverview/Create', __CLASS__.'::createMultiGradebookOverviewPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/StudentTransfer/Create', __CLASS__.'::createStudentTransferPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/SignOutCertificate/Create', __CLASS__.'::createSignOutCertificatePdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/AccidentReport/Create', __CLASS__.'::createAccidentReportPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/StaffAccidentReport/Create', __CLASS__.'::createStaffAccidentReportPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/PasswordChange/Create', __CLASS__.'::createPasswordChangePdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/MultiPassword/Create', __CLASS__.'::createMultiPasswordPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Gradebook/Create', 'SPHERE\Application\Api\Document\Creator::createGradebookPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/MultiGradebook/Create', 'SPHERE\Application\Api\Document\Creator::createMultiGradebookPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/BillingDocument/Create', 'SPHERE\Application\Api\Document\Creator::createBillingDocumentPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/BillingDocumentWarning/Create', 'SPHERE\Application\Api\Document\Creator::createBillingDocumentWarningPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Account/Create', 'SPHERE\Application\Api\Document\Creator::createAccountPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Manual/Create/Pdf', 'SPHERE\Application\Api\Document\Creator::createManualPdf'
        ));
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/Manual/Create/xlsx', 'SPHERE\Application\Api\Document\Creator::createManualExcel'
//        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/EnrollmentDocument/CreateMulti', __CLASS__ . '::createEnrollmentDocumentMultiPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/ClassRegister/Create', __CLASS__ . '::createClassRegisterPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/CourseContent/Create', __CLASS__ . '::createCourseContentPdf'
        ));
    }

    /**
     * @param array $Data
     *
     * @return Stage|string
     */
    public static function createEnrollmentDocumentPdf($Data = array())
    {

        return Creator::createDataPdf($Data, 'EnrollmentDocument', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param int|null $PersonId
     * @param bool     $Redirect
     *
     * @return Stage|string
     */
    public static function createStudentCardPdf(int $PersonId = null, bool $Redirect = true)
    {
        return Creator::createStudentCardPdf($PersonId, $Redirect);
    }

    /**
     * @param int|null $PersonId
     * @param bool     $Redirect
     *
     * @return Stage|string
     */
    public static function createStudentCardNewPdf(int $PersonId = null, bool $Redirect = true)
    {
        return Creator::createStudentCardNewPdf($PersonId, $Redirect);
    }

    /**
     * @param null|int $DivisionId
     * @param null|int $List
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createStudentCardMultiPdf($DivisionId = null, $List = null, $Redirect = true)
    {
        return Creator::createMultiStudentCardPdf($DivisionId, $List, $Redirect);
    }

    /**
     * @param null|int $DivisionId
     * @param null|int $List
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createStudentCardMultiNewPdf($DivisionId = null, $List = null, $Redirect = true)
    {
        return Creator::createMultiStudentCardNewPdf($DivisionId, $List, $Redirect);
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @return Stage|string
     */
    public static function createGradebookOverviewPdf($PersonId = null, $DivisionId = null)
    {

        return Creator::createGradebookOverviewPdf($PersonId, $DivisionId,Creator::PAPERORIENTATION_LANDSCAPE);
    }

    /**
     * @param null $DivisionId
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createMultiGradebookOverviewPdf($DivisionId = null, $GroupId = null, $Redirect = true)
    {

        return Creator::createMultiGradebookOverviewPdf($DivisionId, $GroupId, Creator::PAPERORIENTATION_LANDSCAPE, $Redirect);
    }

    /**
     * @param array $Data
     *
     * @return Stage|string
     */
    public static function createStudentTransferPdf($Data = array())
    {
        return Creator::createDataPdf($Data, 'StudentTransfer', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param array $Data
     *
     * @return Stage|string
     */
    public static function createSignOutCertificatePdf($Data = array())
    {
        return Creator::createDataPdf($Data, 'SignOutCertificate', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param array $Data
     *
     * @return Stage|string
     */
    public static function createAccidentReportPdf($Data = array())
    {

        return Creator::createDataPdf($Data, 'AccidentReport', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param array $Data
     *
     * @return Stage|string
     */
    public static function createStaffAccidentReportPdf(array $Data = array())
    {

        return Creator::createDataPdf($Data, 'StaffAccidentReport', Creator::PAPERORIENTATION_PORTRAIT);
    }


    /**
     * @param array $Data
     * @param bool  $Redirect
     *
     * @return Stage|string
     */
    public static function createPasswordChangePdf($Data = array(), $Redirect = true)
    {

        $Post = array('Data' => $Data);
        $Post['Redirect'] = 0;
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
                '/Api/Document/Standard/PasswordChange/Create',
                $Post
            );
        }
        return Creator::createChangePasswordPdf($Data, Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param array $Data
     * @param bool  $Redirect
     *
     * @return Stage|string
     */
    public static function createMultiPasswordPdf($Data = array(), $Redirect = true)
    {

        $Post = array('Data' => $Data);
        $Post['Redirect'] = 0;
        if ($Redirect) {
            return \SPHERE\Application\Api\Education\Certificate\Generator\Creator::displayWaitingPage(
               '/Api/Document/Standard/MultiPassword/Create',
                $Post
            );
        }

        return Creator::createMultiPasswordPdf($Data, Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param null $AccountId
     * @param bool $Redirect
     *
     * @return Stage|string
     */
    public static function createAccountPdf($AccountId = null, $Redirect = true)
    {
        return Creator::createAccountPdf($AccountId, $Redirect);
    }

    /**
     * @param string $DivisionId
     * @param bool $Redirect
     *
     * @return string
     */
    public static function createEnrollmentDocumentMultiPdf(string $DivisionId, bool $Redirect = true): string
    {
        return Creator::createMultiEnrollmentDocumentPdf($DivisionId, $Redirect);
    }

    /**
     * @param null $DivisionId
     * @param null $GroupId
     * @param null $YearId
     * @param bool $Redirect
     *
     * @return string
     */
    public static function createClassRegisterPdf($DivisionId = null, $GroupId = null, $YearId = null, bool $Redirect = true): string
    {
        return Creator::createClassRegisterPdf($DivisionId, $GroupId, $YearId, $Redirect);
    }

    /**
     * @param null $DivisionId
     * @param null $SubjectId
     * @param null $SubjectGroupId
     * @param bool $Redirect
     *
     * @return string
     */
    public static function createCourseContentPdf($DivisionId = null, $SubjectId = null, $SubjectGroupId = null, bool $Redirect = true): string
    {
        return Creator::createCourseContentPdf($DivisionId, $SubjectId, $SubjectGroupId, $Redirect);
    }

    /**
     *
     */
    public static function useService()
    {

    }

    /**
     *
     */
    public static function useFrontend()
    {

    }
}