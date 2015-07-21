<?php
namespace SPHERE\Application\System\Information;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\System\Information\Cache\Cache;
use SPHERE\Application\System\Information\Database\Database;
use SPHERE\Application\System\Information\Platform\Platform;
use SPHERE\Application\System\Information\Protocol\Protocol;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class Information
 *
 * @package SPHERE\Application\System\Information
 */
class Information implements IApplicationInterface
{

    public static function registerApplication()
    {

        /**
         * Register Module
         */
        Platform::registerModule();
        Cache::registerModule();
        Database::registerModule();
        Protocol::registerModule();
        /**
         * Register Navigation
         */
        Main::getDisplay()->addServiceNavigation(
            new Link( new Link\Route( __NAMESPACE__ ), new Link\Name( 'System' ) )
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute( __NAMESPACE__, 'Information::frontendMachine' )
        );
    }

    /**
     * @return Stage
     */
    public function frontendMachine()
    {

        $Stage = new Stage( 'Systemeinstellungen' );

        ob_start();
        phpinfo();
        $PhpInfo = ob_get_clean();

        $Stage->setContent(
            '<div id="phpinfo">'.
            preg_replace( '!,!', ', ',
                preg_replace( '!<th>(enabled)\s*</th>!i',
                    '<th><span class="badge badge-success">$1</span></th>',
                    preg_replace( '!<td class="v">(On|enabled|active|Yes)\s*</td>!i',
                        '<td class="v"><span class="badge badge-success">$1</span></td>',
                        preg_replace( '!<td class="v">(Off|disabled|No)\s*</td>!i',
                            '<td class="v"><span class="badge badge-danger">$1</span></td>',
                            preg_replace( '!<i>no value</i>!',
                                '<span class="label label-warning">no value</span>',
                                preg_replace( '%^.*<body>(.*)</body>.*$%ms', '$1', $PhpInfo )
                            )
                        )
                    )
                )
            )
            .'</div>'
        );
        return $Stage;
    }
}
