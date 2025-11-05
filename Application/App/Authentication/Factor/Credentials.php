<?php

namespace SPHERE\Application\App\Authentication\Factor;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Dispatcher;
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
    /**
     * @throws AppException
     */
    public static function registerModule(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/credentials', __CLASS__ . '::handleRequest');
        $dispatcher::registerRoute($route, true);
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

    public static function useService()
    {
        // TODO: Implement useService() method.
    }
}
