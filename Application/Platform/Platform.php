<?php
namespace SPHERE\Application\Platform;

use SPHERE\Application\IClusterInterface;
use SPHERE\Application\Platform\Assistance\Assistance;
use SPHERE\Application\Platform\Gatekeeper\Gatekeeper;
use SPHERE\Application\Platform\System\System;
use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Stage;

/**
 * Class System
 *
 * @package SPHERE\Application\System
 */
class Platform implements IClusterInterface
{

    public static function registerCluster()
    {

        /**
         * Register Application
         */
        System::registerApplication();
        Gatekeeper::registerApplication();
        Assistance::registerApplication();
        /**
         * Register Navigation
         */
        Main::getDisplay()->addServiceNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Plattform'), new Link\Icon(new CogWheels()))
        );
        /**
         * Register Route
         */
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(__NAMESPACE__, 'Platform::frontendPlatform')
        );
    }

    /**
     * @return Stage
     */
    public function frontendPlatform()
    {

        $Stage = new Stage('Plattform', 'Systemeinstellungen');

        ob_start();
        phpinfo();
        $PhpInfo = ob_get_clean();

        $Stage->setContent(
            '<div id="phpinfo">'.
            preg_replace('!,!', ', ',
                preg_replace('!<th>(enabled)\s*</th>!i',
                    '<th><span class="badge badge-success">$1</span></th>',
                    preg_replace('!<td class="v">(On|enabled|active|Yes)\s*</td>!i',
                        '<td class="v"><span class="badge badge-success">$1</span></td>',
                        preg_replace('!<td class="v">(Off|disabled|No)\s*</td>!i',
                            '<td class="v"><span class="badge badge-danger">$1</span></td>',
                            preg_replace('!<i>no value</i>!',
                                '<span class="label label-warning">no value</span>',
                                preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $PhpInfo)
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
