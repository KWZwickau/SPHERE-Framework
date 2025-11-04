<?php

namespace SPHERE\Application\App\Authentication;

use SPHERE\Application\App\ApplicationInterface;
use SPHERE\Application\App\Authentication\Factor\Credentials;
use SPHERE\Application\App\Authentication\Factor\Token;
use SPHERE\Application\App\Authentication\Factor\YubiKey;
use SPHERE\Application\App\Authentication\Process\Service;
use SPHERE\Application\App\Authentication\Process\SignIn;
use SPHERE\Application\App\Authentication\Process\SignOut;
use SPHERE\System\Database\Link\Identifier;

/**
 *
 */
class Authentication implements ApplicationInterface
{
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
}
