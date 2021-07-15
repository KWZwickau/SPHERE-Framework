<?php

namespace SPHERE\Application\Api\Setting\Authorization;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\Authorization\GroupRole\GroupRole;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Question;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiGroupRole
 * 
 * @package SPHERE\Application\Api\Setting\Authorization
 */
class ApiGroupRole extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadGroupRoleContent');

        $Dispatcher->registerMethod('openCreateGroupRoleModal');
        $Dispatcher->registerMethod('saveCreateGroupRoleModal');

        $Dispatcher->registerMethod('openEditGroupRoleModal');
        $Dispatcher->registerMethod('saveEditGroupRoleModal');

        $Dispatcher->registerMethod('openDeleteGroupRoleModal');
        $Dispatcher->registerMethod('saveDeleteGroupRoleModal');


        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock($Content = '', $Identifier = '')
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose()
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadGroupRoleContent()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'GroupRoleContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadGroupRoleContent',
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return TableData
     */
    public function loadGroupRoleContent()
    {
        return GroupRole::useFrontend()->loadGroupRoleTable();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineOpenCreateGroupRoleModal()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openCreateGroupRoleModal',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Danger|string
     */
    public function openCreateGroupRoleModal()
    {
        return $this->getGroupRoleModal(GroupRole::useFrontend()->formGroupRole());
    }

    /**
     * @param $form
     * @param null $GroupRoleId
     *
     * @return string
     */
    private function getGroupRoleModal($form, $GroupRoleId = null)
    {
        if ($GroupRoleId) {
            $title = new Title(new Edit() . ' Benutzerrollen-Gruppe bearbeiten');
        } else {
            $title = new Title(new Plus() . ' Benutzerrollen-Gruppe hinzufügen');
        }

        return $title
            . new Layout(array(
                    new LayoutGroup(
                        new LayoutRow(
                            new LayoutColumn(
                                new Well(
                                    $form
                                )
                            )
                        )
                    ))
            );
    }

    /**
     * @return Pipeline
     */
    public static function pipelineCreateGroupRoleSave()
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveCreateGroupRoleModal'
        ));

        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param array $Data
     *
     * @return Danger|string
     */
    public function saveCreateGroupRoleModal($Data = array())
    {
        if (($form = GroupRole::useService()->checkFormGroupRole($Data))) {
            // display Errors on form
            return $this->getGroupRoleModal($form);
        }

        if (GroupRole::useService()->createGroupRole($Data)) {
            return new Success('Die Benutzerrollen-Gruppe wurde erfolgreich gespeichert.')
                . self::pipelineLoadGroupRoleContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Benutzerrollen-Gruppe konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param int $GroupRoleId
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditGroupRoleModal($GroupRoleId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openEditGroupRoleModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'GroupRoleId' => $GroupRoleId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GroupRoleId
     *
     * @return string
     */
    public function openEditGroupRoleModal($GroupRoleId)
    {
        if (!($tblGroupRole = GroupRole::useService()->getGroupRoleById($GroupRoleId))) {
            return new Danger('Die Benutzerrollen-Gruppe wurde nicht gefunden', new Exclamation());
        }

        return $this->getGroupRoleModal(GroupRole::useFrontend()->formGroupRole($GroupRoleId, true), $GroupRoleId);
    }

    /**
     * @param $GroupRoleId
     *
     * @return Pipeline
     */
    public static function pipelineEditGroupRoleSave($GroupRoleId)
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditGroupRoleModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'GroupRoleId' => $GroupRoleId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GroupRoleId
     * @param $Data
     *
     * @return Danger|string
     */
    public function saveEditGroupRoleModal($GroupRoleId, $Data)
    {
        if (!($tblGroupRole = GroupRole::useService()->getGroupRoleById($GroupRoleId))) {
            return new Danger('Die Benutzerrollen-Gruppe wurde nicht gefunden', new Exclamation());
        }

        if (($form = GroupRole::useService()->checkFormGroupRole($Data, $tblGroupRole))) {
            // display Errors on form
            return $this->getGroupRoleModal($form, $GroupRoleId);
        }

        if (GroupRole::useService()->updateGroupRole($tblGroupRole, $Data)) {
            return new Success('Die Benutzerrollen-Gruppe wurde erfolgreich gespeichert.')
                . self::pipelineLoadGroupRoleContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Benutzerrollen-Gruppe konnte nicht gespeichert werden.') . self::pipelineClose();
        }
    }

    /**
     * @param int $GroupRoleId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteGroupRoleModal($GroupRoleId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openDeleteGroupRoleModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'GroupRoleId' => $GroupRoleId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GroupRoleId
     *
     * @return string
     */
    public function openDeleteGroupRoleModal($GroupRoleId)
    {
        if (!($tblGroupRole = GroupRole::useService()->getGroupRoleById($GroupRoleId))) {
            return new Danger('Die Benutzerrollen-Gruppe wurde nicht gefunden', new Exclamation());
        }

        return new Title(new Remove() . ' Benutzerrollen-Gruppe löschen')
            . new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel(
                                new Question() . ' Diese Benutzerrollen-Gruppe wirklich löschen?',
                                array(
                                    $tblGroupRole->getName(),
                                ),
                                Panel::PANEL_TYPE_DANGER
                            )
                            . (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                                ->ajaxPipelineOnClick(self::pipelineDeleteGroupRoleSave($GroupRoleId))
                            . (new Standard('Nein', self::getEndpoint(), new Remove()))
                                ->ajaxPipelineOnClick(self::pipelineClose())
                        )
                    )
                )
            );
    }

    /**
     * @param $GroupRoleId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteGroupRoleSave($GroupRoleId)
    {

        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveDeleteGroupRoleModal'
        ));
        $ModalEmitter->setPostPayload(array(
            'GroupRoleId' => $GroupRoleId
        ));
        $ModalEmitter->setLoadingMessage('Wird bearbeitet');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $GroupRoleId
     *
     * @return Danger|string
     */
    public function saveDeleteGroupRoleModal($GroupRoleId)
    {
        if (!($tblGroupRole = GroupRole::useService()->getGroupRoleById($GroupRoleId))) {
            return new Danger('Die Benutzerrollen-Gruppe wurde nicht gefunden', new Exclamation());
        }

        if (GroupRole::useService()->destroyGroupRole($tblGroupRole)) {
            return new Success('Die Benutzerrollen-Gruppe wurde erfolgreich gelöscht.')
                . self::pipelineLoadGroupRoleContent()
                . self::pipelineClose();
        } else {
            return new Danger('Die Benutzerrollen-Gruppe konnte nicht gelöscht werden.') . self::pipelineClose();
        }
    }
}