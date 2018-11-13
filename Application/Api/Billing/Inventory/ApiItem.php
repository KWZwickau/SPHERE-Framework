<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiItem
 * @package SPHERE\Application\Api\Billing\Inventory
 */
class ApiItem extends Extension implements IApiInterface
{

    // registered method
    // ToDO Constanten überflüssig, wenn mehrere Modalreceiver sowie Pipelines vorhanden sind
    const MODAL_SHOW_EDIT_ITEM = 'showEditItem';
    const MODAL_SHOW_ADD_VARIANT = 'showAddVariant';
    const MODAL_SHOW_DELETE_ITEM = 'showDeleteItem';

    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod(self::MODAL_SHOW_EDIT_ITEM);
        $Dispatcher->registerMethod(self::MODAL_SHOW_ADD_VARIANT);
        $Dispatcher->registerMethod(self::MODAL_SHOW_DELETE_ITEM);
//        $Dispatcher->registerMethod('changeEditItem');
//        $Dispatcher->registerMethod('doDeleteItem');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        //ToDO möglicherweise mehrere Receiver um die verschiedenen Modalheader umzusetzen
        return (new ModalReceiver(null,  new Close()))->setIdentifier('ModalIdentifier');
    }

    /**
     * @param string $ApiTarget
     * @return Pipeline
     */
    public static function pipelineOpenModal($ApiTarget = '')
    {

        //ToDO Werden verschiedene reciever benutzt, so kann man auch unterschiedliche Pipelines nutzen um flexiebler zu bleiben
        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => $ApiTarget
        ));

//        $ComparePasswordEmitter->setLoadingMessage('Information gespeichert.');
//        $Emitter->setPostPayload(array());
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function showEditItem()
    {

        return 'Test showEditItem';
    }

    /**
     * @return string
     */
    public function showAddVariant()
    {

        return 'Test showAddVariant';
    }

    /**
     * @return string
     */
    public function showDeleteItem()
    {

        return 'Test showDeleteItem';
    }

}