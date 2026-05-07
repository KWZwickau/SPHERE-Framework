<?php

namespace SPHERE\Application\App\Education\ClassRegister;


use DateTime;
use DateTimeInterface;
use SPHERE\Application\App\AppException;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response200;
use SPHERE\Application\App\Response\ResponseInterface;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;

/**
 *
 */
class ClassRegister implements ModuleInterface
{
    /**
     * @throws AppException
     */
    public static function registerModule()
    {
        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/Digital/Load', __CLASS__ . '::getDigitalLoad');
        $dispatcher::registerRoute($route);
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/Digital/Nuff', __CLASS__ . '::getDigitalLoad');
        $dispatcher::registerRoute($route);
    }

    public static function getDigitalLoad(?string $Date = null, string $Type = ''): ResponseInterface
    {
        $route = '/RestApi/Education/ClassRegister/Digital/Content/Load';

        // todo remove AccountId after extern API
        $params = [];
        if (($tblAccount = Account::useService()->getAccountBySession())) {
            $params = array('AccountId' => $tblAccount->getId());
        }

        $result = array();

        if ($Date) {
            if (!($dateTime = DateTime::createFromFormat(DateTimeInterface::ISO8601, $Date))) {
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
                        'Link' => [
                            'loadContent' => [
                                'Method' => 'GET',
                                'Url' => 'https://' . $_SERVER['HTTP_HOST'] . $route,
                                'Parameters' => $paramsItem
                        ]]
                    );
                }
            }
        }

        return new Response200($result);
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

}
