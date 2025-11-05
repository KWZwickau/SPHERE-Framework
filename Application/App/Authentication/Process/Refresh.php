<?php

namespace SPHERE\Application\App\Authentication\Process;


use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response307;
use SPHERE\Application\App\Response\Code\Response400;
use SPHERE\Application\App\Response\Code\Response405;
use SPHERE\Application\App\Response\Code\Response422;
use SPHERE\Application\App\Response\Code\Response501;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use SPHERE\System\Database\Link\Identifier;

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
        ?string $deviceToken = null,
        ?string $authenticationToken = null,
    ): ResponseInterface
    {
        // ---
        // Validate request input
        // ---

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return new Response405($_SERVER['REQUEST_METHOD']);
        }

        // ---
        // Validate user input
        // ---

        // Test availability (structure)
        if (
            null === $deviceToken
            || null === $authenticationToken
        ) {
            return new Response400('Credentials not provided');
        }
        // Test compatibility (content)
        if (
            empty($deviceToken)
            || empty($authenticationToken)
        ) {
            return new Response422('Credentials not provided');
        }


        return new Response501(null);
    }

    public static function useService(): Service
    {
        return new Service(new Identifier('Platform', 'App', 'Authentication'),
            __DIR__ . '/Service/Entity', __NAMESPACE__ . '\Service\Entity'
        );
    }
}
