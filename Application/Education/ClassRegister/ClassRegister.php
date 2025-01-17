<?php
namespace SPHERE\Application\Education\ClassRegister;

use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Instruction\Instruction;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\IApplicationInterface;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;

/**
 * Class ClassRegister
 *
 * @package SPHERE\Application\Education\ClassRegister
 */
class ClassRegister implements IApplicationInterface
{
    public static function registerApplication()
    {
        Digital::registerModule();
        Instruction::registerModule();
        Timetable::registerModule();

        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route(__NAMESPACE__ . '\Digital'), new Link\Name('Digitales Klassenbuch'))
        );
        Main::getDisplay()->addApplicationNavigation(
            new Link(new Link\Route('SPHERE\Application\Education\Absence'), new Link\Name('Fehlzeiten'))
        );
    }
}
