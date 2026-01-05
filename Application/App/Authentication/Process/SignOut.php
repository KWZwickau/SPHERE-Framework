<?php

namespace SPHERE\Application\App\Authentication\Process;


use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Authentication\SignIn\RequestMethod;
use SPHERE\Application\App\Response\Code\Response501;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Common\Main;

/**
 *
 */
class SignOut implements ModuleInterface
{

    /**
     * @throws AppException
     */
    public static function registerModule(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/sign-out', __CLASS__ . '::handleRequest');
        $dispatcher::registerRoute($route, true);
    }

    public static function handleRequest(): ResponseInterface
    {
        // -----
        // Validate request input
        // -----
        if (!RequestMethod::wasPostMethod()) {
            return RequestMethod::wasWrong();
        }

        // TODO: Start Logout-Process
        // Remove SSW-PHP-Session
        // Remove App-Account-Tokens
        // Remove App-Account-Process

        return new Response501(null);
    }

    public static function useService(): Service
    {
        return Authentication::useService();
    }
}
