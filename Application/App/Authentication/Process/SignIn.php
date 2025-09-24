<?php

namespace SPHERE\Application\App\Authentication\Process;


use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Common\Main;

/**
 *
 */
class SignIn implements ModuleInterface
{

    public static function registerModule(): void
    {
        Main::getDispatcher()::registerRoute(Main::getDispatcher()::createRoute(
            __NAMESPACE__ . '/Sign-In', __CLASS__ . '::handleRequest'
        ));
    }

    public static function handleRequest(): ResponseInterface
    {
        // TODO: Start MFA-Login-Process
    }
}
