<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class Instruction implements IModuleInterface
{
    public static function registerModule()
    {
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\ClassRegister\Digital\Instruction\Setting'), new Link\Name('Einstellung Belehrung'))
        );
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\ClassRegister\Digital\Instruction\Reporting'), new Link\Name('Auswertung Belehrung'))
        );

        /**
         * Route
         */
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE\Application\Education\ClassRegister\Digital\Instruction\Setting',
            __NAMESPACE__ . '\Frontend::frontendInstructionSetting'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE\Application\Education\ClassRegister\Digital\Instruction',
            __NAMESPACE__ . '\Frontend::frontendInstruction'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE\Application\Education\ClassRegister\Digital\Instruction\Reporting',
            __NAMESPACE__ . '\Frontend::frontendInstructionReporting'
        ));
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Education', 'Application', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
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