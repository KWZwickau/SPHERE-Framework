<?php
namespace SPHERE\Application;

use MOC\V\Component\Router\Component\IBridgeInterface;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;

/**
 * Class Dispatcher
 *
 * @package SPHERE\Application
 */
class Dispatcher
{

    /** @var IBridgeInterface|null $Router */
    private static $Router = null;

    private static $Widget = array();

    /**
     * @param IBridgeInterface|null $Router
     */
    function __construct(IBridgeInterface $Router = null)
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
    public static function createRoute($Path, $Controller)
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
     * @param RouteParameter $Route
     *
     * @throws \Exception
     */
    public static function registerRoute(RouteParameter $Route)
    {

        if (Access::useService()->hasAuthorization($Route->getPath())) {
            if (in_array($Route->getPath(), self::$Router->getRouteList())) {
                throw new \Exception(__CLASS__.' > Route already available! ('.$Route->getPath().')');
            } else {
                self::$Router->addRoute($Route);
            }
        }
    }

    /**
     * @param $Path
     *
     * @return string
     * @throws \Exception
     */
    public static function fetchRoute($Path)
    {

        $Path = trim($Path, '/');
        if (in_array($Path, self::$Router->getRouteList())) {
            return self::$Router->getRoute($Path);
        } else {
            return self::$Router->getRoute('Platform/Assistance/Error/Authorization');
        }
    }

    /**
     * @param string $Location
     * @param string $Content
     * @param int    $Width
     * @param int    $Height
     */
    public static function registerWidget($Location, $Content, $Width = 2, $Height = 2)
    {

        self::$Widget[$Location][] = array($Content, $Width, $Height);
    }

    /**
     * @param string $Location
     *
     * @return string
     */
    public static function fetchDashboard($Location)
    {

        $Dashboard = '<div class="Location-'.$Location.' gridster"><ul style="list-style: none; display: none;">';
        if (isset( self::$Widget[$Location] )) {
            $Row = 1;
            $Column = 1;
            foreach ((array)self::$Widget[$Location] as $Index => $Widget) {
                $Dashboard .= '<li id="Widget-'.$Index.'" '
                    .'data-row="'.$Row.'" '
                    .'data-col="'.$Column.'" '
                    .'data-sizex="'.$Widget[1].'" '
                    .'data-sizey="'.$Widget[2].'" '
                    .'class="Widget">'.$Widget[0].'</li>';
                if ($Column >= 8) {
                    $Column = 1;
                    $Row++;
                }
                $Column++;
            }
        }
        return $Dashboard.'</div><script>Client.Use( "ModGrid", function() { jQuery( "div.Location-'.$Location.'.gridster ul" ).ModGrid({ storage: "Widget-'.$Location.'" }); } );</script>';
    }
}
