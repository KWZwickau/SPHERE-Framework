<?php

namespace SPHERE\Application\RestApi\Menu;

use SPHERE\Application\ParentStudentAccess\OnlineAbsence\OnlineAbsence;
use SPHERE\Application\ParentStudentAccess\OnlineTimeTable\OnlineTimeTable;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\RestApi\IApiInterface;
use SPHERE\Common\Main;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiMenu implements IApiInterface
{
    /**
     * @return void
     */
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
            __NAMESPACE__ . '/Load', __CLASS__ . '::getMenu',
        ));
    }

    /**
     * @return JsonResponse
     */
    public static function getMenu(): JsonResponse
    {
        $result = [];

        if (($tblAccount = Account::useService()->getAccountBySession())) {
            // todo remove AccountId after extern API
            $params = array('AccountId' => $tblAccount->getId());

            if (($item = self::getMenuItem(
                '/RestApi/Education/Grade/OnlineGradeBook/Year/Load',
                'OnlineGradeBook',
                'Notenübersicht',
                $params
            ))) {
                $result[] = $item;
            }

            if (OnlineAbsence::useService()->getIsModuleRegistered()
                && ($item = self::getMenuItem(
                    '/RestApi/Education/Absence/Load',
                    'OnlineAbsence',
                    'Fehlzeiten',
                    $params
                ))
            ) {
                $result[] = $item;
            }

            if (OnlineTimeTable::useService()->getPersonListFromAccountBySession()
                && ($item = self::getMenuItem(
                    '/RestApi/Education/ClassRegister/OnlineTimeTable/Load',
                    'OnlineTimeTable',
                    'Stundenplan',
                    $params
                ))
            ) {
                $result[] = $item;
            }
        }

        return new JsonResponse($result, Response::HTTP_OK);
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