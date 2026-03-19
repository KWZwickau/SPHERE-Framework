<?php

namespace SPHERE\Application\RestApi\Education\Grade;

use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\ParentStudentAccess\OnlineGradebook\OnlineGradebook;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\RestApi\IApiInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiGrade implements IApiInterface
{
    /**
     * @return void
     */
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/OnlineGradeBook/Year/Load', __CLASS__ . '::getYears',
        ));

        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/OnlineGradeBook/Load', __CLASS__ . '::getOnlineGradeBook',
        ));

        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/OnlineGradeBook/RecentGrades/Load', __CLASS__ . '::getRecentGrades',
        ));
    }

    /**
     * @return JsonResponse
     */
    public static function getYears(): JsonResponse
    {
        $result = [];

        list($tblYearList) = OnlineGradebook::useService()->getOnlineGradeBookYearAndBlockedAndDataList();

        if (!empty($tblYearList)) {
            $tblYearList = (new Extension())->getSorter($tblYearList)->sortObjectBy('DisplayName', null, Sorter::ORDER_DESC);
            /** @var TblYear $tblYear */
            foreach ($tblYearList as $tblYear) {
                $result[] = array(
                    'Name' => $tblYear->getYear() ?: $tblYear->getName(),
                    'Type' => 'GradeBook',
                    'Link' => 'https://' . $_SERVER['HTTP_HOST'] . '/RestApi/Education/Grade/OnlineGradeBook/Load',
                    'Parameters' => array(
                        // todo remove AccountId after extern API
                        'AccountId' => ($tblAccount = Account::useService()->getAccountBySession()) ? $tblAccount->getId() : null,
                        'YearId' => $tblYear->getId()
                    )
                );

                $result[] = array(
                    'Name' => $tblYear->getYear() ?: $tblYear->getName(),
                    'Type' => 'RecentGrades',
                    'Link' => 'https://' . $_SERVER['HTTP_HOST'] . '/RestApi/Education/Grade/OnlineGradeBook/RecentGrades/Load',
                    'Parameters' => array(
                        // todo remove AccountId after extern API
                        'AccountId' => ($tblAccount = Account::useService()->getAccountBySession()) ? $tblAccount->getId() : null,
                        'YearId' => $tblYear->getId()
                    )
                );
            }
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }

    /**
     * @param $YearId
     *
     * @return JsonResponse
     */
    public static function getOnlineGradeBook($YearId = null): JsonResponse
    {
        $result = [];

        // prüfen ob schuljahr zulässig
        list($tblYearList) = OnlineGradebook::useService()->getOnlineGradeBookYearAndBlockedAndDataList();
        if (isset($tblYearList[$YearId])
            && ($tblYear = Term::useService()->getYearById($YearId))
            && ($tblPersonList = OnlineGradebook::useService()->getPersonListFromAccountBySession())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                if (($tblStudentEducation = DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear))) {
                    $result[] = array(
                        'Person' => $tblPerson->getLastFirstName(),
                        'Division' => DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson),
                        'SubjectList' => Grade::useService()->getStudentOverviewDataByPerson($tblPerson, $tblYear, $tblStudentEducation, true, false, true),
                    );
                }
            }
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }

    /**
     * @param null $YearId
     * @param null $MaxCount
     *
     * @return JsonResponse
     */
    public static function getRecentGrades($YearId = null, $MaxCount = null): JsonResponse
    {
        $result = [];

        // prüfen ob schuljahr zulässig
        list($tblYearList) = OnlineGradebook::useService()->getOnlineGradeBookYearAndBlockedAndDataList();
        if (isset($tblYearList[$YearId])
            && ($tblYear = Term::useService()->getYearById($YearId))
            && ($tblPersonList = OnlineGradebook::useService()->getPersonListFromAccountBySession())
        ) {
            foreach ($tblPersonList as $tblPerson) {
                if (DivisionCourse::useService()->getStudentEducationByPersonAndYear($tblPerson, $tblYear)) {
                    $result[] = array(
                        'Person' => $tblPerson->getLastFirstName(),
                        'Division' => DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson),
                        'GradeList' => Grade::useService()->getRecentGrades($tblPerson, $tblYear, true, $MaxCount)
                    );
                }
            }
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }
}