<?php
namespace SPHERE\Application\Api\Platform\Gatekeeper;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblGroup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Consumer;
use SPHERE\Application\Setting\Authorization\Group\Group;
use SPHERE\Common\Frontend\Ajax\Emitter\ClientEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
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
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Link\Repository\Success;
use SPHERE\Common\Frontend\Link\Structure\LinkGroup;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;

/**
 * Class ApiUserGroup
 *
 * @package SPHERE\Application\Api\Platform\Gatekeeper
 */
class ApiUserGroup implements IApiInterface
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
        $Dispatcher->registerMethod('destroyUserGroup');

        return $Dispatcher->callMethod($MethodName);
    }

    public function pieceTableUserGroupList($receiverTableUserGroupList)
    {

        $tblGroupAll = Account::useService()->getGroupAll(Consumer::useService()->getConsumerBySession());

        $receiverDestroyGroup = new ModalReceiver( 'Sind Sie sicher?', new Close() );

        $TableList = array();
        array_walk( $tblGroupAll, function( TblGroup $tblGroup ) use( &$TableList, $receiverTableUserGroupList, $receiverDestroyGroup ) {


            $pipelineDestroyGroup = new Pipeline();
            $emitterDestroyGroup = new ServerEmitter( new BlockReceiver(), ApiUserGroup::getRoute() );
            $emitterDestroyGroup->setGetPayload(array(
                ApiUserGroup::API_DISPATCHER => 'destroyUserGroup',
                'Id' => $tblGroup->getId()
            ));
            $pipelineDestroyGroup->addEmitter( $emitterDestroyGroup );

            $emitterTableUserGroupList = new ServerEmitter((new BlockReceiver())->setIdentifier($receiverTableUserGroupList), ApiUserGroup::getRoute());
            $emitterTableUserGroupList->setGetPayload(array(
                ApiUserGroup::API_DISPATCHER => 'pieceTableUserGroupList',
                'receiverTableUserGroupList' => $receiverTableUserGroupList
            ));
//            $pipelineDestroyGroup->addEmitter($emitterTableUserGroupList);

            $pipelineDestroyGroupConfirm = new Pipeline();
            $emitterDestroyGroupConfirm = new ClientEmitter( $receiverDestroyGroup, new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn('Wollen Sie die Gruppe '.$tblGroup->getName().' wirklich löschen?')
                    ),
                    new LayoutRow(
                        new LayoutColumn(array(
                            (new Standard('Ja','#', new Enable()))->ajaxPipelineOnClick( $pipelineDestroyGroup ),
                            new Close('Nein', new Disable())
                        ))
                    ),
                ))
            ));
            $pipelineDestroyGroupConfirm->addEmitter( $emitterDestroyGroupConfirm );

            $Group = $tblGroup->__toArray();
            $Group['Option'] = (new LinkGroup())
                        ->addLink(new Standard('', '#', new Edit()))
                        ->addLink(
                            (new Standard('', '#', new Remove()))->ajaxPipelineOnClick( $pipelineDestroyGroupConfirm )
                        )
                    . new Standard('', '#', new Setup());
            $TableList[] = $Group;
        });

        return new TableData(
//            array(array(
//                'Name' => 'Gruppenname',
//                'Description' => 'Gruppenbeschreibung',
//                'Role' => 'Zugriffsrechte',
//                'Member' => 'Benutzer',
//                'Option' => (new LinkGroup())
//                        ->addLink(new Standard('', '#', new Edit()))
//                        ->addLink(new Standard('', '#', new Remove()))
//                    . new Standard('', '#', new Setup())
//            ))
            $TableList
            , null, array(
            'Name' => 'Gruppenname',
            'Description' => 'Gruppenbeschreibung',
            'Role' => 'Zugriffsrechte',
            'Member' => 'Benutzer',
            'Option' => ''
        )).$receiverDestroyGroup;
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
        $pipelineTableUserGroupList = new Pipeline();
        $emitterTableUserGroupList = new ServerEmitter((new BlockReceiver())->setIdentifier($receiverTableUserGroupList), ApiUserGroup::getRoute());
        $emitterTableUserGroupList->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'pieceTableUserGroupList',
            'receiverTableUserGroupList' => $receiverTableUserGroupList
        ));
        $pipelineTableUserGroupList->addEmitter($emitterTableUserGroupList);

        $pipelineCreateUserGroup = new Pipeline();
        $emitterCreateUserGroup = new ServerEmitter((new BlockReceiver())->setIdentifier($receiverCreateUserGroup), ApiUserGroup::getRoute());
        $emitterCreateUserGroup->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'createUserGroup',
            'receiverTableUserGroupList' => $receiverTableUserGroupList,
            'receiverCreateUserGroup' => $receiverCreateUserGroup
        ));
        $pipelineCreateUserGroup->addEmitter($emitterCreateUserGroup);

        return Group::useService()->createGroup(
                (new ApiUserGroup())
                    ->formCreateUserGroup()
                    ->ajaxPipelineOnSubmit($pipelineCreateUserGroup)
                , $Group
            )
            . $pipelineTableUserGroupList;
    }

    public function destroyUserGroup()
    {
        return "Wech";
    }

    /**
     * @return Route
     */
    public static function getRoute()
    {
        return new Route(__CLASS__);
    }

    /**
     * @return Form
     */
    public function formCreateUserGroup()
    {
        return new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Group[Name]', 'Gruppenname', 'Gruppenname'))->setRequired()
                    ),
                    new FormColumn(
                        (new TextArea('Group[Description]', 'Gruppenbeschreibung',
                            'Gruppenbeschreibung'))->setMaxLengthValue(200)
                    ),
                ))
            ), new Primary('Speichern', new Save())
        );
    }
}