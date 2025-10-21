<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response200;
use SPHERE\Application\App\Response\Code\Response400;
use SPHERE\Application\App\Response\Code\Response401;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;

/**
 *
 */
class SignIn implements ModuleInterface
{
    public static function registerModule(): void
    {
        Main::getDispatcher()::registerRoute(Main::getDispatcher()::createRoute(
            __NAMESPACE__ . '/sign-in', __CLASS__ . '::handleRequest'
        ));
    }

    public static function handleRequest(
        ?string $credentialIdentifier = null,
    ): ResponseInterface
    {
        // TODO: Start MFA-Login-Process
        if (empty($credentialIdentifier)) {
            return new Response400('Credentials not provided', [
                'credentialIdentifier' => $credentialIdentifier,
            ]);
        }

        $tblAccount = Account::useService()->getAccountByUsername($credentialIdentifier);

        if (!$tblAccount) {
            return new Response401('Credentials not valid', [
                'credentialIdentifier' => $credentialIdentifier
            ]);
        }

        // TODO:

        return new Response200(':o)');

    }
}
