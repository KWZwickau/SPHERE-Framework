<?php
namespace SPHERE\Application\Transfer\Import\Chemnitz;

use MOC\V\Component\Document\Component\Bridge\Repository\PhpExcel;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Chemnitz
 *
 * @package SPHERE\Application\Transfer\Import\Chemnitz
 */
class Chemnitz implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student', __NAMESPACE__ . '\Frontend::frontendStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Student/Import', __NAMESPACE__ . '\Frontend::frontendStudentImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/Person', __NAMESPACE__ . '\Frontend::frontendPersonImport'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__ . '/InterestedPerson', __NAMESPACE__ . '\Frontend::frontendInterestedPersonImport'
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
