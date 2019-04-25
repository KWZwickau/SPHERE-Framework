<?php
namespace SPHERE\Application\Api\Platform\Gatekeeper;

use SPHERE\Application\Api\Response;
use SPHERE\Application\IModuleInterface;
use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Common\Frontend\Icon\Repository\HazardSign;
use SPHERE\Common\Frontend\Icon\Repository\Success;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

/**
 * Class Gatekeeper
 *
 * @package SPHERE\Application\Api\Platform\Gatekeeper
 */
class Gatekeeper extends Extension implements IModuleInterface
{

    public static function registerModule()
    {

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Authorization/Access/PrivilegeGrantRight',
            __CLASS__.'::executeAuthorizationAccessPrivilegeGrantRight'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Authorization/Access/LevelGrantPrivilege',
            __CLASS__.'::executeAuthorizationAccessLevelGrantPrivilege'
        ));

        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __NAMESPACE__.'/Authorization/Access/RoleGrantLevel',
            __CLASS__.'::executeAuthorizationAccessRoleGrantLevel'
        ));
    }

    /**
     * @return IServiceInterface
     */
    public static function useService()
    {
        // TODO: Implement useService() method.
    }

    /**
     * @return IFrontendInterface
     */
    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param null|array  $Direction
     * @param null|array  $Data
     *
     * @return Response
     */
    public function executeAuthorizationAccessPrivilegeGrantRight( $Direction = null, $Data = null)
    {


        if ($Data && $Direction) {
            if (!isset( $Data['Id'] ) || !isset( $Data['tblRight'] )) {
                return ( new Response() )->addError('Fehler!',
                    new HazardSign().' Die Zuweisung der Rechte konnte nicht aktualisiert werden.', 0);
            }
            $Id = $Data['Id'];
            $tblRight = $Data['tblRight'];

            if ($Direction['From'] == 'TableAvailable') {
                $Remove = false;
            } else {
                $Remove = true;
            }

            $tblPrivilege = Access::useService()->getPrivilegeById($Id);
            if ($tblPrivilege && null !== $tblRight && ( $tblRight = Access::useService()->getRightById($tblRight) )) {
                if ($Remove) {
                    Access::useService()->removePrivilegeRight($tblPrivilege, $tblRight);
                } else {
                    Access::useService()->addPrivilegeRight($tblPrivilege, $tblRight);
                }
            }

            return ( new Response() )->addData(new Success().' Die Zuweisung der Rechte wurde erfolgreich aktualisiert.');
        }
        return ( new Response() )->addError('Fehler!',
            new HazardSign().' Die Zuweisung der Rechte konnte nicht aktualisiert werden.', 0);
    }

    /**
     * @param null|array  $Direction
     * @param null|array  $Data
     *
     * @return Response|string
     */
    public function executeAuthorizationAccessLevelGrantPrivilege( $Direction = null, $Data = null)
    {

        if ($Data && $Direction) {
            if (!isset( $Data['Id'] ) || !isset( $Data['tblPrivilege'] )) {
                return ( new Response() )->addError('Fehler!',
                    new HazardSign().' Die Zuweisung der Privilegien konnte nicht aktualisiert werden.', 0);
            }
            $Id = $Data['Id'];
            $tblPrivilege = $Data['tblPrivilege'];

            if ($Direction['From'] == 'TableAvailable') {
                $Remove = false;
            } else {
                $Remove = true;
            }

            $tblLevel = Access::useService()->getLevelById($Id);
            if ($tblLevel && null !== $tblPrivilege && ( $tblPrivilege = Access::useService()->getPrivilegeById($tblPrivilege) )) {
                if ($Remove) {
                    Access::useService()->removeLevelPrivilege($tblLevel, $tblPrivilege);
                } else {
                    Access::useService()->addLevelPrivilege($tblLevel, $tblPrivilege);
                }
            }
            return ( new Response() )->addData(new Success().' Die Zuweisung der Privilegien wurde erfolgreich aktualisiert.');
        }
        return ( new Response() )->addError('Fehler!',
            new HazardSign().' Die Zuweisung der Privilegien konnte nicht aktualisiert werden.', 0);
    }

    /**
     * @param null|array  $Direction
     * @param null|array  $Data
     *
     * @return Response|string
     */
    public function executeAuthorizationAccessRoleGrantLevel( $Direction = null, $Data = null)
    {

        if ($Data && $Direction) {
            if (!isset( $Data['Id'] ) || !isset( $Data['tblLevel'] )) {
                return ( new Response() )->addError('Fehler!',
                    new HazardSign().' Die Zuweisung der Zugriffslevel konnte nicht aktualisiert werden.', 0);
            }
            $Id = $Data['Id'];
            $tblLevel = $Data['tblLevel'];

            if ($Direction['From'] == 'TableAvailable') {
                $Remove = false;
            } else {
                $Remove = true;
            }

            $tblRole = Access::useService()->getRoleById($Id);
            if ($tblRole && null !== $tblLevel && ( $tblLevel = Access::useService()->getLevelById($tblLevel) )) {
                if ($Remove) {
                    Access::useService()->removeRoleLevel($tblRole, $tblLevel);
                } else {
                    Access::useService()->addRoleLevel($tblRole, $tblLevel);
                }
            }
            return ( new Response() )->addData(new Success().' Die Zuweisung der Zugriffslevel wurde erfolgreich aktualisiert.');
        }
        return ( new Response() )->addError('Fehler!',
            new HazardSign().' Die Zuweisung der Zugriffslevel konnte nicht aktualisiert werden.', 0);
    }
}
