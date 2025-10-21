<?php

namespace SPHERE\Application\App\Authentication;

use SPHERE\Application\App\ApplicationInterface;
use SPHERE\Application\App\Authentication\Factor\Credentials;
use SPHERE\Application\App\Authentication\Factor\Token;
use SPHERE\Application\App\Authentication\Factor\YubiKey;
use SPHERE\Application\App\Authentication\Process\Service;
use SPHERE\Application\App\Authentication\Process\SignIn;
use SPHERE\Application\App\Authentication\Process\SignOut;
use SPHERE\Application\App\Response\Code\Response501;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

/**
 *
 */
class Authentication implements ApplicationInterface
{
    public static function registerApplication(): void
    {
        Main::getDispatcher()::registerRoute(Main::getDispatcher()::createRoute(
            __NAMESPACE__ . '/status', __CLASS__ . '::authenticationStatus'
        ));

        SignIn::registerModule();
        SignOut::registerModule();

        Credentials::registerModule();
        Token::registerModule();
        YubiKey::registerModule();
    }

    public static function useService(): Service
    {
        return new Service(new Identifier('Platform', 'App', 'Authentication'),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }

    /** @noinspection PhpUnused */
    public static function authenticationStatus(): ResponseInterface
    {
        // TODO: Execute MFA-Step > YubiKey
        return new Response501(null);
    }
}
