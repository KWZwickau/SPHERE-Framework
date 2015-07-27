<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Access;

use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblLevel;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblRight;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access
 */
class Frontend
{

    /**
     * @return Stage
     */
    public function frontendWelcome()
    {

        $Stage = new Stage( 'Rechteverwaltung' );
        return $Stage;
    }

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendLevel( $Name )
    {

        $Stage = new Stage( 'Berechtigungen', 'Zugriffslevel' );
        $tblLevelAll = Access::useService()->getLevelAll();
        array_walk( $tblLevelAll, function ( TblLevel &$tblLevel ) {

            $tblPrivilege = Access::useService()->getPrivilegeAllByLevel( $tblLevel );
            if (empty( $tblPrivilege )) {
                /** @noinspection PhpUndefinedFieldInspection */
                $tblLevel->Option = new Warning( 'Keine Zugriffslevel vergeben' );
            } else {
                array_walk( $tblPrivilege, function ( TblPrivilege &$tblPrivilege ) {

                    $tblPrivilege = $tblPrivilege->getName();
                } );
                array_unshift( $tblPrivilege, '' );
                /** @noinspection PhpUndefinedFieldInspection */
                $tblLevel->Option = new Panel( 'Privilegien', $tblPrivilege )
                    .new PullRight( new Danger( 'Privilegien bearbeiten',
                        '/System/Gatekeeper/Authorization/Access/LevelGrantPrivilege',
                        null, array( 'Id' => $tblLevel->getId() )
                    ) );
            }
        } );
        $Stage->setContent(
            ( $tblLevelAll
                ? new TableData( $tblLevelAll, new Title( 'Bestehende Zugriffslevel' ), array(
                    'Name'   => 'Name',
                    'Option' => 'Optionen'
                ) )
                : new Warning( 'Keine Zugriffslevel vorhanden' )
            )
            .Access::useService()->createLevel(
                new Form( new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField( 'Name', 'Name', 'Name' )
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Zugriffslevel anlegen' ) )
                    , new Primary( 'Hinzufügen' )
                ), $Name
            )
        );
        return $Stage;
    }

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendPrivilege( $Name )
    {

        $Stage = new Stage( 'Berechtigungen', 'Privilegien' );
        $tblPrivilegeAll = Access::useService()->getPrivilegeAll();
        array_walk( $tblPrivilegeAll, function ( TblPrivilege &$tblPrivilege ) {

            $tblRight = Access::useService()->getRightAllByPrivilege( $tblPrivilege );
            if (empty( $tblRight )) {
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPrivilege->Option = new Warning( 'Keine Zugriffslevel vergeben' );
            } else {
                array_walk( $tblRight, function ( TblRight &$tblRight ) {

                    $tblRight = $tblRight->getRoute();
                } );
                array_unshift( $tblRight, '' );
                /** @noinspection PhpUndefinedFieldInspection */
                $tblPrivilege->Option = new Panel( 'Rechte (Routen)', $tblRight )
                    .new PullRight( new Danger( 'Rechte bearbeiten',
                        '/System/Gatekeeper/Authorization/Access/PrivilegeGrantRight',
                        null, array( 'Id' => $tblPrivilege->getId() )
                    ) );
            }
        } );
        $Stage->setContent(
            ( $tblPrivilegeAll
                ? new TableData( $tblPrivilegeAll, new Title( 'Bestehende Privilegien' ), array(
                    'Name'   => 'Name',
                    'Option' => 'Optionen'
                ) )
                : new Warning( 'Keine Privilegien vorhanden' )
            )
            .Access::useService()->createPrivilege(
                new Form( new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField( 'Name', 'Name', 'Name' )
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Privileg anlegen' ) )
                    , new Primary( 'Hinzufügen' )
                ), $Name
            )
        );
        return $Stage;
    }

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendRight( $Name )
    {

        $Stage = new Stage( 'Berechtigungen', 'Rechte' );
        $tblRightAll = Access::useService()->getRightAll();
        $Stage->setContent(
            ( $tblRightAll
                ? new TableData( $tblRightAll, new Title( 'Bestehende Rechte', 'Routen' ), array(
                    'Route' => 'Name'
                ) )
                : new Warning( 'Keine Routen vorhanden' )
            )
            .Access::useService()->createRight(
                new Form(
                    new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField( 'Name', 'Name', 'Name' )
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Recht anlegen', 'Route' ) )
                    , new Primary( 'Hinzufügen' )
                ), $Name
            )
        );
        return $Stage;
    }

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendRole( $Name )
    {

        $Stage = new Stage( 'Berechtigungen', 'Rollen' );
        $tblRoleAll = Access::useService()->getRoleAll();
        array_walk( $tblRoleAll, function ( TblRole &$tblRole ) {

            $tblLevel = Access::useService()->getLevelAllByRole( $tblRole );

            if (empty( $tblLevel )) {
                /** @noinspection PhpUndefinedFieldInspection */
                $tblRole->Option = new Warning( 'Keine Zugriffslevel vergeben' );
            } else {
                array_walk( $tblLevel, function ( TblLevel &$tblLevel ) {

                    $tblLevel = $tblLevel->getName();
                } );
                array_unshift( $tblLevel, '' );
                /** @noinspection PhpUndefinedFieldInspection */
                $tblRole->Option = new Panel( 'Zugriffslevel', $tblLevel )
                    .new PullRight( new Danger( 'Zugriffslevel bearbeiten',
                        '/System/Gatekeeper/Authorization/Access/RoleGrantLevel',
                        null, array( 'Id' => $tblRole->getId() )
                    ) );
            }
        } );
        $Stage->setContent(
            ( $tblRoleAll
                ? new TableData( $tblRoleAll, new Title( 'Bestehende Rollen' ), array(
                    'Name'   => 'Name',
                    'Option' => 'Optionen'
                ) )
                : new Warning( 'Keine Rollen vorhanden' )
            )
            .Access::useService()->createRole(
                new Form( new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField( 'Name', 'Name', 'Name' )
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title( 'Rolle anlegen' ) )
                    , new Primary( 'Hinzufügen' )
                ), $Name
            )
        );
        return $Stage;
    }
}
