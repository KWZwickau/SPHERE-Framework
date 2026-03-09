<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Authentication\SignIn\EmptySignInFields;
use SPHERE\Application\App\Response\Authentication\SignIn\EmptyOtpFields;
use SPHERE\Application\App\Response\Authentication\SignIn\MissingSignInFields;
use SPHERE\Application\App\Response\Authentication\SignIn\MissingOtpFields;
use SPHERE\Application\App\Response\Authentication\SignIn\RequestMethod;
use SPHERE\Application\App\Response\Authentication\SignIn\WrongSignInFields;
use SPHERE\Application\App\Response\Authentication\SignIn\WrongOtpFields;
use SPHERE\Application\App\Response\Code\Response201;
use SPHERE\Application\App\Response\Code\Response500;
use SPHERE\Application\App\Response\Code\Response502;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
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
        ?string $deviceIdentifier = null,
        ?string $deviceCode = null,
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
            return new MissingSignInFields();
        }
        // Test compatibility (content)
        if (
            empty(trim($deviceIdentifier))
            || empty(trim($credentialIdentifier))
            || empty(trim($credentialPassword))
        ) {
            return new EmptySignInFields();
        }

        // -----
        // Run Process
        // -----
        // Find Account
        $tblAccount = Account::useService()->getAccountByCredential($credentialIdentifier, $credentialPassword);
        if (!$tblAccount) {
            return new WrongSignInFields();
        }

        // Find device or create new device
        $tblDevice = Authentication::useService()->getDeviceByIdentifier($tblAccount, $deviceIdentifier);
        if (!$tblDevice) {
            $tblDevice = Authentication::useService()->createDevice($tblAccount, $deviceIdentifier);
            if (!$tblDevice) {
                return new Response502('Device creation failed');
            }
        }

        // Determine if OTP is necessary (No Authentication-Token or Timed-Out)
        if (!$tblDevice->getAuthenticationToken()) {

            $tblAuthentications = Account::useService()->getAuthenticationListByAccount($tblAccount);
            foreach ($tblAuthentications as $tblAuthentication) {
                // Those Identifications don't do MFA
                if (!in_array($tblAuthentication->getTblIdentification()->getName(), [
                    'Credential',
                    'UserCredential'
                ])) {

                    if (!$tblDevice->getOtpToken()) {
                        // Create OTP
                        try {
                            $otpToken = Authentication::produceOtpToken();
                            Authentication::useService()->modifyOtpToken(
                                $tblDevice, $otpToken, Authentication::OTP_TOKEN_TIMEOUT
                            );
                            return new MissingOtpFields($tblDevice->getOtpTimeout());
                        } catch (Throwable $exception) {
                            return new Response500('Code creation failed', $exception->getMessage());
                        }
                    }

                    // -----
                    // Validate user input
                    // -----
                    // Test availability (structure)
                    if (null === $deviceCode) {
                        return new MissingOtpFields($tblDevice->getOtpTimeout());
                    }
                    // Test compatibility (content)
                    if (empty($deviceCode)
                    ) {
                        return new EmptyOtpFields($tblDevice->getOtpTimeout());
                    }
                    // Test validity
                    if ($deviceCode !== $tblDevice->getOtpToken()) {
                        return new WrongOtpFields($tblDevice->getOtpTimeout());
                    }

                    // All tests passed, timeout token and connect device :-)
                    Authentication::useService()->modifyOtpToken($tblDevice, $tblDevice->getOtpToken(), 0);
                    break;
                }
            }
        }

        // -----
        // All steps are solved
        // - Give token :-)
        // -----
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

    public static function useService(): Service
    {
        return Authentication::useService();
    }
}
