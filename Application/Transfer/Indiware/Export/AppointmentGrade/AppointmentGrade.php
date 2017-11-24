<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Graduation
 * @package SPHERE\Application\Transfer\Export\Graduation
 */
class AppointmentGrade extends Extension implements IModuleInterface
{

    public static function registerModule()
    {
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendExport'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Process', __NAMESPACE__.'\Frontend::frontendAppointmentGradeUpload'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Prepare', __NAMESPACE__.'\Frontend::frontendAppointmentGradePrepare'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Failure', __NAMESPACE__.'\Frontend::frontendMissExport'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service(new Identifier('Setting', 'Consumer', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity',
            __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend()
    {

        return new Frontend();
    }
}
