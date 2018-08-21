<?php

namespace SPHERE\Application\Reporting\Individual;


use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\System\Database\Link\Identifier;

class Individual implements IApplicationInterface, IModuleInterface
{
    public static function registerApplication()
    {
        self::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Flexible Auswertung'))
        );

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __NAMESPACE__.'\Frontend::frontendDashboard'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Group', __NAMESPACE__.'\Frontend::frontendGroup'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Student', __NAMESPACE__.'\Frontend::frontendStudent'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Prospect', __NAMESPACE__.'\Frontend::frontendProspect'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Custody', __NAMESPACE__.'\Frontend::frontendCustody'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Teacher', __NAMESPACE__.'\Frontend::frontendTeacher'
        ));
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Club', __NAMESPACE__.'\Frontend::frontendClub'
        ));
    }

    public static function registerModule()
    {
        // TODO: Implement registerModule() method.
    }

    /**
     * @return Service
     */
    public static function useService()
    {
        return new Service(
            new Identifier('Setting', 'Consumer', null, null, Consumer::useService()->getConsumerBySession()),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        return new Frontend();
    }

}
