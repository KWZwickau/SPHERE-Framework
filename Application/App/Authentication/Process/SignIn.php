<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblDevice;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response201;
use SPHERE\Application\App\Response\Code\Response400;
use SPHERE\Application\App\Response\Code\Response401;
use SPHERE\Application\App\Response\Code\Response409;
use SPHERE\Application\App\Response\Code\Response422;
use SPHERE\Application\App\Response\Code\Response502;
use SPHERE\Application\App\Response\RequestMethod;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;

/**
 *
 */
class SignIn implements ModuleInterface
{
    // Identifications without activation
    public const SKIP_ACTIVATION = [
        'Credential',
        'UserCredential'
    ];

    /**
     * @throws AppException
     */
    public static function registerModule(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/sign-in', __CLASS__ . '::handleRequest');
        $dispatcher::registerRoute($route, true);
    }

    public static function handleRequest(
        ?string $deviceIdentifier = null,
        ?string $deviceName = null,
        ?string $credentialIdentifier = null,
        ?string $credentialPassword = null
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
            || null === $credentialIdentifier
            || null === $credentialPassword
        ) {
            return new Response400('Missing mandatory parameters');
        }
        // Test compatibility (content)
        if (
            empty(trim($deviceIdentifier))
            || empty(trim($credentialIdentifier))
            || empty(trim($credentialPassword))
        ) {
            return new Response422('Missing mandatory parameters');
        }

        if (
            null === $deviceName
            || empty(trim($deviceName))
        ) {
            $deviceName = 'New device';
        }

        // -----
        // Run Process
        // -----
        // Find Account
        $tblAccount = Account::useService()->getAccountByCredential($credentialIdentifier, $credentialPassword);
        if (!$tblAccount) {
            return new Response401('Invalid credentials');
        }
        // Find device or create new device
        $tblDevice = Authentication::useService()->getDeviceByIdentifier($tblAccount, $deviceIdentifier);
        if (!$tblDevice) {
            $tblDevice = Authentication::useService()->createDevice($tblAccount, $deviceIdentifier, $deviceName);
            if (!$tblDevice) {
                return new Response502('Device creation failed');
            }
        }

        // Device disabled by user?
        if ($tblDevice->getIsActive() === false) {
            return new Response401('Device is disabled');
        }

        // Determine if activation is necessary for this account
        $useActivation = false;
        $tblAuthentications = Account::useService()->getAuthenticationListByAccount($tblAccount);
        foreach ($tblAuthentications as $tblAuthentication) {
            // Account has identifications with MFA?
            if (!in_array($tblAuthentication->getTblIdentification()->getName(), self::SKIP_ACTIVATION)) {
                $useActivation = true;
                break;
            }
        }

        // Check if activation should be skipped
        if (!$useActivation) {
            // All tests passed, connect device and give tokens :-)
            $return = self::createTokens($tblDevice);
            Authentication::useService()->modifyIsActive($tblDevice, true);
            return $return;
        }

        // Await device activation by user
        if (!$tblDevice->getIsActive()) {
            return new Response409('Activation needed');
        }

        // All tests passed, connect device and give tokens :-)
        return self::createTokens($tblDevice);
    }

    public static function useService(): Service
    {
        return Authentication::useService();
    }

    private static function createTokens(TblDevice $tblDevice): ResponseInterface
    {
        Authentication::useService()->modifyAuthenticationToken(
            $tblDevice, Authentication::produceAuthenticationToken(), Authentication::AUTHENTICATION_TOKEN_TIMEOUT
        );
        Authentication::useService()->modifyAccessToken(
            $tblDevice, Authentication::produceAccessToken(), Authentication::ACCESS_TOKEN_TIMEOUT
        );
        return new Response201([
            'authenticationToken' => $tblDevice->getAuthenticationToken(),
            'accessToken' => $tblDevice->getAccessToken()
        ]);
    }
}
