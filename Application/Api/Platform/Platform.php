<?php
namespace SPHERE\Application\Api\Platform;

use SPHERE\Application\Api\Platform\Database\Database;
use SPHERE\Application\IApplicationInterface;

class Platform implements IApplicationInterface
{

    public static function registerApplication()
    {

        Database::registerModule();
    }
}
