<?php

namespace SPHERE\Application\RestApi;

use Exception;
use MOC\V\Component\Router\Component\IBridgeInterface;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\System\Extension\Extension;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Dispatcher  extends Extension
{
    private static ?IBridgeInterface $Router = null;
    private static array $PublicRoutes = array();

    /**
     * @param IBridgeInterface|null $Router
     */
    public function __construct(IBridgeInterface $Router = null)
    {

        if (null !== $Router) {
            self::$Router = $Router;
        }
    }

    /**
     * @param RouteParameter $Route
     */
    public static function registerRoute(RouteParameter $Route): void
    {
        try {
            if (Access::useService()->hasAuthorization($Route->getPath())) {
                if (in_array($Route->getPath(), self::$Router->getRouteList())) {
                    throw new Exception(__CLASS__.' > Route already available! ('.$Route->getPath().')');
                } else {
                    if (preg_match('!^/?RestApi/Public/!i', $Route->getPath())) {
                        self::$Router->addRoute($Route);
                    } else {
                        if (Access::useService()->existsRightByName('/'.$Route->getPath())) {
                            self::$Router->addRoute($Route);
                        }
                    }
                }
            }
            if (!Access::useService()->existsRightByName('/'.$Route->getPath())) {
                if (!in_array($Route->getPath(), self::$PublicRoutes)) {
                    self::$PublicRoutes[] = '/' . $Route->getPath();
                }
            }
        } catch (Exception $Exception) {
            throw new Exception($Exception->getMessage());
        }
    }

    /**
     * @param $Path
     * @param $Controller
     *
     * @return RouteParameter
     */
    public static function createRoute($Path, $Controller): RouteParameter
    {

        // Map Controller Class to FQN
        if (false === strpos($Controller, 'SPHERE')) {
            $Controller = '\\'.$Path.'\\'.$Controller;
        }
        // Map Controller to Syntax
        $Controller = str_replace(array('/', '//', '\\', '\\\\'), '\\', $Controller);

        // Map Route to FileSystem
        $Path = str_replace(array('/', '//', '\\', '\\\\'), '/', $Path);
        $Path = trim(str_replace('SPHERE/Application', '', $Path), '/');

        return new RouteParameter($Path, $Controller);
    }

    /**
     * @param $Path
     *
     * @return JsonResponse
     */
    public static function fetchRoute($Path): JsonResponse
    {

        $Path = trim($Path, '/');
        if (in_array($Path, self::$Router->getRouteList())) {
            return (new JsonResponse([self::$Router->getRoute($Path)],Response::HTTP_OK));
        } else {
            return new JsonResponse('Route not found!', Response::HTTP_NOT_FOUND);
        }
    }

    /**
     * @return array
     */
    public static function getPublicRoutes(): array
    {
        return self::$PublicRoutes;
    }
}