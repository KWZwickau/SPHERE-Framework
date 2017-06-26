<?php
namespace SPHERE\Application\Api;

use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;

/**
 * Class ApiTrait
 * @package SPHERE\Application\Api
 */
trait ApiTrait
{
    /**
     *
     */
    public static function registerApi()
    {
        Main::getDispatcher()->registerRoute(
            Main::getDispatcher()->createRoute(
                __CLASS__, __CLASS__ . '::exportApi'
            )
        );
    }

    /**
     * @return Route
     */
    public static function getEndpoint()
    {
        return new Route(__CLASS__);
    }
}
