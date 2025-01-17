<?php

namespace SPHERE\Application\Education\Absence;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

class Absence implements IApplicationInterface, IModuleInterface
{
    public static function registerApplication()
    {
        self::registerModule();
    }

    public static function registerModule()
    {
        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('SPHERE\Application\Education\Absence', __NAMESPACE__ . '\Frontend::frontendAbsenceOverview')
        );

        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('SPHERE\Application\Education\ClassRegister\Digital\AbsenceMonth',
                __NAMESPACE__ . '\Frontend::frontendAbsenceMonth')
        );
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute('SPHERE\Application\Education\ClassRegister\Digital\AbsenceStudent',
                __NAMESPACE__ . '\Frontend::frontendAbsenceStudent')
        );
    }

    /**
     * @return Service
     */
    public static function useService(): Service
    {
        return new Service(new Identifier('Education', 'Application', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}