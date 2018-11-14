<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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
        $Dispatcher->registerMethod('showAddItem');
        $Dispatcher->registerMethod('addItem');
        $Dispatcher->registerMethod('editItem');
        $Dispatcher->registerMethod('showEditItem');
        $Dispatcher->registerMethod('showAddVariant');
        $Dispatcher->registerMethod('showDeleteItem');
        $Dispatcher->registerMethod('deleteItem');
//        $Dispatcher->registerMethod('changeEditItem');
//        $Dispatcher->registerMethod('doDeleteItem');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Header
     * @param string $Identifier
     *
     * @return ModalReceiver
     */
    public static function receiverModal($Header = '', $Identifier = '')
    {

        return (new ModalReceiver($Header,  new Close()))->setIdentifier('Modal'.$Identifier);
    }

    /**
     * @param string $Identifier
     * @param array  $Warning
     * @param array  $Item
     * @param array  $Group
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddItemModal($Identifier = '', $Warning = array(), $Item = array(), $Group = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showAddItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'Warning' => $Warning,
            'Item' => $Item,
            'Group' => $Group
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineSaveAddItem($Identifier = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'addItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier
        ));
        $Pipeline->appendEmitter($Emitter);
        // Close Modal
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        //ToDO reload Table

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditItem($Identifier = '', $ItemId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'editItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId' => $ItemId
        ));
        $Pipeline->appendEmitter($Emitter);
        // Close Modal
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        //ToDO reload Table

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param array      $Warning
     * @param array      $Item
     * @param array      $Group
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditItemModal($Identifier = '', $ItemId = '', $Warning = array(), $Item = array(), $Group = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showEditItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId,
            'Warning'    => $Warning,
            'Item'       => $Item,
            'Group'      => $Group,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddVariantModal($Identifier = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showAddVariant'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineOpenDeleteItemModal($Identifier = '', $ItemId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showDeleteItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Pipeline
     */
    public static function pipelineDeleteItem($Identifier = '', $ItemId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'deleteItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId'     => $ItemId,
        ));
        $Pipeline->appendEmitter($Emitter);
        //ToDO reload Table

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     * @param array  $Warning
     *
     * @return string
     */
    public function showAddItem($Identifier = '', $Warning = array())
    {

        if(!empty($Warning)){
            foreach($Warning as &$Entry){
                $Entry = new Danger($Entry);
            }
        }

        return implode($Warning)
            .Item::useFrontend()->formItem($Identifier);
    }

    /**
     * @param string $Identifier
     * @param array  $Item
     * @param array  $Group
     *
     * @return string
     */
    public function addItem($Identifier = '', $Item = array(), $Group = array())
    {

        $Error = false;
        $Warning = array();
        if($Item['Name'] === ''){
            $Warning[] = 'Bitte geben Sie den Namen der Beitragsart an';
            $Error = true;
        } elseif(Item::useService()->getItemByName($Item['Name'])){
            $Warning[] = 'Beitragsart exisitiert bereits, sie darf nicht doppelt angelegt werden';
            $Error = true;
        }
        if(empty($Group)){

            $Warning[] = 'Bitte geben Sie mindestens eine Personengruppe an';
            $Error = true;
        }
        if($Error){
            return self::pipelineOpenAddItemModal($Identifier, $Warning, $Item, $Group);
        }

        if(($tblItem = Item::useService()->createItem($Item))){
            foreach($Group as $GroupId){
                if(($tblGroup = Group::useService()->getGroupById($GroupId))){
                    Item::useService()->createItemGroup($tblItem, $tblGroup);
                }
            }
        }

        return ($Item
                    ? new Success('Beitragsart erfolgreich angelegt')
                    : new Danger('Beitragsart konnte nicht gengelegt werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param array      $Item
     * @param array      $Group
     *
     * @return string
     */
    public function editItem($Identifier = '', $ItemId = '',$Item = array(), $Group = array())
    {

        $Error = false;
        $Warning = array();
        if($Item['Name'] === ''){
            $Warning[] = 'Bitte geben Sie den Namen der Beitragsart an';
            $Error = true;
        } elseif(($tblItem = Item::useService()->getItemByName($Item['Name'])) && $tblItem->getId() != $ItemId){
            $Warning[] = 'Beitragsart exisitiert bereits, sie darf nicht doppelt angelegt werden';
            $Error = true;
        }
        if(empty($Group)){

            $Warning[] = 'Bitte geben Sie mindestens eine Personengruppe an';
            $Error = true;
        }
        if($Error){
            return self::pipelineOpenEditItemModal($Identifier, $ItemId, $Warning, $Item, $Group);
        }

        if(($tblItem = Item::useService()->getItemById($ItemId))){
            Item::useService()->changeItem($tblItem, $Item['Name']);

            // Delete existing PersonGroup
            //ToDO only remove not necessary Entry's
            if(($tblItemGroupList = Item::useService()->getItemGroupByItem($tblItem))){
                foreach($tblItemGroupList as $tblItemGroup){
                    Item::useService()->removeItemGroup($tblItemGroup);
                }
            }

            foreach($Group as $GroupId){
                if(($tblGroup = Group::useService()->getGroupById($GroupId))){
                    Item::useService()->createItemGroup($tblItem, $tblGroup);
                }
            }
        }

        return ($Item
                    ? new Success('Beitragsart erfolgreich angelegt')
                    : new Danger('Beitragsart konnte nicht gengelegt werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     * @param array      $Warning
     *
     * @return string
     */
    public function showEditItem($Identifier = '', $ItemId = '', $Warning = array())
    {

        if(!empty($Warning)){
            foreach($Warning as &$Entry){
                $Entry = new Danger($Entry);
            }
        }

        return implode($Warning)
            .Item::useFrontend()->formItem($Identifier, $ItemId);
    }

    /**
     * @return string
     */
    public function showAddVariant()
    {

        return 'Test showAddVariant';
    }

    /**
     * @param string $Identifier
     * @param string $ItemId
     *
     * @return string
     */
    public function showDeleteItem($Identifier = '', $ItemId = '')
    {

        $tblItem = Item::useService()->getItemById($ItemId);
        $GroupArray = array();
        if($tblItem){
            if(($tblGroupPersonList = Item::useService()->getItemGroupByItem($tblItem))){
                foreach($tblGroupPersonList as $tblGroupPerson){
                    if(($tblGroup = $tblGroupPerson->getServiceTblGroup())){
                        $GroupArray[] = $tblGroup->getName();
                    }
                }
            }

            $Content[] = new Bold('Beitragsart: '. $tblItem->getName());
            $Content[] ='hinterlegte Personengruppen: '. new Bold(implode(', ', $GroupArray));

            return new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            new Panel('Soll die Beitragsart wirklich entfernt werden?'
                                , new Listing($Content))
                        ),
                        new LayoutColumn(
                            (new DangerLink('Ja', self::getEndpoint(), new Ok()))
                            ->ajaxPipelineOnClick(self::pipelineDeleteItem($Identifier, $ItemId))
                            .new Close('Nein', new Disable())
                        )
                    ))
                )
            );

        } else {
            return new Warning('Beitragsart wurde nicht gefunden');
        }
    }

    public function deleteItem($ItemId = '')
    {

        if(($tblItem = Item::useService()->getItemById($ItemId))){
            Item::useService()->removeItem($tblItem);

            return new Success('Beitragsart wurde erfolgreich entfernt');
        }
        return new Danger('Beitragsart konnte nicht entfernt werden');
    }

}