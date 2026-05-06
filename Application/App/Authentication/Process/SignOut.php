<?php

namespace SPHERE\Application\App\Authentication\Process;


use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response204;
use SPHERE\Application\App\Response\Code\Response400;
use SPHERE\Application\App\Response\Code\Response401;
use SPHERE\Application\App\Response\Code\Response403;
use SPHERE\Application\App\Response\Code\Response422;
use SPHERE\Application\App\Response\Code\Response500;
use SPHERE\Application\App\Response\RequestMethod;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
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

    public static function handleRequest(
        ?string $deviceIdentifier = null,
        ?string $authenticationToken = null,
    ): ResponseInterface {
        // -----
        // Validate request input
        // -----
        if (!RequestMethod::wasPostMethod()) {
            return RequestMethod::wasWrong();
        }
        // -----
        // Validate user input
        // -----
        // Test availability (structure)
        if (
            null === $deviceIdentifier
            || null === $authenticationToken
        ) {
            return new Response400('Missing mandatory parameters');
        }
        // Test compatibility (content)
        if (
            empty(trim($deviceIdentifier))
            || empty(trim($authenticationToken))
        ) {
            return new Response422('Missing mandatory parameters');
        }

        $tblDevice = Authentication::useService()->getDeviceByAuthentication($authenticationToken);
        if (!$tblDevice) {
            return new Response401('Invalid credentials');
        }
        if ($tblDevice->getDeviceIdentifier() !== $deviceIdentifier) {
            return new Response403('Invalid credentials');
        }

        // Remove Session
        $tblAccount = $tblDevice->getServiceTblAccount();
        if (!$tblAccount) {
            return new Response500('Unable to sign out account');
        }

        // Timeout Session
        $accessToken = $tblDevice->getAccessToken();
        if ($accessToken) {
            Account::useService()->destroySession(null, $accessToken);
        }

        // Timeout Token
        Authentication::useService()->modifyAuthenticationToken($tblDevice, $tblDevice->getAuthenticationToken(), 0);
        Authentication::useService()->modifyAccessToken($tblDevice, $tblDevice->getAccessToken(), 0);

        return new Response204();
    }

    public static function useService(): Service
    {
        return Authentication::useService();
    }
}
