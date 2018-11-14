<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Api\Billing\Inventory\ApiItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Save;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Inventory\Item
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendItem()
    {

        $Stage = new Stage('Beitragsart', 'Übersicht');
        $Stage->addButton((new Primary('Beitragsart hinzufügen', ApiItem::getEndpoint(), new Plus()))
            ->ajaxPipelineOnClick(ApiItem::pipelineOpenAddItemModal('AddItem')));

        //ToDO Table as receiver Content
        $tblItemAll = Item::useService()->getItemAll();
        $TableContent = array();
        if (!empty( $tblItemAll )) {
            array_walk($tblItemAll, function (TblItem $tblItem) use (&$TableContent) {

                $Item['Name'] = $tblItem->getName();
                $Item['PersonGroup'] = '';
//                $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                $Item['Variant'] = '';

                $Item['Option'] =
                    (new Standard('', ApiItem::getEndpoint(), new Edit(), array(), 'Bearbeiten'))
                    ->ajaxPipelineOnClick(ApiItem::pipelineOpenEditItemModal('EditItem', $tblItem->getId()))
                    .(new Standard('', ApiItem::getEndpoint(), new Plus(), array(), 'Varianten hinzufügen'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenAddVariantModal('AddVariant'))
                    .(new Standard('', ApiItem::getEndpoint(), new Remove(), array(), 'Löschen'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenDeleteItemModal('deleteItem', $tblItem->getId()));

                $GroupList = array();
                if(($PersonGroupList = Item::useService()->getItemGroupByItem($tblItem))){
                    foreach($PersonGroupList as $PersonGroup){
                        if(($tblGroup = $PersonGroup->getServiceTblGroup())){
                            $GroupList[] = $tblGroup->getName();
                        }
                    }
                }
                if(!empty($GroupList)){
                    $Item['PersonGroup'] = implode(', ', $GroupList);
                }

                array_push($TableContent, $Item);
            });
        }

        $Stage->setContent(
            ApiItem::receiverModal('Anlegen einer neuen Beitragsart', 'AddItem')
            .ApiItem::receiverModal('Beitragsart bearbeiten', 'EditItem')
            .ApiItem::receiverModal('Anlegen einer neuen Beitrags-Variante', 'AddVariant')
            .ApiItem::receiverModal('Entfernen einer Beitragsart', 'deleteItem')
            .new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            new TableData($TableContent, null,
                                array(
                                    'Name'        => 'Name',
                                    'PersonGroup' => 'zugewiesene Personengruppen',
                                    'Variant'     => 'Preis-Varianten',
                                    'Option'      => ''
                                )
                            )
                        )
                    ), new Title(new ListingTable().' Übersicht')
                ))
            )
        );

        return $Stage;
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
            $SaveButton =(new Primary('Speichern', ApiItem::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiItem::pipelineSaveEditItem($Identifier, $ItemId));
        } else {
            $SaveButton =(new Primary('Speichern', ApiItem::getEndpoint(), new Save()))
                ->ajaxPipelineOnClick(ApiItem::pipelineSaveAddItem($Identifier));
        }

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
}
