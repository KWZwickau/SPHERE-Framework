<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 09.09.2016
 * Time: 10:31
 */

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
            __NAMESPACE__.'/StudentCard/Create', __CLASS__.'::createStudentCardPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/AccidentReport/Create', __CLASS__.'::createAccidentReportPdf'
        ));

//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__.'/StudentCard/Download', __NAMESPACE__.'\Repository\StudentCardTwig::downloadStudentCard')
//        );
    }

    /**
     * @param null $PersonId
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createEnrollmentDocumentPdf($PersonId = null)
    {

        return Creator::createPdf($PersonId, __NAMESPACE__ . '\Repository\EnrollmentDocument');
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
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createAccidentReportPdf($PersonId = null)
    {

        return Creator::createPdf($PersonId, __NAMESPACE__.'\Repository\AccidentReport');
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