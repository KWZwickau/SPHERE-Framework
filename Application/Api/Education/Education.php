<?php
namespace SPHERE\Application\Api\Education;

use SPHERE\Application\Api\Education\Certificate\Certificate;
use SPHERE\Application\Api\Education\Certificate\Generate\ApiGenerate;
use SPHERE\Application\Api\Education\Certificate\PrintCertificate\ApiPrintCertificate;
use SPHERE\Application\Api\Education\Certificate\Reporting\ApiReporting;
use SPHERE\Application\Api\Education\ClassRegister\ApiAbsence;
use SPHERE\Application\Api\Education\ClassRegister\ApiDiary;
use SPHERE\Application\Api\Education\ClassRegister\ApiDigital;
use SPHERE\Application\Api\Education\ClassRegister\ApiInstructionItem;
use SPHERE\Application\Api\Education\ClassRegister\ApiInstructionSetting;
use SPHERE\Application\Api\Education\ClassRegister\ApiSortDivision;
use SPHERE\Application\Api\Education\ClassRegister\ApiTimetable;
use SPHERE\Application\Api\Education\ClassRegister\ClassRegister;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourse;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseMember;
use SPHERE\Application\Api\Education\DivisionCourse\ApiDivisionCourseStudent;
use SPHERE\Application\Api\Education\DivisionCourse\ApiStudentSubject;
use SPHERE\Application\Api\Education\DivisionCourse\ApiSubjectTable;
use SPHERE\Application\Api\Education\DivisionCourse\ApiTeacherLectureship;
use SPHERE\Application\Api\Education\DivisionCourse\ApiYearChange;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiGradeBook;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiScoreRule;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiScoreType;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiStudentOverview;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTask;
use SPHERE\Application\Api\Education\Graduation\Grade\ApiTeacherGroup;
use SPHERE\Application\Api\Education\Graduation\Grade\Gradebook;
use SPHERE\Application\Api\Education\Graduation\Grade\Task;
use SPHERE\Application\Api\Education\Prepare\ApiPrepare;
use SPHERE\Application\Api\Education\Prepare\Prepare;
use SPHERE\Application\Api\Education\School\ApiCourse;
use SPHERE\Application\Api\Education\Term\ApiYear;
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
        ApiGenerate::registerApi();
        ApiReporting::registerApi();
        YearPeriod::registerApi();
        YearHoliday::registerApi();
        ApiPrepare::registerApi();
        ApiAbsence::registerApi();
        ApiCourse::registerApi();
        ApiDigital::registerApi();
        ApiTimetable::registerApi();
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
        Task::registerModule();
        ApiScoreType::registerApi();
        ApiScoreRule::registerApi();
        Gradebook::registerModule();
        ApiYear::registerApi();
        ApiPrintCertificate::registerApi();
    }
}
