<?php

namespace SPHERE\Application\Setting\Authorization\GroupRole;

use SPHERE\Application\Api\Setting\Authorization\ApiGroupRole;
use SPHERE\Application\Setting\Authorization\Account\Account;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\GroupRole
 */
class Frontend extends Extension implements IFrontendInterface
{
    /**
     * @return Stage
     */
    public function frontendGroupRole()
    {
        $stage = new Stage('Benutzerrollen', 'Übersicht');
        $stage->setMessage('Hier können Sie Benutzerrollen anlegen und verwalten. Eine Benutzerrolle besteht aus
            beliebigen Anzahl von Benutzerrechten, welche Sie selbst auswählen können. Beim Anlegen oder Bearbeiten eines
            Benutzerkontos können somit über die Benutzerrolle mehrere Benutzerrechte vorausgewählt werden. Durch das '
            . new Bold('nachträgliche') . ' Bearbeiten oder Löschen von Benutzerrollen werden die Benutzerrechte an den
            Benutzerkonten ' . new Bold('nicht') . ' geändert.');

        $stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Container('&nbsp;'),
                            ApiGroupRole::receiverModal(),
                            (new Primary(
                                new Plus() . ' Benutzerrolle hinzufügen',
                                ApiGroupRole::getEndpoint()
                            ))->ajaxPipelineOnClick(ApiGroupRole::pipelineOpenCreateGroupRoleModal())
                        )),
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Container('&nbsp;'),
                            ApiGroupRole::receiverBlock($this->loadGroupRoleTable(), 'GroupRoleContent')
                        )),
                    )),
                )),
            ))
        );

        return $stage;
    }

    /**
     * @return TableData
     */
    public function loadGroupRoleTable()
    {
        $dataList = array();
        if (($tblGroupRoleList = GroupRole::useService()->getGroupRoleAll())) {
            foreach ($tblGroupRoleList as $tblGroupRole) {
                $roles = array();
                if (($tblGroupRoleLinkList = GroupRole::useService()->getGroupRoleLinkAllByGroupRole($tblGroupRole))) {
                    foreach ($tblGroupRoleLinkList as $tblGroupRoleLink) {
                        if (($tblRole = $tblGroupRoleLink->getServiceTblRole())) {
                            $roles[] = $tblRole->getName();
                        }
                    }
                }
                asort($roles);

                $dataList[] = array(
                    'Name' => $tblGroupRole->getName(),
                    'Roles' => implode('</br>', $roles),
                    'Options' => (new Standard(
                        '',
                        ApiGroupRole::getEndpoint(),
                        new Edit(),
                        array(),
                        'Bearbeiten'
                    ))->ajaxPipelineOnClick(ApiGroupRole::pipelineOpenEditGroupRoleModal($tblGroupRole->getId()))
                    . (new Standard(
                            '',
                            ApiGroupRole::getEndpoint(),
                            new Remove(),
                            array(),
                            'Löschen'
                        ))->ajaxPipelineOnClick(ApiGroupRole::pipelineOpenDeleteGroupRoleModal($tblGroupRole->getId()))
                );
            }
        }

        return new TableData($dataList, null, array('Name' => 'Name', 'Roles' => 'Rollen', 'Options' => ' '));
    }

    /**
     * @param null $GroupRoleId
     * @param false $setPost
     *
     * @return Form
     */
    public function formGroupRole($GroupRoleId = null, $setPost = false)
    {
        if ($GroupRoleId && ($tblGroupRole = GroupRole::useService()->getGroupRoleById($GroupRoleId))) {
            // beim Checken der Inputfeldern darf der Post nicht gesetzt werden
            if ($setPost) {
                $Global = $this->getGlobal();
                $Global->POST['Data']['Name'] = $tblGroupRole->getName();

                if (($tblGroupRoleLinkList = GroupRole::useService()->getGroupRoleLinkAllByGroupRole($tblGroupRole))) {
                    foreach ($tblGroupRoleLinkList as $tblGroupRoleLink) {
                        if (($tblRole = $tblGroupRoleLink->getServiceTblRole())) {
                            $Global->POST['Data']['Role'][$tblRole->getId()] = $tblRole->getId();
                        }
                    }
                }

                $Global->savePost();
            }
        }

        if ($GroupRoleId) {
            $saveButton = (new Primary('Speichern', ApiGroupRole::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiGroupRole::pipelineEditGroupRoleSave($GroupRoleId));
        } else {
            $saveButton = (new Primary('Speichern', ApiGroupRole::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiGroupRole::pipelineCreateGroupRoleSave());
        }

        return (new Form(
            new FormGroup(array(
                new FormRow(array(
                    new FormColumn(
                        new Panel(
                            new Person() . ' Benutzerrolle',
                            array(new TextField('Data[Name]', 'Name', 'Name ' . new Danger('*'))),
                            Panel::PANEL_TYPE_INFO
                        ), 6),
                    new FormColumn(
                        new Panel(
                            new Nameplate() . ' mit folgenden Benutzerrechten',
                            Account::useService()->getRoleCheckBoxList('Data[Role]'),
                            Panel::PANEL_TYPE_INFO
                        ), 6),
                )),
                new FormRow(array(
                    new FormColumn(
                        $saveButton
                    )
                )),
            ))
        ))->disableSubmitAction();
    }
}