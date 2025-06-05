<?php

namespace SPHERE\Application\RestApi\Education\ClassRegister;

use DateTime;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\RestApi\IApiInterface;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiDigital implements IApiInterface
{
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/Digital/Load', __CLASS__ . '::getDigitalLoad',
        ));
    }

    /**
     * @param null $Date
     * @param string $Type
     *
     * @return JsonResponse
     */
    public static function getDigitalLoad($Date = null, string $Type = ''): JsonResponse
    {
        $route = '/RestApi/Education/ClassRegister/Digital/Content/Load';

        // todo remove AccountId after extern API
        $params = [];
        if (($tblAccount = Account::useService()->getAccountBySession())) {
            $params = array('AccountId' => $tblAccount->getId());
        }

        $result = array();

        if ($Date) {
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
        } else {
            $dateTime = new DateTime('today');
        }

        if ($dateTime) {
            // Uhrzeit entfernen
            $dateTime = new DateTime($dateTime->format('d.m.Y'));

            if ($Type == 'Timetable') {
                $list = Timetable::useService()->getTimetableDataForTeacher($dateTime);
            } elseif ($Type == 'TeacherLectureship') {
                $list = Digital::useService()->getDigitalClassRegisterDataForTeacher();
            } else {
                // auto
                $list = Timetable::useService()->getTimetableDataForTeacher($dateTime);
                if (!$list) {
                    $list = Digital::useService()->getDigitalClassRegisterDataForTeacher();
                }
            }

            foreach ($list as $item) {
                $paramsItem = $params;
                if ($item instanceof TblTimetableNode) {
                    $paramsItem['DivisionCourseId'] = $item->getServiceTblCourse() ? $item->getServiceTblCourse()->getId() : null;
                    $result[] = array(
                        'UE' => $item->getHour(),
                        'DivisionCourse' => $item->getServiceTblCourse() ? $item->getServiceTblCourse()->getName() : null,
                        'Subject' => $item->getServiceTblSubject() ? $item->getServiceTblSubject()->getAcronym() : null,
                        'Room' => $item->getRoom() ?: null,
                        'Link' => 'https://' . $_SERVER['HTTP_HOST'] . $route,
                        'Parameters' => $paramsItem
                    );
                } else {
                    $paramsItem['DivisionCourseId'] = $item['DivisionCourseId'];
                    $result[] = array(
                        'UE' => null,
                        'DivisionCourse' => $item['DivisionCourse'] ?? null,
                        'Subject' => null,
                        'Room' => null,
                        'Link' => 'https://' . $_SERVER['HTTP_HOST'] . $route,
                        'Parameters' => $paramsItem
                    );
                }
            }
        }

        return new JsonResponse($result, Response::HTTP_OK);
    }
}