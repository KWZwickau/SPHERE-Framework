<?php
namespace SPHERE\Application\Api\Document\Standard;

use SPHERE\Application\Api\Document\Creator;
use SPHERE\Application\Document\Generator\Generator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\People\Person\Person;
use SPHERE\Common\Main;
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
    }

    /**
     * @param array $Data
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createEnrollmentDocumentPdf($Data = array())
    {

        return Creator::createDataPdf($Data, 'EnrollmentDocument', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param null $PersonId
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createStudentCardPdf($PersonId = null)
    {

        if (($tblPerson = Person::useService()->getPersonById($PersonId))
            && ($tblSchoolTypeList = Generator::useService()->getSchoolTypeListForStudentCard($tblPerson))
        ) {

            return Creator::createMultiPdf($tblPerson, $tblSchoolTypeList);
        } else {
            return ('Keine Schülerkartei vorhanden');
        }
    }

    /**
     * @param null $PersonId
     * @param null $DivisionId
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createGradebookOverviewPdf($PersonId = null, $DivisionId = null)
    {

        return Creator::createGradebookOverviewPdf($PersonId, $DivisionId,Creator::PAPERORIENTATION_LANDSCAPE);
    }

    /**
     * @param null $DivisionId
     * @param bool $Redirect
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createMultiGradebookOverviewPdf($DivisionId = null, $Redirect = true)
    {

        return Creator::createMultiGradebookOverviewPdf($DivisionId, Creator::PAPERORIENTATION_LANDSCAPE, $Redirect);
    }

    /**
     * @param array $Data
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createStudentTransferPdf($Data = array())
    {
        return Creator::createDataPdf($Data, 'StudentTransfer', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param array $Data
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createSignOutCertificatePdf($Data = array())
    {
        return Creator::createDataPdf($Data, 'SignOutCertificate', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param array $Data
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createAccidentReportPdf($Data = array())
    {

        return Creator::createDataPdf($Data, 'AccidentReport', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param array $Data
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createPasswordChangePdf($Data = array())
    {

        return Creator::createDataPdf($Data, 'PasswordChange', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param array $Data
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createPasswordCreatePdf($Data = array())
    {

        return Creator::createDataPdf($Data, 'PasswordCreate', Creator::PAPERORIENTATION_PORTRAIT);
    }

    /**
     * @param array $Data
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createMultiPasswordPdf($Data = array())
    {

        return Creator::createDataPdf($Data, 'MultiPassword', Creator::PAPERORIENTATION_PORTRAIT);
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