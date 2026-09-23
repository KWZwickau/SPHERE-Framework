<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response201;
use SPHERE\Application\App\Response\Code\Response400;
use SPHERE\Application\App\Response\Code\Response401;
use SPHERE\Application\App\Response\Code\Response409;
use SPHERE\Application\App\Response\Code\Response422;
use SPHERE\Application\App\Response\RequestMethod;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumerLogin;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

/**
 *
 */
class Refresh extends Extension implements ModuleInterface
{
    /**
     * @throws AppException
     */
    public static function registerModule(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/refresh', __CLASS__ . '::handleRequest');
        $dispatcher::registerRoute($route, true);
    }

    public static function handleRequest(
        ?string $deviceIdentifier = null,
        ?string $authenticationToken = null,
        ?string $appVersion = null,
    ): ResponseInterface {
        // -----
        // Validate request input
        // -----
        if (!RequestMethod::wasPostMethod()) {
            return RequestMethod::wasWrong();
        }

        // read from header
//        $headerArray = self::getRequest()->getHeaderArray();
//        $deviceIdentifier = $headerArray['x-device-key'][0] ?? null;
//        $appVersion = $headerArray['x-app-version'][0] ?? null;
//        return new Response201($deviceIdentifier);

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

        // Rewire to sign in if token is not valid or not matching
        $tblDevice = Authentication::useService()->getDeviceByAuthentication($authenticationToken);
        if (!$tblDevice) {
            return new Response401('Invalid authentication token');
        }
        // find consumer on account
        $tblAccount = $tblDevice->getServiceTblAccount();
        if (!$tblAccount) {
            return new Response401('Invalid credentials');
        }
        $tblConsumer = $tblAccount->getServiceTblConsumer();
        if (!$tblConsumer) {
            return new Response401('Invalid credentials');
        }
        if (!Consumer::useService()->getConsumerLoginByConsumerAndSystem(
            $tblConsumer, TblConsumerLogin::VALUE_SYSTEM_SSW_APP
        )) {
            return new Response401('Consumer is disabled');
        }

        if ($tblDevice->getDeviceIdentifier() !== $deviceIdentifier) {
            return new Response409('Wrong device identifier');
        }
        // Device disabled by user?
        if (false === $tblDevice->getIsActive()) {
            return new Response401('Device is disabled');
        }
        // Await device activation by user
        if (null === $tblDevice->getIsActive()) {
            return new Response409('Activation needed');
        }

        // Timeout Session
        $accessToken = $tblDevice->getAccessToken();
        if ($accessToken) {
            Account::useService()->destroySession(null, $accessToken);
        }

        // -----
        // All steps are solved
        // -----

        // notice AppVersion
        Authentication::useService()->modifyAppVersion($tblDevice, $appVersion);

        // Refresh authentication token?
        /** @noinspection PhpArrayIndexImmediatelyRewrittenInspection */
        $response = [
            'authenticationToken' => null,
            'accessToken' => null
        ];

        if ((
                // Start at 0 ((issued at + token timeout) - token timeout)
                ($tblDevice->getAuthenticationTimeout() - Authentication::AUTHENTICATION_TOKEN_TIMEOUT)
                // + (0 + issued at + refresh timeout)
                + Authentication::AUTHENTICATION_TOKEN_REFRESH
            ) <= time() // refresh after refresh timeout counting from issued at
        ) {
            Authentication::useService()->modifyAuthenticationToken(
                $tblDevice, Authentication::produceAuthenticationToken(), Authentication::AUTHENTICATION_TOKEN_TIMEOUT
            );
            $response['authenticationToken'] = $tblDevice->getAuthenticationToken();
        }

        Authentication::useService()->modifyAccessToken(
            $tblDevice, Authentication::produceAccessToken(), Authentication::ACCESS_TOKEN_TIMEOUT
        );
        $response['accessToken'] = $tblDevice->getAccessToken();

        return new Response201($response);
    }

    public static function useService(): Service
    {
        return Authentication::useService();
    }
}
