<?php

namespace SPHERE\Application\RestApi\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\ParentStudentAccess\OnlineTimeTable\OnlineTimeTable;
use SPHERE\Application\RestApi\IApiInterface;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiTimeTable  implements IApiInterface
{
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/OnlineTimeTable/Load', __CLASS__ . '::getTimeTableLoad',
        ));
    }

    /**
     * @param $Date
     *
     * @return JsonResponse
     */
    public static function getTimeTableLoad($Date): JsonResponse
    {
        $result = array();

        if (!($dateTime = DateTime::createFromFormat(\DateTimeInterface::ISO8601, $Date))) {
            if (str_contains($Date, '-') && strlen($Date) >= 10) {
                $dateTime = DateTime::createFromFormat('Y-m-d', substr($Date, 0, 10));
            } elseif (str_contains($Date, '.') && strlen($Date) >= 10) {
                $dateTime = DateTime::createFromFormat('d.m.Y', substr($Date, 0, 10));
            }
        }

        if (!$dateTime) {
            $result['Error'] = $Date . ' konnte nicht in ein korrektes Datum umgewandelt werden';
        }

        if ($dateTime
            && ($tblPersonList = OnlineTimeTable::useService()->getPersonListFromAccountBySession())
        ) {
            // Uhrzeit entfernen
            $dateTime = new DateTime($dateTime->format('d.m.Y'));

            foreach ($tblPersonList as $tblPerson) {
                $timeTable = Timetable::useService()->getTimeTableByStudentAndDate($tblPerson, $dateTime);

                $result[] = array(
                    'Person' => $tblPerson->getLastFirstName(),
                    'Division' => DivisionCourse::useService()->getCurrentMainCoursesByPersonAndDate($tblPerson),
                    'Date' => $dateTime->format('c'),
                    'FullDay' => $timeTable['FullDay'] ?? null,
                    'TimeTableList' => $timeTable['TimeTableList'] ?? [],
                );
            }
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }
}