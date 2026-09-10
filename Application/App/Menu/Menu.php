<?php
namespace SPHERE\Application\App\Menu;

use SPHERE\Application\App\AppException;
use SPHERE\Application\App\ApplicationInterface;
use SPHERE\Application\App\Dispatcher;
use SPHERE\Application\App\ModuleInterface;
use SPHERE\Application\App\Response\Code\Response200;
use SPHERE\Application\ParentStudentAccess\OnlineAbsence\OnlineAbsence;
use SPHERE\Application\ParentStudentAccess\OnlineTimeTable\OnlineTimeTable;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\JsonResponse;

class Menu implements ApplicationInterface, ModuleInterface
{

    /**
     * @throws AppException
     */
    public static function registerApplication()
    {

        self::registerModule();
    }

    /**
     * @return void
     */
    public static function registerModule(): void
    {

        /** @var Dispatcher $dispatcher */
        $dispatcher = Main::getDispatcher();
        $route = $dispatcher::createRoute(__NAMESPACE__ . '/Load', __CLASS__ . '::getMenu');
        $dispatcher::registerRoute($route);
    }

    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return JsonResponse
     */
    public static function getMenu(): JsonResponse
    {
        $result = [];

        if (($tblAccount = Account::useService()->getAccountBySession())) {
//            $params = array('AccountId' => $tblAccount->getId());
            $params = array();

            if (($item = self::getMenuItem(
                '/app/education/grade/onlinegradebook/year/load',
                'OnlineGradeBook',
                'Notenübersicht',
                $params
            ))) {
                $result[] = $item;
            }

            if (OnlineAbsence::useService()->getIsModuleRegistered()
                && ($item = self::getMenuItem(
                    '/app/education/absence/load',
                    'OnlineAbsence',
                    'Fehlzeiten',
                    $params
                ))
            ) {
                $result[] = $item;
            }

            if (OnlineTimeTable::useService()->getPersonListFromAccountBySession()
                && ($item = self::getMenuItem(
                    '/app/education/classregister/onlinetimetable/load',
                    'OnlineTimeTable',
                    'Stundenplan',
                    $params
                ))
            ) {
                $result[] = $item;
            }

            // existiert noch nicht -> erstmal nur Platzhalter
            if (($item = self::getMenuItem(
                '/app/education/classregister/digital/load',
                'Digital',
                'Digitales Klassenbuch',
                $params
            ))) {
                $result[] = $item;
            }
        }

        return new Response200($result);
    }

    /**
     * @param string $route
     * @param string $type
     * @param string $name
     * @param array $params
     *
     * @return array|null
     */
    private static function getMenuItem(string $route, string $type, string $name, array $params = []): ?array
    {
        if ((Access::useService()->hasAuthorization($route))) {
            return array(
                'Type' => $type,
                'Name' => $name,
                'Link' => 'https://' . $_SERVER['HTTP_HOST'] . $route,
                'Parameters' => $params
            );
        }

        return null;
    }
}