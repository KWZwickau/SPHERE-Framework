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
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourse;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseMember;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseStudent;
use SPHERE\Application\Api\Education\DivisionCourse\ApiStudentSubject;
use SPHERE\Application\Api\Education\DivisionCourse\ApiSubjectTable;
use SPHERE\Application\Api\Education\DivisionCourse\ApiTeacherLectureship;
use SPHERE\Application\Api\Education\DivisionCourse\ApiYearChange;
use SPHERE\Application\Api\Education\Graduation\Evaluation\ApiEvaluation;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiScoreRule;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiScoreType;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiStudentOverview;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTask;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradebookOld;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradesAllYears;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiGradeMaintenance;
use SPHERE\Application\Api\Education\Graduation\Gradebook\ApiMinimumGradeCount;
use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Api\Education\Prepare\Prepare;
use SPHERE\Application\Api\Education\School\ApiCourse;
use SPHERE\Application\Api\Education\Term\YearHoliday;
use SPHERE\Application\Api\Education\Term\YearPeriod;
use SPHERE\Application\Education\Graduation\Evaluation\Evaluation;
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
        ApiGradebookOld::registerApi();
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

        ApiDivisionCourse::registerApi();
        ApiDivisionCourseMember::registerApi();
        ApiDivisionCourseStudent::registerApi();
        ApiTeacherLectureship::registerApi();
        ApiSubjectTable::registerApi();
        ApiStudentSubject::registerApi();
        ApiYearChange::registerApi();
        ApiTeacherGroup::registerApi();
        ApiGradeBook::registerApi();
        ApiStudentOverview::registerApi();
        ApiTask::registerApi();
        ApiScoreType::registerApi();
        ApiScoreRule::registerApi();
    }
}
