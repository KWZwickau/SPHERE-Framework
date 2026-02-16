<?php

namespace SPHERE\Application\App\Authentication\Factor;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Authentication\Process\Service;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Authentication\SignIn\EmptyBasicFields;
use SPHERE\Application\App\Response\Authentication\SignIn\EmptyCredentialFields;
use SPHERE\Application\App\Response\Authentication\SignIn\MissingBasicFields;
use SPHERE\Application\App\Response\Authentication\SignIn\RetryProcess;
use SPHERE\Application\App\Response\Authentication\SignIn\RequestMethod;
use SPHERE\Application\App\Response\Authentication\SignIn\WrongCredentialFields;
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
    public const FACTOR_NAME = 'Credentials';

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
        ?string $deviceIdentifier = null,
        ?string $processToken = null,
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

        // TODO: Execute 1. MFA-Step > Username & Password
        if (empty($credentialPassword)) {
            return new EmptyCredentialFields($processToken);
        }

        $tblAccount = Account::useService()->getAccountByCredential($credentialIdentifier, $credentialPassword);

        if (!$tblAccount) {
            return new WrongCredentialFields($processToken);
        }

        // TODO:
        Authentication::produceProcessToken();
        Authentication::useService()->getDeviceWithIdentifierAndToken();

        return new RetryProcess();
    }

    public static function useService(): Service
    {
        return Authentication::useService();
    }
}
