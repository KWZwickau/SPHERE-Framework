<?php

namespace SPHERE\Application\App\Authentication\Factor;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Authentication\Authentication;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response200;
use SPHERE\Application\App\Response\Code\Response307;
use SPHERE\Application\App\Response\Code\Response400;
use SPHERE\Application\App\Response\Code\Response401;
use SPHERE\Application\App\Response\Code\Response405;
use SPHERE\Application\App\Response\Code\Response422;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\Request;

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
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return new Response405($_SERVER['REQUEST_METHOD']);
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
            return new Response400([
                'url' => '/app/authentication/process/sign-in?processToken='.$processToken.'#' . __LINE__,
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
            || empty($processToken)
            || empty($credentialIdentifier)
        ) {
            return new Response422([
                'url' => '/app/authentication/process/sign-in?processToken='.$processToken.'#' . __LINE__,
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
        Authentication::produceProcessToken();
        Authentication::useService()->getDeviceWithIdentifierAndToken();

        return new Response307('/app/authentication/process/sign-in');
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }
}
