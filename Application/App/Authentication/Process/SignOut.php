<?php

namespace SPHERE\Application\App\Authentication\Process;


use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Common\Main;

/**
 *
 */
class SignOut implements ModuleInterface
{

    public static function registerModule(): void
    {
        Main::getDispatcher()::registerRoute(Main::getDispatcher()::createRoute(
            __NAMESPACE__ . '/Sign-Out', __CLASS__ . '::handleRequest'
        ));
    }

    public static function handleRequest(): ResponseInterface
    {
        // TODO: Start Logout-Process
    }
}
