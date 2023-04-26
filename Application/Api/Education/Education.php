<?php
namespace SPHERE\Application\Api\Education;

use SPHERE\Application\Api\Education\Certificate\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generate\ApiGenerate;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDiary;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Api\Education\ClassRegister\ApiInstructionItem;
use SPHERE\Application\Api\Education\ClassRegister\ApiInstructionSetting;
use SPHERE\Application\Api\Education\ClassRegister\ApiSortDivision;
use SPHERE\Application\Api\Education\ClassRegister\ClassRegister;
use SPHERE\Application\Api\Education\Division\AddDivision;
use SPHERE\Application\Api\Education\Division\DivisionCustody;
use SPHERE\Application\Api\Education\Division\DivisionRepresentative;
use SPHERE\Application\Api\Education\Division\DivisionTeacher;
use SPHERE\Application\Api\Education\Division\StudentGroupSelect;
use SPHERE\Application\Api\Education\Division\StudentSelect;
use SPHERE\Application\Api\Education\Division\StudentStatus;
use SPHERE\Application\Api\Education\Division\SubjectSelect;
use SPHERE\Application\Api\Education\Division\SubjectTeacher;
use SPHERE\Application\Api\Education\Division\ValidationFilter;
use SPHERE\Application\Api\Education\Graduation\Evaluation\ApiEvaluation;
use SPHERE\Application\Api\Education\Graduation\Evaluation\Evaluation;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradebook;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradesAllYears;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradeMaintenance;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiMinimumGradeCount;
use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Api\Education\Prepare\Prepare;
use SPHERE\Application\Api\Education\School\ApiCourse;
use SPHERE\Application\Api\Education\Term\YearHoliday;
use SPHERE\Application\Api\Education\Term\YearPeriod;
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
        ApiDiary::registerApi();
        ApiSortDivision::registerApi();
        Prepare::registerModule();
        SubjectSelect::registerApi();
        StudentSelect::registerApi();
        StudentGroupSelect::registerApi();
        ValidationFilter::registerApi();
        ApiGenerate::registerApi();
        StudentStatus::registerApi();
        ApiGradebook::registerApi();
        ApiEvaluation::registerApi();
        ApiMinimumGradeCount::registerApi();
        SubjectTeacher::registerApi();
        DivisionTeacher::registerApi();
        DivisionCustody::registerApi();
        DivisionRepresentative::registerApi();
        YearPeriod::registerApi();
        YearHoliday::registerApi();
        ApiPrepare::registerApi();
        ApiAbsence::registerApi();
        AddDivision::registerApi();
        ApiCourse::registerApi();
        ApiGradesAllYears::registerApi();
        ApiGradeMaintenance::registerApi();
        ApiDigital::registerApi();
        ApiInstructionSetting::registerApi();
        ApiInstructionItem::registerApi();
        Evaluation::registerModule();
    }
}
