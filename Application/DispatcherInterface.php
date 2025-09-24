<?php

namespace SPHERE\Application;


use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;

/**
 *
 */
interface DispatcherInterface
{
    public static function registerRoute(RouteParameter $route): void;
    public static function createRoute(string $path, string $controller): RouteParameter;
}
