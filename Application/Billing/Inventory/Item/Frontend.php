<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Api\Billing\Inventory\ApiItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
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
            ->ajaxPipelineOnClick(ApiItem::pipelineOpenAddItemModal('addItem')));

        $Stage->setContent(
            ApiItem::receiverModal('Anlegen einer neuen Beitragsart', 'addItem')
            .ApiItem::receiverModal('Beitragsart bearbeiten', 'editItem')
            .ApiItem::receiverModal('Entfernen einer Beitragsart', 'deleteItem')
            .ApiItem::receiverModal('Anlegen einer neuen Beitrags-Variante', 'addVariant')
            .ApiItem::receiverModal('Entfernen einer Beitragsart', 'editVariant')
            .new Layout(
                new LayoutGroup(array(
                    new LayoutRow(
                        new LayoutColumn(
                            ApiItem::receiverItemTable($this->getItemTable())
                        )
                    ), new Title(new ListingTable().' Übersicht')
                ))
            )
        );

        return $Stage;
    }

    /**
     * @return TableData
     */
    public function getItemTable()
    {

        $tblItemAll = Item::useService()->getItemAll();
        $TableContent = array();
        if (!empty($tblItemAll)) {
            array_walk($tblItemAll, function (TblItem $tblItem) use (&$TableContent) {

                $Item['Name'] = $tblItem->getName()
                .(new Link('', ApiItem::getEndpoint(), new Pencil(), array(), 'Bearbeiten der Beitragsart'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenEditItemModal('editItem', $tblItem->getId()))
                .'|'
                .(new Link(new DangerText(new Disable()), ApiItem::getEndpoint(), null, array(), 'Löschen der Beitragsart'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenDeleteItemModal('deleteItem', $tblItem->getId()));
                ;
                $Item['PersonGroup'] = '';
//                $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                $Item['Variant'] = '';

                $GroupList = array();
                if (($PersonGroupList = Item::useService()->getItemGroupByItem($tblItem))) {
                    foreach ($PersonGroupList as $PersonGroup) {
                        if (($tblGroup = $PersonGroup->getServiceTblGroup())) {
                            $GroupList[] = $tblGroup->getName();
                        }
                    }
                    sort($GroupList);
                }
                if (!empty($GroupList)) {
                    $Item['PersonGroup'] = new Listing($GroupList);
                }

                $RowList = array();
                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))){
                    foreach($tblItemVariantList as $tblItemVariant){
                        $Row = $tblItemVariant->getName().
                            (new Link('', '', new Pencil()))
                            ->ajaxPipelineOnClick(ApiItem::pipelineOpenEditVariantModal('editVariant', $tblItem->getId(), $tblItemVariant->getId()))
                            .'|'.
                            new Link(new DangerText(new Disable()), '')
                            .'<br/>'.$tblItemVariant->getDescription();
                        if(($tblItemCalculationList = Item::useService()->getItemCalculationByItem($tblItemVariant))){
                            foreach($tblItemCalculationList as $tblItemCalculation){
                                $Row .= '<br/>&nbsp;&nbsp;&nbsp;&nbsp;Preis: '.$tblItemCalculation->getPriceString().'&nbsp;&nbsp;&nbsp;&nbsp;'.'PlatzhalterDatumVon - PlatzhalterDatumBis '
                                .new Link('', '', new Pencil()).'|'.new Link(new DangerText(new Disable()), '');
//                                    .$tblItemCalculation->getDateFrom().' - '.$tblItemCalculation->getDateTo();
                            }
                            $Row .= '<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.new Link('Preis hinzufügen', '', new Plus());
                        }
                        $RowList[] = $Row;
                    }
                }

                $RowList[] = (new Link('Variante hinzufügen', '', new Plus()))
                    ->ajaxPipelineOnClick(ApiItem::pipelineOpenAddVariantModal('addVariant', $tblItem->getId()));

                $Item['Variant'] = new Listing($RowList);

                array_push($TableContent, $Item);
            });
        }

        return new TableData($TableContent, null,
            array(
                'Name'        => 'Name',
                'PersonGroup' => 'Personengruppen',
                'Variant'     => 'Preis-Varianten',
//                'Option'      => ''
            )
        );
    }
}
