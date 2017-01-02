<?php
namespace SPHERE\Application\Setting\Authorization\Group;

use SPHERE\Application\Api\Platform\Gatekeeper\ApiUserGroup;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\PersonGroup;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\Authorization\Group
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendUserGroup()
    {
        $Stage = new Stage('Benutzergruppen');
        $Stage->setMessage('');

        /**
         * Show Table UserGroup
         */
        $pipelineTableUserGroupList = new Pipeline();
        $receiverTableUserGroupList = new BlockReceiver();
        $emitterTableUserGroupList = new ServerEmitter($receiverTableUserGroupList, ApiUserGroup::getRoute());
        $emitterTableUserGroupList->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'pieceTableUserGroupList',
            'receiverTableUserGroupList' => $receiverTableUserGroupList->getIdentifier()
        ));
        $pipelineTableUserGroupList->addEmitter($emitterTableUserGroupList);
        $receiverTableUserGroupList->initContent($pipelineTableUserGroupList);

        /**
         * Create New UserGroup
         */
        $pipelineCreateUserGroup = new Pipeline();
        $receiverCreateUserGroup = new BlockReceiver();
        $emitterCreateUserGroup = new ServerEmitter($receiverCreateUserGroup, ApiUserGroup::getRoute());
        $emitterCreateUserGroup->setGetPayload(array(
            ApiUserGroup::API_DISPATCHER => 'createUserGroup',
            'receiverTableUserGroupList' => $receiverTableUserGroupList->getIdentifier(),
            'receiverCreateUserGroup' => $receiverCreateUserGroup->getIdentifier()
        ));
        $pipelineCreateUserGroup->addEmitter($emitterCreateUserGroup);
        $receiverCreateUserGroup->initContent(
            (new ApiUserGroup())->formCreateUserGroup()->ajaxPipelineOnSubmit($pipelineCreateUserGroup)
        );

        $Stage->setContent(
            new Layout(array(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            $receiverTableUserGroupList
                        )
                    ),
                ), new Title(new PersonGroup() . ' Bestehende Benutzergruppen')),
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new Well(
                                $receiverCreateUserGroup
                            )
                        )
                    ),
                ), new Title(new PlusSign() . ' Neue Benutzergruppe anlegen')),
            ))
        );

        return $Stage;
    }
}
