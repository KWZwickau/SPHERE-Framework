<?php
namespace SPHERE\Application;

use MOC\V\Component\Router\Component\IBridgeInterface;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;

/**
 * Class Dispatcher
 *
 * @package SPHERE\Application
 */
class Dispatcher
{

    /** @var IBridgeInterface|null $Router */
    private static $Router = null;

    /**
     * @param IBridgeInterface|null $Router
     */
    function __construct( IBridgeInterface $Router = null )
    {

        if (null !== $Router) {
            self::$Router = $Router;
        }
    }

    /**
     * @param $Path
     * @param $Controller
     *
     * @return RouteParameter
     */
    public static function createRoute( $Path, $Controller )
    {

        $Path = str_replace( array( '/', '//', '\\', '\\\\' ), '/', $Path );
        $Path = trim( str_replace( 'SPHERE/Application', '', $Path ), '/' );
        return new RouteParameter( $Path, $Controller );
    }

    /**
     * @param RouteParameter $Route
     *
     * @throws \Exception
     */
    public static function registerRoute( RouteParameter $Route )
    {

        // TODO: Register -IF- Access granted

        if (in_array( $Route->getPath(), self::$Router->getRouteList() )) {
            throw new \Exception( __CLASS__.' > Route already available! ('.$Route->getPath().')' );
        } else {
            self::$Router->addRoute( $Route );
        }
    }

    /**
     * @param $Path
     *
     * @return string
     * @throws \Exception
     */
    public static function fetchRoute( $Path )
    {

        $Path = trim( $Path, '/' );
        if (in_array( $Path, self::$Router->getRouteList() )) {
            return self::$Router->getRoute( $Path );
        } else {
            throw new \Exception( __CLASS__.' > Route not available! ('.$Path.')' );
        }
    }
}
