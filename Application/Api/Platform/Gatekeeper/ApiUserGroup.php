<?php
namespace SPHERE\Application\Api\Platform\Gatekeeper;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Authorization\Group\Group;
use SPHERE\Common\Frontend\Ajax\Emitter\ClientEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary;
use SPHERE\Common\Frontend\Form\Repository\Field\TextArea;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Enable;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Icon\Repository\Setup;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiUserGroup
 *
 * @package SPHERE\Application\Api\Platform\Gatekeeper
 */
class ApiUserGroup extends Extension implements IApiInterface
{
    const API_DISPATCHER = 'MethodName';

    public static function registerApi()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__, __CLASS__ . '::ApiDispatcher'
        ));
    }

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function ApiDispatcher($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('pieceTableUserGroupList');

        $Dispatcher->registerMethod('pieceTableMemberGroup');
        $Dispatcher->registerMethod('pieceTableMemberAvailable');

        $Dispatcher->registerMethod('addUserToGroup');
        $Dispatcher->registerMethod('removeUserFromGroup');

        $Dispatcher->registerMethod('createUserGroup');
        $Dispatcher->registerMethod('editUserGroup');
        $Dispatcher->registerMethod('destroyUserGroup');

        return $Dispatcher->callMethod($MethodName);
    }

    public function pieceTableUserGroupList($receiverTableUserGroupList)
    {

        $tblGroupAll = Account::useService()->getGroupAll(Consumer::useService()->getConsumerBySession());

        $receiverDestroyGroup = new ModalReceiver('Sind Sie sicher?');
        $receiverDestroyGroupClose = new InlineReceiver();
        $receiverEditGroup = new ModalReceiver('Gruppe bearbeiten', new Close());

        $TableList = array();
        if ($tblGroupAll) {
            array_walk($tblGroupAll,
                function (TblGroup $tblGroup) use (
                    &$TableList,
                    $receiverTableUserGroupList,
                    $receiverDestroyGroup,
                    $receiverDestroyGroupClose,
                    $receiverEditGroup
                ) {
                    /**
                     * Destroy Group
                     */
                    $pipelineDestroyGroup = new Pipeline();
                    $emitterDestroyGroup = new ServerEmitter(new BlockReceiver(), ApiUserGroup::getRoute());
                    $emitterDestroyGroup->setLoadingMessage('Gruppe löschen', 'Bitte warten');
                    $emitterDestroyGroup->setGetPayload(array(
                        ApiUserGroup::API_DISPATCHER => 'destroyUserGroup',
                        'Id' => $tblGroup->getId()
                    ));
                    $pipelineDestroyGroup->addEmitter($emitterDestroyGroup);
                    /**
                     * Close Destroy Dialog
                     */
                    $emitterDestroyGroupClose = new ScriptEmitter($receiverDestroyGroupClose,
                        new ScriptEmitter\CloseModalScript($receiverDestroyGroup));
                    $pipelineDestroyGroup->addEmitter($emitterDestroyGroupClose);
                    /**
                     * Reload Group List
                     */
                    $emitterTableUserGroupList = new ServerEmitter((new BlockReceiver())->setIdentifier($receiverTableUserGroupList),
                        ApiUserGroup::getRoute());
                    $emitterTableUserGroupList->setGetPayload(array(
                        ApiUserGroup::API_DISPATCHER => 'pieceTableUserGroupList',
                        'receiverTableUserGroupList' => $receiverTableUserGroupList
                    ));
                    $pipelineDestroyGroup->addEmitter($emitterTableUserGroupList);

                    /**
                     * Open Confirm Dialog
                     */
                    $pipelineDestroyGroupConfirm = new Pipeline();
                    $emitterDestroyGroupConfirm = new ClientEmitter($receiverDestroyGroup, new Layout(
                        new LayoutGroup(array(
                            new LayoutRow(
                                new LayoutColumn('Wollen Sie die Gruppe ' . $tblGroup->getName() . ' wirklich löschen?')
                            ),
                            new LayoutRow(
                                new LayoutColumn(
                                    new PullRight(
                                        (new Standard('Ja', __CLASS__,
                                            new Enable())
                                        )->ajaxPipelineOnClick($pipelineDestroyGroup)
                                        . new Close('Nein', new Disable())
                                    )
                                )
                            ),
                        ))
                    ));
                    $pipelineDestroyGroupConfirm->addEmitter($emitterDestroyGroupConfirm);

                    /**
                     * Edit Group
                     */
                    $pipelineEditGroup = new Pipeline();
                    $emitterEditGroup = new ServerEmitter($receiverEditGroup, ApiUserGroup::getRoute());
                    $emitterEditGroup->setGetPayload(array(
                        ApiUserGroup::API_DISPATCHER => 'editUserGroup',
                        'Id' => $tblGroup->getId(),
                        'receiverTableUserGroupList' => $receiverTableUserGroupList,
                        'receiverEditUserGroup' => $receiverEditGroup->getIdentifier()
                    ));
                    $pipelineEditGroup->addEmitter($emitterEditGroup);

                    /**
                     * Data
                     */
                    $Group = $tblGroup->__toArray();
                    $Group['Option'] =
                        (new Standard('', __CLASS__, new Edit()))->ajaxPipelineOnClick($pipelineEditGroup)->__toString()
                        . (new Standard('', __CLASS__, new Remove()))->ajaxPipelineOnClick($pipelineDestroyGroupConfirm)->__toString()
                        . new Standard('', '#', new Setup());
                    $TableList[] = $Group;
                });
        }

        return new TableData(

            $TableList
            , null, array(
            'Name' => 'Name',
            'Description' => 'Beschreibung',
            'Role' => 'Rechte',
            'Member' => 'Benutzer',
            'Option' => ''
        ), array(
            "columnDefs" => array(
                array("searchable" => false, "targets" => -1),
                array("type" => "natural", "targets" => '_all')
            )
        )) . $receiverDestroyGroup . $receiverDestroyGroupClose . $receiverEditGroup;
    }

    /**
     * @return Route
     */
    public static function getRoute()
    {
        return new Route(__CLASS__);
    }

    public function pieceTableMemberGroup()
    {
        return new TableData(array());
    }

    public function pieceTableMemberAvailable()
    {
        return new TableData(array());
    }

    public function addUserToGroup($GroupId, $UserId)
    {
        $Pipeline = new Pipeline();

        return $Pipeline;
    }

    public function removeUserFromGroup($GroupId, $UserId)
    {
        $Pipeline = new Pipeline();

        return $Pipeline;
    }

    public function createUserGroup($Group, $receiverTableUserGroupList, $receiverCreateUserGroup)
    {

        /**
         * Create Group
         */
        $pipelineCreateUserGroup = new Pipeline();
        $emitterCreateUserGroup = new ServerEmitter((new ModalReceiver())->setIdentifier($receiverCreateUserGroup),
            ApiUserGroup::getRoute());
        $emitterCreateUserGroup->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'createUserGroup',
            'receiverTableUserGroupList' => $receiverTableUserGroupList,
            'receiverCreateUserGroup' => $receiverCreateUserGroup
        ));
        $pipelineCreateUserGroup->addEmitter($emitterCreateUserGroup);

        /**
         * Reload Group-List
         */
        $emitterTableUserGroupList = new ServerEmitter((new BlockReceiver())->setIdentifier($receiverTableUserGroupList),
            ApiUserGroup::getRoute());
        $emitterTableUserGroupList->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'pieceTableUserGroupList',
            'receiverTableUserGroupList' => $receiverTableUserGroupList
        ));
        $pipelineCreateUserGroup->addEmitter($emitterTableUserGroupList);

        return new Well(Group::useService()->createGroup(
            (new ApiUserGroup())
                ->formUserGroup()
                ->ajaxPipelineOnSubmit($pipelineCreateUserGroup)
            , $Group
        ));
    }


    public function editUserGroup($Id, $Group, $receiverTableUserGroupList, $receiverEditUserGroup)
    {
        // todo: Edit

        /**
         * Edit Group
         */
        $pipelineEditUserGroup = new Pipeline();
        $emitterEditUserGroup = new ServerEmitter((new ModalReceiver())->setIdentifier($receiverEditUserGroup),
            ApiUserGroup::getRoute());
        $emitterEditUserGroup->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'editUserGroup',
            'Id' => $Id,
            'receiverTableUserGroupList' => $receiverTableUserGroupList,
            'receiverEditUserGroup' => $receiverEditUserGroup
        ));
        $pipelineEditUserGroup->addEmitter($emitterEditUserGroup);

