<?php

namespace SPHERE\Application\Api\Document\Custom\Radebeul;


use SPHERE\Application\Api\Document\Creator;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class Radebeul extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/StudentCard/Create', __CLASS__.'::createStudentCardPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/StudentCard/CreateMulti', __CLASS__.'::createStudentCardMultiPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/StudentList/Create', __CLASS__.'::createStudentListPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/AuthorizedToCollect/Create', __CLASS__.'::createAuthorizedToCollectPdf'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/AuthorizedToCollect/CreateMulti', __CLASS__.'::createAuthorizedToCollectMultiPdf'
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

    /**
     * @param null $PersonId
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createStudentCardPdf($PersonId = null)
    {

        return Creator::createPdf($PersonId, __NAMESPACE__.'\Repository\StudentCard');
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Stage|string
     */
    public static function createStudentCardMultiPdf($DivisionCourseId = null)
    {
        return Creator::createMultiPdf($DivisionCourseId, __NAMESPACE__.'\Repository\StudentCard');
    }

    /**
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createStudentListPdf()
    {

        return Creator::createPdf(null, __NAMESPACE__.'\Repository\StudentList');
    }

    /**
     * @param null $PersonId
     *
     * @return \SPHERE\Common\Window\Stage|string
     */
    public static function createAuthorizedToCollectPdf($PersonId = null)
    {

        return Creator::createPdf($PersonId, __NAMESPACE__.'\Repository\AuthorizedToCollect');
    }

    /**
     * @param $DivisionCourseId
     *
     * @return Stage|string
     */
    public static function createAuthorizedToCollectMultiPdf($DivisionCourseId = null)
    {
        return Creator::createMultiPdf($DivisionCourseId, __NAMESPACE__.'\Repository\AuthorizedToCollect');
    }
}