<?php

namespace SPHERE\Application\App\Authentication\Factor;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Authentication\Process\Service;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Authentication\SignIn\EmptyBasicFields;
use SPHERE\Application\App\Response\Authentication\SignIn\MissingBasicFields;
use SPHERE\Application\App\Response\Authentication\SignIn\RequestMethod;
use SPHERE\Application\App\Response\Code\Response501;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Common\Main;

/**
 *
 */
class YubiKey implements ModuleInterface
{
    /**
     * @throws AppException
     */
    public static function registerModule(): void
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/yubikey', __CLASS__ . '::handleRequest');
        $dispatcher::registerRoute($route, true);
    }

    public static function handleRequest(
        ?string $deviceIdentifier = null,
        ?string $processToken = null,
        ?string $credentialIdentifier = null,

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
            || null === $processToken
            || null === $credentialIdentifier
        ) {
            return new MissingBasicFields($processToken);
        }
        // Test compatibility (content)
        if (
            empty($deviceIdentifier)
            || empty($processToken)
            || empty($credentialIdentifier)
        ) {
            return new EmptyBasicFields($processToken);
        }

        // TODO: Execute MFA-Step > YubiKey
        return new Response501(null);
    }

    public static function useService(): Service
    {
        return Authentication::useService();
    }
}
