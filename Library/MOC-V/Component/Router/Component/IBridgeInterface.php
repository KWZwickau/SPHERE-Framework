<?php
namespace MOC\V\Component\Router\Component;

use MOC\V\Component\Router\Component\Exception\ComponentException;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Component\Router\Component
 */
interface IBridgeInterface
{

    /**
     * @param RouteParameter $RouteOption
     *
     * @return IBridgeInterface
     */
    public function addRoute(RouteParameter $RouteOption);

    /**
     * @param null|string $Path
     *
     * @return string
     * @throws ComponentException
     */
    public function getRoute($Path = null);

    /**
     * @return array
     */
    public function getRouteList();
}
