<?php
namespace SPHERE\Application\Setting\ItsLearning;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Common\Frontend\Icon\Repository\Publicly;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class ItsLearning
 * @package SPHERE\Application\Setting\ItsLearning
 */
class ItsLearning implements IApplicationInterface, IModuleInterface
{
    public static function registerApplication()
    {

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Itslearning'), new Link\Icon(new Publicly()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, '/Frontend::frontendDownload'
        ));


//        Main::getDispatcher()->registerWidget('Untis', array(__CLASS__, 'widgetLectureship'), 2, 2);
    }

    public static function registerModule()
    {
//        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
//            __NAMESPACE__, '/Frontend::frontendDownload'
//        ));
    }

    public static function useService()
    {
        return new Service();
    }

    public static function useFrontend()
    {
        return new Frontend();
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {
        $Stage = new Stage('itslearning', 'Datentransfer');

        return $Stage;
    }


}