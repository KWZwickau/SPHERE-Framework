<?php

namespace SPHERE\Application\App\Authentication\Factor;

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
class Credentials implements ModuleInterface
{
    public static function registerModule(): void
    {
        Main::getDispatcher()::registerRoute(
            Main::getDispatcher()::createRoute(
                __NAMESPACE__ . '/credentials', __CLASS__ . '::handleRequest'
            )
        );
    }

    public static function handleRequest(
        ?string $credentialIdentifier = null,
        ?string $credentialPassword = null
    ): ResponseInterface {

        // TODO: Execute 1. MFA-Step > Username & Password
        if (empty($credentialIdentifier) || empty($credentialPassword)) {
            return new Response400('Credentials not provided', [
                'credentialIdentifier' => $credentialIdentifier,
                'credentialPassword' => $credentialPassword
            ]);
        }

        $tblAccount = Account::useService()->getAccountByCredential($credentialIdentifier, $credentialPassword);

        if (!$tblAccount) {
            return new Response401('Credentials not valid', [
                'credentialIdentifier' => $credentialIdentifier,
                'credentialPassword' => $credentialPassword
            ]);
        }

        // TODO:

        return new Response200(':o)');
    }
}
