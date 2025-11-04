<?php

namespace SPHERE\Application\App;

use MOC\V\Component\Router\Component\IBridgeInterface;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;
use SPHERE\Application\App\Response\Code\Response404;
use SPHERE\Application\App\Response\Code\Response500;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\DispatcherInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\System\Extension\Extension;
use Throwable;

/**
 *
 */
class Dispatcher extends Extension implements DispatcherInterface
{
    private static array $publicRoutes = [
        '/app/authentication/process/sign-in',
        '/app/authentication/process/sign-out',
        '/app/authentication/factor/credentials',
        '/app/authentication/factor/yubikey',
        '/app/authentication/factor/token',
    ];
    private static ?IBridgeInterface $router = null;

    public function __construct(?IBridgeInterface $router)
    {
        if (null !== $router) {
            self::$router = $router;
        }

        set_error_handler(static function ($code, $content, $file, $line) {
            (new Response500($content, ['line' => $line, 'file' => $file, 'code' => $code]))->send();
            exit();
        });
    }

    /**
     * @throws AppException
     */
    public static function registerRoute(RouteParameter $route): void
    {
        $path = '/' . strtolower($route->getPath());

        if (in_array($path, self::$publicRoutes, true)
            || Access::useService()->hasAuthorization($path)
        ) {
            // Exists already?
            if (in_array($path, self::$router->getRouteList(), true)) {
                throw new AppException(__CLASS__ . ' > Route already available! (' . $path . ')');
            }
            // Add if restricted (additional check, in case "hasAuthorization" messes up)
            if (in_array($path, self::$publicRoutes, true)
                || Access::useService()->existsRightByName($path)
            ) {
                self::$router->addRoute($route);
            } else {
                throw new AppException(__CLASS__ . ' > Route has no authorization! (' . $path . ')');
            }
        }
    }

    public static function createRoute(string $path, string $controller): RouteParameter
    {
        // Map Controller Class to FQN
        if (!str_contains($controller, 'SPHERE')) {
            $controller = '\\' . $path . '\\' . $controller;
        }
        // Map Controller to Syntax
        $controller = str_replace(['/', '//', '\\', '\\\\'], '\\', $controller);

        // Map Route to FileSystem
        $path = str_replace(['/', '//', '\\', '\\\\'], '/', $path);
        $path = trim(str_replace('SPHERE/Application', '', $path), '/');

        return new RouteParameter($path, $controller);
    }

    public static function fetchRoute(string $path): ResponseInterface
    {
        $path = trim($path, '/');
        if (in_array($path, self::$router->getRouteList(), true)) {
            try {
                /** @var ResponseInterface $response */
                $response = self::$router->getRoute($path);
                return $response;
            } catch (Throwable $throwable) {
                return new Response500($throwable->getMessage(), $throwable->getTrace());
            }
        }
        return new Response404('Route not found', $path);
    }
}
