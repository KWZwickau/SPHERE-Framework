<?php
namespace SPHERE\Application\Corporation\Search;

use SPHERE\Application\IApplicationInterface;

/**
 * Class Search
 *
 * @package SPHERE\Application\Corporation\Search
 */
class Search implements IApplicationInterface
{

    public static function registerApplication()
    {

        Group\Group::registerModule();
    }

}
