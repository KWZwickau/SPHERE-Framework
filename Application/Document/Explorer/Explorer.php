<?php
namespace SPHERE\Application\Document\Explorer;

use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Explorer
 *
 * @package SPHERE\Application\Document\Explorer
 */
class Explorer implements IApplicationInterface
{

    public static function registerApplication()
    {

        Storage::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__), new Link\Name('Explorer'))
        );
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__, 'Frontend::frontendExplorer'
        ));
    }
}
