<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRight;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Minus;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Icon\Repository\TagList;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Icon\Repository\TileList;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\PullLeft;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Repository\Title;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link;
use SPHERE\Common\Window\Redirect;
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

        $Stage = new Stage('Rechteverwaltung');
        $this->menuButton($Stage);
        return $Stage;
    }

    /**
     * @param Stage $Stage
     */
    private function menuButton(Stage $Stage)
    {

        $Stage->addButton(new Standard('Rollen', new Link\Route(__NAMESPACE__.'/Role'), new TagList(), array(),
            'Zusammenstellung von Berechtigungen'));
        $Stage->addButton(new Standard('Zugriffslevel', new Link\Route(__NAMESPACE__.'/Level'), new Tag(), array(),
            'Gruppen von Privilegien'));
        $Stage->addButton(new Standard('Privilegien', new Link\Route(__NAMESPACE__.'/Privilege'), new TileBig(),
            array(), 'Gruppen von Rechten'));
        $Stage->addButton(new Standard('Rechte', new Link\Route(__NAMESPACE__.'/Right'), new TileList(), array(),
            'Geschützte Routen'));
    }

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendLevel($Name)
    {

        $Stage = new Stage('Berechtigungen', 'Zugriffslevel');
        $this->menuButton($Stage);
        $tblLevelAll = Access::useService()->getLevelAll();
        if ($tblLevelAll) {
            array_walk($tblLevelAll, function (TblLevel &$tblLevel) {

                $tblPrivilege = Access::useService()->getPrivilegeAllByLevel($tblLevel);
                if (empty( $tblPrivilege )) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblLevel->Option = new Warning('Keine Privilegien vergeben')
                        .new PullRight(new Danger('Privilegien hinzufügen',
                            '/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege',
                            null, array('Id' => $tblLevel->getId())
                        ));
                } else {
                    array_walk($tblPrivilege, function (TblPrivilege &$tblPrivilege) {

                        $tblPrivilege = $tblPrivilege->getName();
                    });
                    array_unshift($tblPrivilege, '');
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblLevel->Option = new Panel('Privilegien', $tblPrivilege)
                        .new PullRight(new Danger('Privilegien bearbeiten',
                            '/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege',
                            null, array('Id' => $tblLevel->getId())
                        ));
                }
            });
        }
        $Stage->setContent(
            ( $tblLevelAll
                ? new TableData($tblLevelAll, new Title('Bestehende Zugriffslevel'), array(
                    'Name'   => 'Name',
                    'Option' => 'Optionen'
                ))
                : new Warning('Keine Zugriffslevel vorhanden')
            )
            .Access::useService()->createLevel(
                new Form(new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField('Name', 'Name', 'Name')
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title('Zugriffslevel anlegen'))
                    , new Primary('Hinzufügen')
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
    public function frontendPrivilege($Name)
    {

        $Stage = new Stage('Berechtigungen', 'Privilegien');
        $this->menuButton($Stage);
        $tblPrivilegeAll = Access::useService()->getPrivilegeAll();
        if ($tblPrivilegeAll) {
            array_walk($tblPrivilegeAll, function (TblPrivilege &$tblPrivilege) {

                $tblRight = Access::useService()->getRightAllByPrivilege($tblPrivilege);
                if (empty( $tblRight )) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPrivilege->Option = new Warning('Keine Rechte vergeben')
                        .new PullRight(new Danger('Rechte hinzufügen',
                            '/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight',
                            null, array('Id' => $tblPrivilege->getId())
                        ));
                } else {
                    array_walk($tblRight, function (TblRight &$tblRight) {

                        $tblRight = $tblRight->getRoute();
                    });
                    array_unshift($tblRight, '');
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblPrivilege->Option = new Panel('Rechte (Routen)', $tblRight)
                        .new PullRight(new Danger('Rechte bearbeiten',
                            '/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight',
                            null, array('Id' => $tblPrivilege->getId())
                        ));
                }
            });
        }
        $Stage->setContent(
            ( $tblPrivilegeAll
                ? new TableData($tblPrivilegeAll, new Title('Bestehende Privilegien'), array(
                    'Name'   => 'Name',
                    'Option' => 'Optionen'
                ))
                : new Warning('Keine Privilegien vorhanden')
            )
            .Access::useService()->createPrivilege(
                new Form(new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField('Name', 'Name', 'Name')
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title('Privileg anlegen'))
                    , new Primary('Hinzufügen')
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
    public function frontendRight($Name)
    {

        $Stage = new Stage('Berechtigungen', 'Rechte');
        $this->menuButton($Stage);
        $tblRightAll = Access::useService()->getRightAll();

        $PublicRouteAll = Main::getDispatcher()->getPublicRoutes();
        if ($PublicRouteAll) {
            array_walk($PublicRouteAll, function (&$Route) {

                $Route = new PullClear(
                    new PullLeft('<span class="sphere-label">'.$Route.'</span>')
                    .new PullRight(
                        new External(
                            'Öffnen', $Route, null, array(), false
                        ).
                        new Danger(
                            'Hinzufügen', '/Platform/Gatekeeper/Authorization/Access/Right', null,
                            array('Name' => $Route)
                        )
                    )
                );
            });
        } else {
            $PublicRouteAll = array();
        }
        $Stage->setContent(
            ( $tblRightAll
                ? new TableData($tblRightAll, new Title('Bestehende Rechte', 'Routen'), array(
                    'Route' => 'Name'
                ))
                : new Warning('Keine Routen vorhanden')
            )
            .Access::useService()->createRight(
                new Form(
                    new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField('Name', 'Name', 'Name')
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title('Recht anlegen', 'Route'))
                    , new Primary('Hinzufügen')
                ), $Name
            )
            .new Panel('Öffentliche Routen', $PublicRouteAll, Panel::PANEL_TYPE_DANGER, null, 50)
        );
        return $Stage;
    }

    /**
     * @param null|string $Name
     *
     * @return Stage
     */
    public function frontendRole($Name)
    {

        $Stage = new Stage('Berechtigungen', 'Rollen');
        $this->menuButton($Stage);
        $tblRoleAll = Access::useService()->getRoleAll();
        if ($tblRoleAll) {
            array_walk($tblRoleAll, function (TblRole &$tblRole) {

                $tblLevel = Access::useService()->getLevelAllByRole($tblRole);

                if (empty( $tblLevel )) {
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblRole->Option = new Warning('Keine Zugriffslevel vergeben')
                        .new PullRight(new Danger('Zugriffslevel hinzufügen',
                            '/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel',
                            null, array('Id' => $tblRole->getId())
                        ));
                } else {
                    array_walk($tblLevel, function (TblLevel &$tblLevel) {

                        $tblLevel = $tblLevel->getName();
                    });
                    array_unshift($tblLevel, '');
                    /** @noinspection PhpUndefinedFieldInspection */
                    $tblRole->Option = new Panel('Zugriffslevel', $tblLevel)
                        .new PullRight(new Danger('Zugriffslevel bearbeiten',
                            '/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel',
                            null, array('Id' => $tblRole->getId())
                        ));
                }
            });
        }
        $Stage->setContent(
            ( $tblRoleAll
                ? new TableData($tblRoleAll, new Title('Bestehende Rollen'), array(
                    'Name'   => 'Name',
                    'Option' => 'Optionen'
                ))
                : new Warning('Keine Rollen vorhanden')
            )
            .Access::useService()->createRole(
                new Form(new FormGroup(
                        new FormRow(
                            new FormColumn(
                                new TextField('Name', 'Name', 'Name')
                            )
                        ), new \SPHERE\Common\Frontend\Form\Repository\Title('Rolle anlegen'))
                    , new Primary('Hinzufügen')
                ), $Name
            )
        );
        return $Stage;
    }

    /**
     * @param integer      $Id
     * @param null|integer $tblLevel
     * @param null|bool    $Remove
     *
     * @return Stage
     */
    public function frontendRoleGrantLevel($Id, $tblLevel, $Remove = null)
    {

        $Stage = new Stage('Berechtigungen', 'Rolle');
        $this->menuButton($Stage);

        $tblRole = Access::useService()->getRoleById($Id);
        if ($tblRole && null !== $tblLevel && ( $tblLevel = Access::useService()->getLevelById($tblLevel) )) {
            if ($Remove) {
                Access::useService()->removeRoleLevel($tblRole, $tblLevel);
                $Stage->setContent(
                    new Redirect('/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel', 0, array('Id' => $Id))
                );
                return $Stage;
            } else {
                Access::useService()->addRoleLevel($tblRole, $tblLevel);
                $Stage->setContent(
                    new Redirect('/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel', 0, array('Id' => $Id))
                );
                return $Stage;
            }
        }
        $tblAccessList = Access::useService()->getLevelAllByRole($tblRole);
        if (!$tblAccessList) {
            $tblAccessList = array();
        }

        $tblAccessListAvailable = array_udiff(Access::useService()->getLevelAll(), $tblAccessList,
            function (TblLevel $ObjectA, TblLevel $ObjectB) {

                return $ObjectA->getId() - $ObjectB->getId();
            }
        );

        /** @noinspection PhpUnusedParameterInspection */
        array_walk($tblAccessListAvailable, function (TblLevel &$Entity, $Index, $Id) {

            /** @noinspection PhpUndefinedFieldInspection */
            $Entity->Option = new PullRight(
                new Success('Hinzufügen', '/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel', new Plus(),
                    array(
                        'Id'       => $Id,
                        'tblLevel' => $Entity->getId()
                    ))
            );
        }, $Id);

        /** @noinspection PhpUnusedParameterInspection */
        array_walk($tblAccessList, function (TblLevel &$Entity, $Index, $Id) {

            /** @noinspection PhpUndefinedFieldInspection */
            $Entity->Option = new PullRight(
                new Danger('Entfernen', '/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel', new Minus(), array(
                    'Id'       => $Id,
                    'tblLevel' => $Entity->getId(),
                    'Remove'   => true
                ))
            );
        }, $Id);

        $Stage->setContent(
            new Info($tblRole->getName())
            .
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Zugriffslevel', 'Zugewiesen'),
                            ( empty( $tblAccessList )
                                ? new Warning('Keine Zugriffslevel vergeben')
                                : new TableData($tblAccessList, null,
                                    array('Name' => 'Name', 'Option' => 'Optionen'))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Zugriffslevel', 'Verfügbar'),
                            ( empty( $tblAccessListAvailable )
                                ? new Info('Keine weiteren Zugriffslevel verfügbar')
                                : new TableData($tblAccessListAvailable, null,
                                    array('Name' => 'Name', 'Option' => 'Optionen'))
                            )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param integer      $Id
     * @param null|integer $tblPrivilege
     * @param null|bool    $Remove
     *
     * @return Stage
     */
    public function frontendLevelGrantPrivilege($Id, $tblPrivilege, $Remove = null)
    {

        $Stage = new Stage('Berechtigungen', 'Zugriffslevel');
        $this->menuButton($Stage);

        $tblLevel = Access::useService()->getLevelById($Id);
        if ($tblLevel && null !== $tblPrivilege && ( $tblPrivilege = Access::useService()->getPrivilegeById($tblPrivilege) )) {
            if ($Remove) {
                Access::useService()->removeLevelPrivilege($tblLevel, $tblPrivilege);
                $Stage->setContent(
                    new Redirect('/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege', 0,
                        array('Id' => $Id))
                );
                return $Stage;
            } else {
                Access::useService()->addLevelPrivilege($tblLevel, $tblPrivilege);
                $Stage->setContent(
                    new Redirect('/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege', 0,
                        array('Id' => $Id))
                );
                return $Stage;
            }
        }
        $tblAccessList = Access::useService()->getPrivilegeAllByLevel($tblLevel);
        if (!$tblAccessList) {
            $tblAccessList = array();
        }

        $tblAccessListAvailable = array_udiff(Access::useService()->getPrivilegeAll(), $tblAccessList,
            function (TblPrivilege $ObjectA, TblPrivilege $ObjectB) {

                return $ObjectA->getId() - $ObjectB->getId();
            }
        );

        /** @noinspection PhpUnusedParameterInspection */
        array_walk($tblAccessListAvailable, function (TblPrivilege &$Entity, $Index, $Id) {

            /** @noinspection PhpUndefinedFieldInspection */
            $Entity->Option = new PullRight(
                new Success('Hinzufügen', '/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege', new Plus(),
                    array(
                        'Id'           => $Id,
                        'tblPrivilege' => $Entity->getId()
                    ))
            );
        }, $Id);

        /** @noinspection PhpUnusedParameterInspection */
        array_walk($tblAccessList, function (TblPrivilege &$Entity, $Index, $Id) {

            /** @noinspection PhpUndefinedFieldInspection */
            $Entity->Option = new PullRight(
                new Danger('Entfernen', '/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege', new Minus(),
                    array(
                        'Id'           => $Id,
                        'tblPrivilege' => $Entity->getId(),
                        'Remove'       => true
                    ))
            );
        }, $Id);

        $Stage->setContent(
            new Info($tblLevel->getName())
            .
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Privilegien', 'Zugewiesen'),
                            ( empty( $tblAccessList )
                                ? new Warning('Keine Privilegien vergeben')
                                : new TableData($tblAccessList, null,
                                    array('Name' => 'Name', 'Option' => 'Optionen'))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Privilegien', 'Verfügbar'),
                            ( empty( $tblAccessListAvailable )
                                ? new Info('Keine weiteren Privilegien verfügbar')
                                : new TableData($tblAccessListAvailable, null,
                                    array('Name' => 'Name', 'Option' => 'Optionen'))
                            )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }

    /**
     * @param integer      $Id
     * @param null|integer $tblRight
     * @param null|bool    $Remove
     *
     * @return Stage
     */
    public function frontendPrivilegeGrantRight($Id, $tblRight, $Remove = null)
    {

        $Stage = new Stage('Berechtigungen', 'Privileg');
        $this->menuButton($Stage);

        $tblPrivilege = Access::useService()->getPrivilegeById($Id);
        if ($tblPrivilege && null !== $tblRight && ( $tblRight = Access::useService()->getRightById($tblRight) )) {
            if ($Remove) {
                Access::useService()->removePrivilegeRight($tblPrivilege, $tblRight);
                $Stage->setContent(
                    new Redirect('/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight', 0,
                        array('Id' => $Id))
                );
                return $Stage;
            } else {
                Access::useService()->addPrivilegeRight($tblPrivilege, $tblRight);
                $Stage->setContent(
                    new Redirect('/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight', 0,
                        array('Id' => $Id))
                );
                return $Stage;
            }
        }
        $tblAccessList = Access::useService()->getRightAllByPrivilege($tblPrivilege);
        if (!$tblAccessList) {
            $tblAccessList = array();
        }

        $tblAccessListAvailable = array_udiff(Access::useService()->getRightAll(), $tblAccessList,
            function (TblRight $ObjectA, TblRight $ObjectB) {

                return $ObjectA->getId() - $ObjectB->getId();
            }
        );

        /** @noinspection PhpUnusedParameterInspection */
        array_walk($tblAccessListAvailable, function (TblRight &$Entity, $Index, $Id) {

            /** @noinspection PhpUndefinedFieldInspection */
            $Entity->Option = new PullRight(
                new Success('Hinzufügen', '/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight', new Plus(),
                    array(
                        'Id'       => $Id,
                        'tblRight' => $Entity->getId()
                    ))
            );
        }, $Id);

        /** @noinspection PhpUnusedParameterInspection */
        array_walk($tblAccessList, function (TblRight &$Entity, $Index, $Id) {

            /** @noinspection PhpUndefinedFieldInspection */
            $Entity->Option = new PullRight(
                new Danger('Entfernen', '/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight', new Minus(),
                    array(
                        'Id'       => $Id,
                        'tblRight' => $Entity->getId(),
                        'Remove'   => true
                    ))
            );
        }, $Id);

        $Stage->setContent(
            new Info($tblPrivilege->getName())
            .
            new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Rechte', 'Zugewiesen'),
                            ( empty( $tblAccessList )
                                ? new Warning('Keine Rechte vergeben')
                                : new TableData($tblAccessList, null,
                                    array('Route' => 'Route', 'Option' => 'Optionen'))
                            )
                        ), 6),
                        new LayoutColumn(array(
                            new \SPHERE\Common\Frontend\Layout\Repository\Title('Rechte', 'Verfügbar'),
                            ( empty( $tblAccessListAvailable )
                                ? new Info('Keine weiteren Rechte verfügbar')
                                : new TableData($tblAccessListAvailable, null,
                                    array('Route' => 'Route', 'Option' => 'Optionen'))
                            )
                        ), 6)
                    ))
                )
            )
        );

        return $Stage;
    }
}
