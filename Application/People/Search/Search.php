<?php
namespace SPHERE\Application\People\Search;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class Search
 *
 * @package SPHERE\Application\People\Search
 */
class Search implements IApplicationInterface
{

    public static function registerApplication()
    {

        Group\Group::registerModule();
    }

}
