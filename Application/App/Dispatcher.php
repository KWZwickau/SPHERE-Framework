<?php

namespace SPHERE\Application\App;

use Exception;
use MOC\V\Component\Router\Component\IBridgeInterface;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\System\Extension\Extension;
use Throwable;

class Dispatcher extends Extension
{
    private static ?IBridgeInterface $Router = null;
    private static array $PublicRoutes = [];

    public function __construct(?IBridgeInterface $Router)
    {
        if (null !== $Router) {
            self::$Router = $Router;
        }
    }

    public static function registerRoute(RouteParameter $Route): void
    {
        try {
            if (Access::useService()->hasAuthorization($Route->getPath())) {
                if (in_array($Route->getPath(), self::$Router->getRouteList())) {
                    throw new Exception(__CLASS__ . ' > Route already available! (' . $Route->getPath() . ')');
                } else {
                    if (!preg_match('!^/?Api/!is', $Route->getPath())) {
                        self::$Router->addRoute($Route);
                    } else {
                        if (Access::useService()->existsRightByName('/' . $Route->getPath())) {
                            self::$Router->addRoute($Route);
                        }
                    }
                }
            }
            if (!Access::useService()->existsRightByName('/' . $Route->getPath())) {
                if (!in_array($Route->getPath(), self::$PublicRoutes)) {
                    self::$PublicRoutes[] = '/' . $Route->getPath();
                }
            }
        } catch (Exception $Exception) {
            throw new Exception($Exception->getMessage());
        }
    }

    public static function createRoute(string $Path, string $Controller): RouteParameter
    {
        // Map Controller Class to FQN
        if (!str_contains($Controller, 'SPHERE')) {
            $Controller = '\\' . $Path . '\\' . $Controller;
        }
        // Map Controller to Syntax
        $Controller = str_replace(array('/', '//', '\\', '\\\\'), '\\', $Controller);

        // Map Route to FileSystem
        $Path = str_replace(array('/', '//', '\\', '\\\\'), '/', $Path);
        $Path = trim(str_replace('SPHERE/Application', '', $Path), '/');

        return new RouteParameter($Path, $Controller);
    }

    public static function fetchRoute(string $Path): ?string
    {
        $Path = trim($Path, '/');
        if (in_array($Path, self::$Router->getRouteList(), true)) {
            try {
                return self::$Router->getRoute($Path);
            } catch (Throwable) {
                return null;
            }
        }
        return null;
    }

    public static function getPublicRoutes(): array
    {
        return self::$PublicRoutes;
    }
}
