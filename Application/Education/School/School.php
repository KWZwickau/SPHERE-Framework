<?php
namespace SPHERE\Application\Education\School;

use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\IApplicationInterface;

/**
 * Class School
 *
 * @package SPHERE\Application\Education\School
 */
class School implements IApplicationInterface
{

    public static function registerApplication()
    {
        Course::registerModule();
    }
}
