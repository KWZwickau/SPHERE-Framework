<?php
namespace SPHERE\Application;

use MOC\V\Component\Router\Component\IBridgeInterface;
use MOC\V\Component\Router\Component\Parameter\Repository\RouteParameter;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Debugger\Logger\ErrorLogger;
use SPHERE\System\Extension\Extension;

/**
 * Class Dispatcher
 *
 * @package SPHERE\Application
 */
class Dispatcher extends Extension
{

    /** @var IBridgeInterface|null $Router */
    private static $Router = null;

    private static $Widget = array();

    private static $PublicRoutes = array();

    /**
     * @param IBridgeInterface|null $Router
     */
    public function __construct(IBridgeInterface $Router = null)
    {

        if (null !== $Router) {
            self::$Router = $Router;

            // Roadmap
            try {
                $this->registerRoute($this->createRoute('Roadmap/Current',
                    'SPHERE\Application\Platform\Roadmap\Roadmap::frontendDashboard')
                );
            } catch (\Exception $Exception) {
                $this->getLogger(new ErrorLogger())->addLog('Unable to register Roadmap');
            }
        }
    }

    /**
     * @param RouteParameter $Route
     *
     * @throws \Exception
     */
    public static function registerRoute(RouteParameter $Route)
    {

        try {
            if (Access::useService()->hasAuthorization($Route->getPath())) {
                if (in_array($Route->getPath(), self::$Router->getRouteList())) {
                    throw new \Exception(__CLASS__.' > Route already available! ('.$Route->getPath().')');
                } else {
                    if (!preg_match('!^/?Api/!is', $Route->getPath())) {
                        self::$Router->addRoute($Route);
                    } else {
                        if (Access::useService()->existsRightByName('/'.$Route->getPath())) {
                            self::$Router->addRoute($Route);
                        } else {
                            $Route = Main::getDispatcher()->createRoute(
                                $Route->getPath(),
                                'SPHERE\Application\Platform\Assistance\Error\Frontend::frontendRoute'
                            );
                            self::$Router->addRoute($Route);
                        }
                    }
                }
            }
            if (!Access::useService()->existsRightByName('/'.$Route->getPath())) {
                if (!in_array($Route->getPath(), self::$PublicRoutes)) {
                    array_push(self::$PublicRoutes, '/'.$Route->getPath());
                }
            }
        } catch (\Exception $Exception) {
            Main::runSelfHeal($Exception);
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
     * @return array
     */
    public static function getPublicRoutes()
    {

        return self::$PublicRoutes;
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
            if (Account::useService()->getAccountBySession()) {
                return self::$Router->getRoute('Platform/Assistance/Error/Authorization');
            } else {
                $Stage = new Stage('Berechtigung', 'Prüfung der Anfrage');
                $Stage->setMessage('<strong>Problem:</strong> Die Anwendung darf die Anfrage nicht verarbeiten');
                $Stage->setContent(
                    '<h2><small>Mögliche Ursachen</small></h2>'
                    .new Danger('Sie sind nicht angemeldet')
                    . new Warning('Sie waren zu lang inaktiv und wurden automatisch vom System abgemeldet')
                    .'<h2><small>Mögliche Lösungen</small></h2>'
                    .new Success('Bitte melden Sie sich an der Plattform an')
                    . new Redirect('Platform/Gatekeeper/Authentication', 10)
                );
                return $Stage;
            }
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

                if (is_callable($Widget[0])) {
                    $Widget[0] = call_user_func($Widget[0]);
                }

                $Dashboard .= '<li id="Widget-'.$Location.'-'.$Index.'" '
                    .'data-row="'.$Row.'" '
                    .'data-col="'.$Column.'" '
                    .'data-sizex="'.$Widget[1].'" '
                    .'data-sizey="'.$Widget[2].'" '
                    .'class="Widget"><div class="Widget-Payload">'.$Widget[0].'</div></li>';
                if ($Column >= 8) {
                    $Column = 1;
                    $Row++;
                }
                $Column++;
            }
        }
        return $Dashboard.'</div>'
        .'<script>executeScript(function(){Client.Use( "ModGrid", function() { jQuery( "div.Location-'.$Location.'.gridster ul" ).ModGrid({ storage: "Widget-'.$Location.'" }); } );});</script>';
    }
}
