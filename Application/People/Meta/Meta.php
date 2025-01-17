<?php
namespace SPHERE\Application\People\Meta;

use SPHERE\Application\IApplicationInterface;
use SPHERE\Application\People\Meta\Agreement\Agreement;
use SPHERE\Application\People\Meta\Child\Child;
use SPHERE\Application\People\Meta\Club\Club;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Custody\Custody;
use SPHERE\Application\People\Meta\Masern\Masern;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Meta\Teacher\Teacher;

/**
 * Class Meta
 *
 * @package SPHERE\Application\People\Meta
 */
class Meta implements IApplicationInterface
{

    public static function registerApplication()
    {

        Agreement::registerModule();
        Masern::registerModule();
        Common::registerModule();
        Prospect::registerModule();
        Student::registerModule();
        Custody::registerModule();
        Club::registerModule();
        Teacher::registerModule();
        Child::registerModule();
    }

}
