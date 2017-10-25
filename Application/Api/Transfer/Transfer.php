<?php

namespace SPHERE\Application\Api\Transfer;

use SPHERE\Application\Api\Transfer\Task\Task;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Transfer
 * @package SPHERE\Application\Api\Transfer
 */
class Transfer implements IApplicationInterface
{

    public static function registerApplication()
    {

        Task::registerModule();
    }
}