<?php

namespace SPHERE\Application\App\Authentication;

use SPHERE\Application\App\ApplicationInterface;
use SPHERE\Application\App\Authentication\Factor\Credentials;
use SPHERE\Application\App\Authentication\Factor\Token;
use SPHERE\Application\App\Authentication\Factor\YubiKey;
use SPHERE\Application\App\Authentication\Process\Refresh;
use SPHERE\Application\App\Authentication\Process\Service;
use SPHERE\Application\App\Authentication\Process\SignIn;
use SPHERE\Application\App\Authentication\Process\SignOut;
use SPHERE\System\Database\Link\Identifier;

/**
 *
 */
class Authentication implements ApplicationInterface
{
    // 60 * 60 * 24 * 120 => 120 Days
    public const AUTHENTICATION_TOKEN_TIMEOUT = 60 * 60 * 24 * 120;
    // 60 * 60 / 2 => 1/2 Hour
    public const ACCESS_TOKEN_TIMEOUT = 60 * 60 / 2;
    // 60 * 60 / 4 => 1/4 Hour
    public const PROCESS_TOKEN_TIMEOUT = 60 * 60 / 4;

    public static function registerApplication(): void
    {
        SignIn::registerModule();
        SignOut::registerModule();

        Credentials::registerModule();
        Token::registerModule();
        YubiKey::registerModule();
    }

    public static function useService(): Service
    {
        return new Service(new Identifier('Platform', 'App', 'Authentication'),
            __DIR__ . '/Process/Service/Entity', __NAMESPACE__ . '\Process\Service\Entity'
        );
    }

    public static function produceProcessToken(): string
    {
        return hash('sha1', uniqid(__METHOD__, true));
    }
    public static function produceAccessToken(): string
    {
        return hash('sha256', uniqid(__METHOD__, true));
    }
    public static function produceAuthenticationToken(): string
    {
        return hash('sha512', uniqid(__METHOD__, true));
    }
}
