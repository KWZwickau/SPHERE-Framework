<?php
namespace SPHERE\Application\People\Search;

use SPHERE\Application\IApplicationInterface;

/**
 * Class Search
 *
 * @package SPHERE\Application\People\Search
 */
class Search implements IApplicationInterface
{
    public static function registerApplication()
    {
        self::registerModule();
    }

    public static function registerModule()
    {

    }

    /**
     * @return Frontend
     */
    public static function useFrontend(): Frontend
    {
        return new Frontend();
    }
}
