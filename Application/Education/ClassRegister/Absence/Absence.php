<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 08.07.2016
 * Time: 09:05
 */

namespace SPHERE\Application\Education\ClassRegister\Absence;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 * @deprecated
 *
 * Class Absence
 *
 * @package SPHERE\Application\Education\ClassRegister\Absence
 */
class Absence implements IModuleInterface
{
    public static function registerModule()
    {
        /**
         * Route
         */
//        Main::getDispatcher()->registerRoute(
//            Main::getDispatcher()->createRoute('SPHERE\Application\Education\Absence', __NAMESPACE__ . '\Frontend::frontendAbsenceOverview')
//        );

//        Main::getDispatcher()->registerRoute(
//            Main::getDispatcher()->createRoute('SPHERE\Application\Education\ClassRegister\Digital\AbsenceMonth',
//                __NAMESPACE__ . '\Frontend::frontendAbsenceMonth')
//        );
//        Main::getDispatcher()->registerRoute(
//            Main::getDispatcher()->createRoute('SPHERE\Application\Education\ClassRegister\Digital\AbsenceStudent',
//                __NAMESPACE__ . '\Frontend::frontendAbsenceStudent')
//        );
    }

    /**
     * @deprecated
     *
     * @return Service
     */
    public static function useService()
    {
        return new Service(new Identifier('Education', 'ClassRegister', null, null,
            Consumer::useService()->getConsumerBySession()),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }

    /**
     * @deprecated
     *
     * @return Frontend
     */
    public static function useFrontend()
    {
        return new Frontend();
    }
}