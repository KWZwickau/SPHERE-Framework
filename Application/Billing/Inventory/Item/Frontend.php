<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Api\Billing\Inventory\ApiItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemCalculation;
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
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Extension\Repository\Sorter;
use SPHERE\System\Extension\Repository\Sorter\DateTimeSorter;

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
            ApiItem::receiverModal('Beitragsart hinzufügen', 'addItem')
            .ApiItem::receiverModal('Beitragsart bearbeiten', 'editItem')
            .ApiItem::receiverModal('Beitragsart entfernen', 'deleteItem')
            .ApiItem::receiverModal('Beitrags-Variante hinzufügen', 'addVariant')
            .ApiItem::receiverModal('Beitrags-Variante bearbeiten', 'editVariant')
            .ApiItem::receiverModal('Beitrags-Variante entfernen', 'deleteVariant')
            .ApiItem::receiverModal('Preis hinzufügen', 'addCalculation')
            .ApiItem::receiverModal('Preis bearbeiten', 'editCalculation')
            .ApiItem::receiverModal('Preis entfernen', 'deleteCalculation')
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
//                    $Item['PersonGroup'] = new Listing($GroupList);
                    $Item['PersonGroup'] = implode('<br/>', $GroupList);
                }

                $RowList = array();
                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))){
                    foreach($tblItemVariantList as $tblItemVariant){
                        $Row = $tblItemVariant->getName().
                            (new Link('', ApiItem::getEndpoint(), new Pencil()))
                            ->ajaxPipelineOnClick(ApiItem::pipelineOpenEditVariantModal('editVariant', $tblItem->getId(), $tblItemVariant->getId()))
                            .'|'.
                            (new Link(new DangerText(new Disable()), ApiItem::getEndpoint()))
                            ->ajaxPipelineOnClick(ApiItem::pipelineOpenDeleteVariantModal('deleteVariant', $tblItemVariant->getId()))
                            .($tblItemVariant->getDescription() ? '<br/>'.$tblItemVariant->getDescription() : '');

                        $PriceAddButton = (new Link('Preis hinzufügen', ApiItem::getEndpoint(), new Plus()))
                            ->ajaxPipelineOnClick(ApiItem::pipelineOpenAddCalculationModal('addCalculation', $tblItemVariant->getId()));

                        if(($tblItemCalculationList = Item::useService()->getItemCalculationByItemVariant($tblItemVariant))){
                            /** @var TblItemCalculation[] $tblItemCalculationList */
                            $tblItemCalculationList = $this->getSorter($tblItemCalculationList)->sortObjectBy('DateFrom', new DateTimeSorter(), Sorter::ORDER_DESC);

                            $Row .= '<table>';
                            foreach($tblItemCalculationList as $tblItemCalculation){

                                //ToDO aktuellen Eintrag markieren
                                $IsNow = false;
                                if(new \DateTime($tblItemCalculation->getDateFrom()) <= new \DateTime()
                                    && new \DateTime($tblItemCalculation->getDateTo()) >= new \DateTime()){
                                    $IsNow = true;
                                }
                                $Price = 'Preis: '.$tblItemCalculation->getPriceString();
                                $Span = ($tblItemCalculation->getDateFrom()
                                    ?$tblItemCalculation->getDateFrom().
                                    ($tblItemCalculation->getDateTo()
                                        ? ' - '.$tblItemCalculation->getDateTo()
                                        : '')
                                    : '');
                                $Option = (new Link('', ApiItem::getEndpoint(), new Pencil()))
                                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenEditCalculationModal('editCalculation', $tblItemVariant->getId(), $tblItemCalculation->getId()))
                                    .'|'.
                                    (new Link(new DangerText(new Disable()), ApiItem::getEndpoint()))
                                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenDeleteCalculationModal('deleteCalculation', $tblItemCalculation->getId()));

                                if($IsNow){
                                    $Price = new Bold($Price);
                                    $Span = new Bold($Span);
                                }

                                $RowContent = '<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;'
                                    . $Price
                                    .'</td><td>&nbsp;&nbsp;&nbsp;&nbsp;'
                                    . $Span
                                    . '</td><td>&nbsp;&nbsp;'
                                    . $Option
                                    . '</td></tr>';
                                if($IsNow){
                                    $Row .= str_replace('<tr>', '<tr style="font-weight:bold">', $RowContent);
                                } else {
                                    $Row .= $RowContent;
                                }
                            }
                            $Row .= '</table>';
                            $Row .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$PriceAddButton;
                        } else {
                            $Row .= '&nbsp;&nbsp;&nbsp;&nbsp;'.$PriceAddButton;
                        }
                        $RowList[] = $Row;
                    }
                }

                $RowList[] = (new Link('Variante hinzufügen', ApiItem::getEndpoint(), new Plus()))
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
            ), array(
                "paging"         => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
//                "searching"      => false, // Deaktivieren Suchen
                "info"           => false,  // Deaktivieren Such-Info
            )
        );
    }
}
