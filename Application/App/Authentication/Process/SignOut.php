<?php

namespace SPHERE\Application\App\Authentication\Process;


use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response501;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 *
 */
class SignOut implements ModuleInterface
{

    public static function registerModule(): void
    {
        Main::getDispatcher()::registerRoute(Main::getDispatcher()::createRoute(
            __NAMESPACE__ . '/sign-out', __CLASS__ . '::handleRequest'
        ));
    }

    public static function handleRequest(): ResponseInterface
    {
        // TODO: Start Logout-Process
        // Remove SSW-PHP-Session
        // Remove App-Account-Tokens
        // Remove App-Account-Process

        return new Response501(null);
    }

    public static function useService(): Service
    {
        return new Service(new Identifier('Platform', 'App', 'Authentication'),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }
}
