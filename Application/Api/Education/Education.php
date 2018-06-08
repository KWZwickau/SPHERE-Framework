<?php
namespace SPHERE\Application\Api\Education;

use SPHERE\Application\Api\Education\Certificate\Certificate;
use SPHERE\Application\Api\Education\ClassRegister\ClassRegister;
use SPHERE\Application\Api\Education\Division\StudentGroupSelect;
use SPHERE\Application\Api\Education\Division\StudentGroupSelectWithFilter;
use SPHERE\Application\Api\Education\Division\StudentSelect;
use SPHERE\Application\Api\Education\Division\SubjectSelect;
use SPHERE\Application\Api\Education\Prepare\Prepare;
use SPHERE\Application\IApplicationInterface;

/**
 * Class Education
 *
 * @package SPHERE\Application\Api\Education
 */
class Education implements IApplicationInterface
{

    public static function registerApplication()
    {

        Certificate::registerModule();
        ClassRegister::registerModule();
        Prepare::registerModule();
        SubjectSelect::registerApi();
        StudentSelect::registerApi();
        StudentGroupSelect::registerApi();
        StudentGroupSelectWithFilter::registerApi();
    }
}
