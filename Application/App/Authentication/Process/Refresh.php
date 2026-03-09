<?php

namespace SPHERE\Application\App\Authentication\Process;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Authentication\SignIn\EmptySignInFields;
use SPHERE\Application\App\Response\Authentication\SignIn\EmptyOtpFields;
use SPHERE\Application\App\Response\Authentication\SignIn\EmptyRefreshFields;
use SPHERE\Application\App\Response\Authentication\SignIn\EmptySignOutFields;
use SPHERE\Application\App\Response\Authentication\SignIn\MissingSignInFields;
use SPHERE\Application\App\Response\Authentication\SignIn\MissingOtpFields;
use SPHERE\Application\App\Response\Authentication\SignIn\MissingRefreshFields;
use SPHERE\Application\App\Response\Authentication\SignIn\MissingSignOutFields;
use SPHERE\Application\App\Response\Authentication\SignIn\RequestMethod;
use SPHERE\Application\App\Response\Authentication\SignIn\WrongSignInFields;
use SPHERE\Application\App\Response\Authentication\SignIn\WrongOtpFields;
use SPHERE\Application\App\Response\Authentication\SignIn\WrongRefreshFields;
use SPHERE\Application\App\Response\Authentication\SignIn\WrongSignOutFields;
use SPHERE\Application\App\Response\Code\Response201;
use SPHERE\Application\App\Response\Code\Response500;
use SPHERE\Application\App\Response\Code\Response501;
use SPHERE\Application\App\Response\Code\Response502;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use Throwable;

/**
 *
 */
class Refresh implements ModuleInterface
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
            return new MissingRefreshFields();
        }
        // Test compatibility (content)
        if (
            empty(trim($deviceIdentifier))
            || empty(trim($authenticationToken))
        ) {
            return new EmptyRefreshFields();
        }

        // Rewire to sign in if token is not valid or not matching
        $tblDevice = Authentication::useService()->getDeviceByAuthentication($authenticationToken);
        if (!$tblDevice) {
            return new WrongSignInFields();
        }
        if ($tblDevice->getDeviceIdentifier() !== $deviceIdentifier) {
            return new WrongSignInFields();
        }

        // -----
        // All steps are solved
        // - Give token :-)
        // -----
        Authentication::useService()->modifyAccessToken(
            $tblDevice, Authentication::produceAccessToken(), Authentication::ACCESS_TOKEN_TIMEOUT
        );
        return new Response201([
            'accessToken' => $tblDevice->getAccessToken()
        ]);
    }

    public static function useService(): Service
    {
        return Authentication::useService();
    }
}
