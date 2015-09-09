<?php
namespace SPHERE\Application\Corporation\Search;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Window\Navigation\Link;

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
