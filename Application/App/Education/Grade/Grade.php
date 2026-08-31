<?php
namespace SPHERE\Application\App\Education\Grade;

use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\Response\Code\Response201;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\ParentStudentAccess\OnlineGradebook\OnlineGradebook;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class Grade implements ModuleInterface
{
    /**
     * @return void
     */
    public static function registerModule(): void
    {

        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/OnlineGradeBook/Year/Load', __CLASS__ . '::getYears');
        $dispatcher::registerRoute($route);
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/OnlineGradeBook/Load', __CLASS__ . '::getOnlineGradeBook');
        $dispatcher::registerRoute($route);
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/OnlineGradeBook/RecentGrades/Load', __CLASS__ . '::getRecentGrades');
        $dispatcher::registerRoute($route);
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
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
                    'Year' => $tblYear->getYear() ?: $tblYear->getName(),

                    'Links' => [
//                        'Type' => 'GradeBook',
                        'loadGrades' => [
                            'Method' => 'GET',
//                            'Name' => 'Fehlzeit hinzufügen',
                            'Url' => 'https://' . $_SERVER['HTTP_HOST'] . '/App/Education/Grade/OnlineGradeBook/Load',
                            'Parameters' => [
                                'AccountId' => ($tblAccount = Account::useService()->getAccountBySession()) ? $tblAccount->getId() : null,
                                'YearId' => $tblYear->getId()
                            ]
                        ],
//                        'Type' => 'RecentGrades',
                        'loadRecentGradesContent' => [
                            'Method' => 'GET',
//                            'Name' => 'Fehlzeit hinzufügen',
                            'Url' => 'https://' . $_SERVER['HTTP_HOST'] . '/App/Education/Grade/OnlineGradeBook/RecentGrades/Load',
                            'Parameters' => [
                                'AccountId' => ($tblAccount = Account::useService()->getAccountBySession()) ? $tblAccount->getId() : null,
                                'YearId' => $tblYear->getId()
                            ]
                        ]
                    ]
                );
            }
        }

        return new Response201($result);
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
                        'SubjectList' => \SPHERE\Application\Education\Graduation\Grade\Grade::useService()->getStudentOverviewDataByPerson($tblPerson, $tblYear, $tblStudentEducation, true, false, true),
                        'Links' => [],
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
                        'GradeList' => Grade::useService()->getRecentGrades($tblPerson, $tblYear, true, $MaxCount),
                        'Links' => [],
                    );
                }
            }
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }
}