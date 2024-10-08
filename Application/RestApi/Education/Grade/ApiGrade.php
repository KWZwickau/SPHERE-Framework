<?php

namespace SPHERE\Application\RestApi\Education\Grade;

use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\ParentStudentAccess\OnlineGradebook\OnlineGradebook;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiGrade
{
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/OnlineGradeBook/Load', __CLASS__ . '::getOnlineGradeBookLoad',
        ));

        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/OnlineGradeBook/Year/Load', __CLASS__ . '::getOnlineGradeBookYears',
        ));
    }

    /**
     * @param $YearId
     *
     * @return JsonResponse
     */
    public static function getOnlineGradeBookLoad($YearId = null): JsonResponse
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
     * @return JsonResponse
     */
    public static function getOnlineGradeBookYears(): JsonResponse
    {
        $result = [];

        list($tblYearList) = OnlineGradebook::useService()->getOnlineGradeBookYearAndBlockedAndDataList();

        if (!empty($tblYearList)) {
            $tblYearList = (new Extension())->getSorter($tblYearList)->sortObjectBy('DisplayName', null, Sorter::ORDER_DESC);
            /** @var TblYear $tblYear */
            foreach ($tblYearList as $tblYear) {
                $result[] = array(
                    'Name' => $tblYear->getYear() ?: $tblYear->getName(),
                    'Link' => 'https://' . $_SERVER['HTTP_HOST'] . '/RestApi/Education/Grade/OnlineGradeBook/Load',
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
}