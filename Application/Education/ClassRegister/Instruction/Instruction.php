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
        /**
         * Route
         */
        Main::getDisplay()->addModuleNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\ClassRegister\Digital\InstructionSetting'), new Link\Name('Einstellungen Belehrungen'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            'SPHERE\Application\Education\ClassRegister\Digital\InstructionSetting',
            __NAMESPACE__ . '\Frontend::frontendInstructionSetting'
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