//        /**
//         * Reload Group-List
//         */
//        $emitterTableUserGroupList = new ServerEmitter((new BlockReceiver())->setIdentifier($receiverTableUserGroupList),
//            ApiUserGroup::getRoute());
//        $emitterTableUserGroupList->setGetPayload(array(
//            ApiUserGroup::API_DISPATCHER => 'pieceTableUserGroupList',
//            'receiverTableUserGroupList' => $receiverTableUserGroupList
//        ));
//        $pipelineEditUserGroup->addEmitter($emitterTableUserGroupList);

        $tblGroup = Account::useService()->getGroupById( $Id );

        $Global = $this->getGlobal();
        $Global->POST['Group'] = $tblGroup->__toArray();
        $Global->savePost();

        return new Well(Group::useService()->editGroup(
            (new ApiUserGroup())
                ->formUserGroup()
                ->ajaxPipelineOnSubmit($pipelineEditUserGroup)
            , $tblGroup, $Group
        ));
    }

    /**
     * @return Form
     */
    public function formUserGroup()
    {
        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Group[Name]', 'Gruppenname', 'Gruppenname'))->setAutoFocus()
                    ),
                    new FormColumn(
                        (new TextArea('Group[Description]', 'Gruppenbeschreibung',
                            'Gruppenbeschreibung'))->setMaxLengthValue(200)
                    ),
                ))
            ), new Primary('Speichern', new Save())
        );
    }

    public function destroyUserGroup($Id)
    {
        if (($tblGroup = Account::useService()->getGroupById($Id))) {
            Account::useService()->destroyGroup($tblGroup);
            return true;
        }
        return false;
    }
}
