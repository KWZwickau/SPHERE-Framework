<?php

namespace SPHERE\Application\App\Authentication\Factor;

use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response501;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Common\Main;

/**
 *
 */
class YubiKey implements ModuleInterface
{
    public static function registerModule(): void
    {
        Main::getDispatcher()::registerRoute(
            Main::getDispatcher()::createRoute(
                __NAMESPACE__ . '/YubiKey', __CLASS__ . '::handleRequest'
            )
                ->setParameterDefault('credentialKey', null)
        );
    }

    public static function handleRequest(): ResponseInterface
    {
        // TODO: Execute MFA-Step > YubiKey
        return new Response501(null);
    }
}
