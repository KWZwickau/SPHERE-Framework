<?php
namespace SPHERE\Application\Api\Billing\Inventory;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Danger as DangerLink;
use SPHERE\Common\Frontend\Link\Repository\Primary;
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
        $Dispatcher->registerMethod('saveAddItem');
        $Dispatcher->registerMethod('saveEditItem');
        $Dispatcher->registerMethod('showEditItem');
        // reload Frontend Info
        $Dispatcher->registerMethod('getItemTable');

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
     * @param string $Content
     * @return BlockReceiver
     */
    public static function receiverItemTable($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('BlockTableContent');
    }

    /**
     * @param string $Identifier
     * @param array  $Item
     * @param array  $Group
     *
     * @return Pipeline
     */
    public static function pipelineOpenAddItemModal($Identifier = '', $Item = array(), $Group = array())
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'showAddItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
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
            ApiItem::API_TARGET => 'saveAddItem'
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
     * @param array      $Item
     * @param array      $Group
     *
     * @return Pipeline
     */
    public static function pipelineOpenEditItemModal($Identifier = '', $ItemId = '',$Item = array(), $Group = array())
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
            'Item'       => $Item,
            'Group'      => $Group,
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
    public static function pipelineSaveEditItem($Identifier = '', $ItemId = '')
    {

        $Receiver = self::receiverModal(null, $Identifier);
        $Pipeline = new Pipeline(true);
        $Emitter = new ServerEmitter($Receiver, ApiItem::getEndpoint());
        $Emitter->setGetPayload(array(
            ApiItem::API_TARGET => 'saveEditItem'
        ));
        $Emitter->setPostPayload(array(
            'Identifier' => $Identifier,
            'ItemId' => $ItemId
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

        return $Pipeline;
    }

    /**
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineCloseModal($Identifier = '')
    {
        $Pipeline = new Pipeline();
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverItemTable(''), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getItemTable'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal('', $Identifier)))->getEmitter());
        return $Pipeline;
    }

    public function getItemTable()
    {

        return Item::useFrontend()->getItemTable();
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return Form
     */
    public function formItem($Identifier = '', $ItemId = '')
    {

        $CheckboxList = array();
        $tblGroupList = array();

        // choose between Add and Edit
        $SaveButton =new Primary('Speichern', ApiItem::getEndpoint(), new Save());
        if('' !== $ItemId /* && ($tblItem = Item::useService()->getItemById($ItemId)) */){
            $SaveButton->ajaxPipelineOnClick(ApiItem::pipelineSaveEditItem($Identifier, $ItemId));
        } else {
            $SaveButton->ajaxPipelineOnClick(ApiItem::pipelineSaveAddItem($Identifier));
        }

        // get all possible Person Groups
        if(($tblSettingGroupPersonAll = Setting::useService()->getSettingGroupPersonAll())){
            foreach($tblSettingGroupPersonAll as $tblSettingGroupPerson){
                if(($tblGroup = $tblSettingGroupPerson->getServiceTblGroupPerson())){
                    $tblGroupList[] = $tblGroup;
                }
            }
        }
        if(($tblGroupList = $this->getSorter($tblGroupList)->sortObjectBy('Name'))){
            /** @var TblGroup $tblGroup */
            foreach($tblGroupList as $tblGroup){
                $CheckboxList[] = new CheckBox('Group['.$tblGroup->getId().']', $tblGroup->getName(), $tblGroup->getId());
            }
        }


        return (new Form(
            new FormGroup(
                new FormRow(array(
                    new FormColumn(
                        (new TextField('Item[Name]', 'Beitragsart', 'Beitragsart'))->setRequired()
                        , 6),
                    new FormColumn(
                        $CheckboxList
                        , 6),
//                    new FormColumn(
//                        new TextField('Item[Description]', 'Beschreibung', 'Beschreibung')
//                    , 6),
                    new FormColumn(
                        $SaveButton
                    )
                ))
            )
        ))->disableSubmitAction();
    }

    /**
     * @param string $Identifier
     * @param string $ItemId
     * @param array $Item
     * @param array $Group
     *
     * @return false|string|Form
     */
    private function checkInputItem($Identifier = '', $ItemId = '',$Item = array(), $Group = array())
    {
        $Error = false;
        $form = $this->formItem($Identifier, $ItemId);
        $Warning = '';
        if (isset($Item['Name']) && empty($Item['Name'])) {
            $form->setError('Item[Name]', 'Bitte geben Sie den Namen der Beitragsart an');
            $Error = true;
            // disable save for duplicated names
        } elseif(isset($Item['Name']) && ($tblItem = Item::useService()->getItemByName($Item['Name']))){
            // ignore own Name
            if($tblItem->getId() != $ItemId){
                $form->setError('Item[Name]','Beitragsart exisitiert bereits, sie darf nicht doppelt angelegt werden');
                $Error = true;
            }
        }
        if (empty($Group)) {
            $Warning = 'Bitte geben Sie mindestens eine Personengruppe an';
            $form->setError('Group[1]', 'Bitte geben Sie eine Hausnummer an');
            $Error = true;
        }
        if(empty($Group)){

            $Error = true;
        }


        if ($Error) {
            if($Warning){
                return new Danger($Warning).$form;
            } else {
                return $form;
            }
        }

        return $Error;
    }

    /**
     * @param string $Identifier
     * @param array  $Warning
     *
     * @return string
     */
    public function showAddItem($Identifier = '')
    {

        return $this->formItem($Identifier);
    }

    /**
     * @param string $Identifier
     * @param array  $Item
     * @param array  $Group
     *
     * @return string
     */
    public function saveAddItem($Identifier = '', $Item = array(), $Group = array())
    {

        // Handle error's
        if ($form = $this->checkInputItem($Identifier, '', $Item, $Group)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Item']['Name'] = $Item['Name'];
            $Global->POST['Group'] = $Group;
            $Global->savePost();
            return $form;
        }

        if(($tblItem = Item::useService()->createItem($Item))){
            foreach($Group as $GroupId){
                if(($tblGroup = Group::useService()->getGroupById($GroupId))){
                    Item::useService()->createItemGroup($tblItem, $tblGroup);
                }
            }
        }

        return ($Item
                    ? new Success('Beitragsart erfolgreich angelegt'). self::pipelineCloseModal($Identifier)
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
    public function saveEditItem($Identifier = '', $ItemId = '',$Item = array(), $Group = array())
    {

        // Handle error's
        if ($form = $this->checkInputItem($Identifier, $ItemId, $Item, $Group)) {
            // display Errors on form
            $Global = $this->getGlobal();
            $Global->POST['Item']['Name'] = $Item['Name'];
            $Global->POST['Group'] = $Group;
            $Global->savePost();
            return $form;
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
                    ? new Success('Beitragsart erfolgreich angelegt') . self::pipelineCloseModal($Identifier)
                    : new Danger('Beitragsart konnte nicht gengelegt werden'));
    }

    /**
     * @param string     $Identifier
     * @param int|string $ItemId
     *
     * @return string
     */
    public function showEditItem($Identifier = '', $ItemId = '')
    {

        if('' !== $ItemId && ($tblItem = Item::useService()->getItemById($ItemId))){
            $Global = $this->getGlobal();
            $Global->POST['Item']['Name'] = $tblItem->getName();
            if(($tblItemGroupList = Item::useService()->getItemGroupByItem($tblItem))){
                foreach($tblItemGroupList as $tblItemGroup){
                    if(($tblGroup = $tblItemGroup->getServiceTblGroup())){
                        $Global->POST['Group'][$tblGroup->getId()] = $tblGroup->getId();
                    }
                }
            }
            $Global->savePost();
        }

        return self::formItem($Identifier, $ItemId);
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

    /**
     * @param string $Identifier
     * @param string $ItemId
     * @return string
     */
    public function deleteItem($Identifier = '', $ItemId = '')
    {

        if(($tblItem = Item::useService()->getItemById($ItemId))){
            Item::useService()->removeItem($tblItem);

            return new Success('Beitragsart wurde erfolgreich entfernt'). self::pipelineCloseModal($Identifier);
        }
        return new Danger('Beitragsart konnte nicht entfernt werden');
    }

}