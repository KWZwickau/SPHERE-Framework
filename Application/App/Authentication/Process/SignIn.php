<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Authentication\Process\Service\Entity\TblStep;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response201;
use SPHERE\Application\App\Response\Code\Response307;
use SPHERE\Application\App\Response\Code\Response400;
use SPHERE\Application\App\Response\Code\Response401;
use SPHERE\Application\App\Response\Code\Response403;
use SPHERE\Application\App\Response\Code\Response405;
use SPHERE\Application\App\Response\Code\Response422;
use SPHERE\Application\App\Response\Code\Response500;
use SPHERE\Application\App\Response\Code\Response501;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

/**
 *
 */
class SignIn implements ModuleInterface
{
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
        ?string $credentialIdentifier = null,
        ?string $deviceIdentifier = null,
        ?string $processToken = null,
        ?string $authenticationToken = null,
    ): ResponseInterface {
        // -----
        // Validate request input
        // -----
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return new Response405($_SERVER['REQUEST_METHOD']);
        }
        // -----
        // Validate user input
        // -----
        // Test availability (structure)
        if (
            null === $deviceIdentifier
            || null === $credentialIdentifier
        ) {
            return new Response400([
                'url' => '/app/authentication/process/sign-in#' . __LINE__,
                'method' => Request::METHOD_POST,
                'provide' => [
                    'deviceIdentifier' => [
                        'type' => 'string',
                        'sensitive' => true
                    ]
                ],
                'prompt' => [
                    'credentialIdentifier' => [
                        'label' => 'Benutzername',
                        'type' => 'string'
                    ]
                ]
            ]);
        }
        // Test compatibility (content)
        if (
            empty($deviceIdentifier)
            || empty($credentialIdentifier)
        ) {
            return new Response422([
                'url' => '/app/authentication/process/sign-in#' . __LINE__,
                'method' => Request::METHOD_POST,
                'provide' => [
                    'deviceIdentifier' => [
                        'type' => 'string',
                        'sensitive' => true
                    ]
                ],
                'prompt' => [
                    'credentialIdentifier' => [
                        'label' => 'Benutzername',
                        'type' => 'string'
                    ]
                ]
            ]);
        }
        // Find Account
        $tblAccount = Account::useService()->getAccountByUsername($credentialIdentifier);
        if (!$tblAccount) {
            return new Response403('Credentials not valid');
        }

        // -----
        // (A) Find/validate current Authentication-Token
        // - Validate Authentication
        // - Skip to (B) if invalid
        // - Create or update accessToken
        // -----
        if (!empty($authenticationToken)) {
            $tblToken = Authentication::useService()->getTokenByAuthentication($authenticationToken);
            if (null === $tblToken) {
                return new Response401('Token not valid');
            }
            if ($tblToken->getTblDevice()?->getDeviceIdentifier() !== $deviceIdentifier) {
                return new Response401('Device not valid');
            }
            if ($tblToken->getServiceTblAccount()?->getUsername() !== $credentialIdentifier) {
                return new Response401('Account not valid');
            }
            $tblToken = Authentication::useService()->createAccessToken($tblToken);
            return new Response201(['accessToken' => $tblToken->getAccessToken()]);
        }

        // -----
        // (B) Find/validate current MFA-Target
        // - Validate Process (and processToken)
        // - Prevent partly solved MFA
        // - Reset Steps to unsolved (if Process is invalid)
        // -----
        // Validate process token
        if (empty($processToken)) {
            // Generate new process token
            $processToken = Authentication::produceProcessToken();
            // Find/Create device and bind process token with device
            $tblDevice = Authentication::useService()->getDeviceWithIdentifierAndToken(
                $deviceIdentifier, $processToken
            );
            if (!$tblDevice) {
                return new Response403('Credentials not valid');
            }
            // New process startet or timed out? -> Reset sign-in process
            Authentication::useService()->resetAllSteps($tblAccount, $tblDevice);
            return new Response307(
                '/app/authentication/process/sign-in?processToken=' . $processToken . '#' . __LINE__
            );
        }
        // Find device
        $tblDevice = Authentication::useService()->getDeviceByIdentifier($deviceIdentifier);
        if (!$tblDevice) {
            return new Response403('Credentials not valid');
        }
        // Validate process
        if (
            null === $tblDevice->getProcessToken()
            || null === $tblDevice->getProcessTimeout()
            || $tblDevice->getProcessToken() !== $processToken
            || $tblDevice->getProcessTimeout() < time()
        ) {
            // New process startet or timed out? -> Reset sign-in process
            Authentication::useService()->resetAllSteps($tblAccount, $tblDevice);
            // Try again
            return new Response307('/app/authentication/process/sign-in#' . __LINE__);
        }

        // -----
        // Re-/Create or remove current MFA steps (if necessary)
        // -----
        Authentication::useService()->createAllSteps($tblAccount, $tblDevice);
        // -----
        // Find current challenge
        // -----
        /** @var TblStep[] $tblSteps */
        $tblSteps = Authentication::useService()->getAllStepsByAccountAndDevice($tblAccount, $tblDevice);
        foreach ($tblSteps as $tblStep) {
            if (!$tblStep->getIsSolved()) {
                try {
                    $factorName = $tblStep->getTblProcess()->getTblFactor()?->getName();
                } catch (Throwable $exception) {
                    return new Response500($exception->getMessage(), $exception->getTrace());
                }
                // TODO: Move switch and information into db to TblFactor
                switch ($factorName) {
                    case 'Credentials':
                        return new Response401([
                            'url' => '/app/authentication/factor/credentials?processToken=' . $processToken . '#' . __LINE__,
                            'method' => Request::METHOD_POST,
                            'prompt' => [
                                'credentialIdentifier' => [
                                    'label' => 'Benutzername',
                                    'type' => 'string'
                                ],
                                'credentialPassword' => [
                                    'label' => 'Passwort',
                                    'type' => 'string',
                                    'sensitive' => true
                                ],
                            ]
                        ]);
                    case 'Yubikey':
                        return new Response401([
                            'url' => '/app/authentication/factor/yubikey?processToken=' . $processToken . '#' . __LINE__,
                            'method' => Request::METHOD_POST,
                            'prompt' => [
                                'credentialToken' => [
                                    'label' => 'YubiKey',
                                    'type' => 'string',
                                    'sensitive' => true
                                ],
                            ]
                        ]);
                    default:
                        return new Response501('Factor not implemented', $factorName);
                }
            }
        }

        // -----
        // (C) All Steps are solved :-)
        // - Create or update authenticationToken
        // -----
        $tblToken = Authentication::useService()->createAuthenticationToken($tblAccount, $tblDevice);
        if (null === $tblToken) {
            return new Response401('Token not valid');
        }
        if ($tblToken->getTblDevice()?->getDeviceIdentifier() !== $deviceIdentifier) {
            return new Response401('Device not valid');
        }
        if ($tblToken->getServiceTblAccount()?->getUsername() !== $credentialIdentifier) {
            return new Response401('Account not valid');
        }
        return new Response201(['authenticationToken' => $tblToken->getAccessToken()]);
    }

    public static function useService(): Service
    {
        return new Service(new Identifier('Platform', 'App', 'Authentication'),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }
}
