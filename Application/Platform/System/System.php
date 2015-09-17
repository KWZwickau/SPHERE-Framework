<?php
namespace SPHERE\Application\Platform\System;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\Platform\System\Archive\Archive;
use SPHERE\Application\Platform\System\Cache\Cache;
use SPHERE\Application\Platform\System\Database\Database;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Platform\System\Test\Test;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class System
 *
 * @package SPHERE\Application\Platform\System
 */
class System implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Protocol::registerModule();
        Database::registerModule();
        Cache::registerModule();
        Archive::registerModule();
        Test::registerModule();
        /**
         * Register Navigation
         */
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('System'), new Link\Icon(new Cog()))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, __CLASS__.'::frontendDashboard'
        ));
    }

    /**
     * @return Stage
     */
    public function frontendDashboard()
    {

        $Stage = new Stage('Dashboard', 'System');

        $Value = 100 / disk_total_space(__DIR__) * disk_free_space(__DIR__);

        Main::getDispatcher()->registerWidget('System', new Panel('Festplattenkapazität', array(
            '<div class="progress" style="margin-bottom: 0;">
                          <div class="progress-bar progress-bar-success" style="width: '.$Value.'%"></div>
                          <div class="progress-bar progress-bar-danger" style="width: '.( 100 - $Value ).'%"></div>
                        </div>',
            'Gesamt: '.number_format(disk_total_space(__DIR__), 0, ',', '.'),
            'Frei: '.number_format(disk_free_space(__DIR__), 0, ',', '.')
        )), 2, 2);

        $free = shell_exec('free');
        $free = (string)trim($free);
        $free_arr = explode("\n", $free);
        $mem = explode(" ", $free_arr[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        $Value = $mem[2] / $mem[1] * 100;

        Main::getDispatcher()->registerWidget('System', new Panel('Speicherkapazität', array(
            '<div class="progress" style="margin-bottom: 0;">
              <div class="progress-bar progress-bar-success" style="width: '.$Value.'%"></div>
              <div class="progress-bar progress-bar-danger" style="width: '.( 100 - $Value ).'%"></div>
            </div>',
            'Gesamt: '.number_format($mem[1], 0, ',', '.'),
            'Frei: '.number_format($mem[2], 0, ',', '.')
        )), 2, 2);

        $load = sys_getloadavg();

        Main::getDispatcher()->registerWidget('System', new Panel('Rechenkapazität', array(
            '<div class="progress" style="margin-bottom: 0;">
              <div class="progress-bar progress-bar-success" style="width: '.( 50 * ( 2 - $load[0] ) ).'%"></div>
              <div class="progress-bar progress-bar-danger" style="width: '.( 50 * ( $load[0] ) ).'%"></div>
            </div>',
            'Genutzt: '.number_format($load[0], 5, ',', '.'),
            'Frei: '.number_format(2 - $load[0], 5, ',', '.')
        )), 2, 2);

        $Stage->setContent(Main::getDispatcher()->fetchDashboard('System'));

        return $Stage;
    }
}
