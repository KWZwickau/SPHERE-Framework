<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Api\Billing\Inventory\ApiItem;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItemCalculation;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\EyeMinus;
use SPHERE\Common\Frontend\Icon\Repository\FolderOpen;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\CustomPanel;
use SPHERE\Common\Frontend\Layout\Repository\PullClear;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\WellReadOnly;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Warning as WarningText;
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

        $Stage = new Stage('Beitragsarten', 'Übersicht');
        $Stage->addButton((new Primary('Beitragsart hinzufügen', ApiItem::getEndpoint(), new Plus()))
            ->ajaxPipelineOnClick(ApiItem::pipelineOpenAddItemModal('addItem')));
        $Stage->addButton(new Standard('Deaktivierte Beitragsarten', '/Billing/Inventory/Item/ViewNotActive', new EyeMinus()));

        $Stage->setContent(
            ApiItem::receiverModal('Beitragsart hinzufügen', 'addItem')
            .ApiItem::receiverModal('Beitragsart bearbeiten', 'editItem')
            .ApiItem::receiverModal('Beitragsart entfernen', 'deleteItem')
            .ApiItem::receiverModal('Beitragsart deaktivieren', 'deactivateItem')
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
                    ),
                    new Title(new ListingTable().' Übersicht')
                ))
            )
        );

        return $Stage;
    }

    /**
     * // preopen changed calculation
     * @param string $tblItemCalculationId
     *
     * @return TableData
     * @throws \Exception
     */
    public function getItemTable($tblItemCalculationId = '')
    {

        $tblItemAll = Item::useService()->getItemAll();
        $TableContent = array();
        if(!empty($tblItemAll)){
            array_walk($tblItemAll, function(TblItem $tblItem) use (&$TableContent, $tblItemCalculationId){

                $Item['Name'] = $tblItem->getName()
                    .(new Link('', ApiItem::getEndpoint(), new Pencil(), array(), 'Beitragsart bearbeiten'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenEditItemModal('editItem', $tblItem->getId()));
                if((Basket::useService()->getBasketItemAllByItem($tblItem))){
                    // Beitragsart deaktivierbar
                    $Item['Name'] .= '|'
                        .(new Link(new WarningText(new EyeMinus()), ApiItem::getEndpoint(), null, array(),
                            'Deaktivierung der Beitragsart'))
                            ->ajaxPipelineOnClick(ApiItem::pipelineOpenDeactivateItemModal('deactivateItem',
                                $tblItem->getId()));
                } else {
                    // Beitragsart löschbar
                    $Item['Name'] .= '|'
                        .(new Link(new DangerText(new Disable()), ApiItem::getEndpoint(), null, array(),
                            'Löschen der Beitragsart'))
                            ->ajaxPipelineOnClick(ApiItem::pipelineOpenDeleteItemModal('deleteItem',
                                $tblItem->getId()));
                }
                $Item['Name'] .= new WellReadOnly(
                    new Layout(new LayoutGroup(array(
                        new LayoutRow(array(
                            new LayoutColumn('Fibu-Konto: '.new Bold(($tblItem->getFibuAccount(true) ?: new Muted('('.$tblItem->getFibuAccount().')'))), 4),
                            new LayoutColumn('Fibu-Gegenkonto: '.new Bold(($tblItem->getFibuToAccount(true) ?: new Muted('('.$tblItem->getFibuToAccount().')'))), 8),
                        )),
                        new LayoutRow(array(new LayoutColumn(
                            '<div style="height: 5px"></div>', 8
                        ))),
                        new LayoutRow(array(
                            new LayoutColumn(
                                'Kostenstelle 1: '.new Bold(($tblItem->getKost1(true) ?: new Muted('('.$tblItem->getKost1().')')))
                            , 4),
                            new LayoutColumn(
                                'Kostenstelle 2: '.new Bold(($tblItem->getKost2(true) ?: new Muted('('.$tblItem->getKost2().')')))
                            , 4),
                            new LayoutColumn(
                                'BU-Schlüssel: '.new Bold(($tblItem->getBuKey(true) ?: new Muted('('.$tblItem->getBuKey().')')))
                            , 4),
                        )),
                    )))
                );

                $Item['PersonGroup'] = '';
                $GroupList = array();
                if(($PersonGroupList = Item::useService()->getItemGroupByItem($tblItem))){
                    foreach($PersonGroupList as $PersonGroup) {
                        if(($tblGroup = $PersonGroup->getServiceTblGroup())){
                            $GroupList[] = $tblGroup->getName();
                        }
                    }
                    sort($GroupList);
                }
                if(!empty($GroupList)){
//                    $Item['PersonGroup'] = new Listing($GroupList);
                    $Item['PersonGroup'] = implode('<br/>', $GroupList);
                }

                $RowList = array();
                if(($tblItemVariantList = Item::useService()->getItemVariantByItem($tblItem))){

                    foreach($tblItemVariantList as $tblItemVariant) {

                        $PanelTitle = $tblItemVariant->getName();
                        $PanelTitleButtons = array();
                        $PanelTitleButtons[] = (new Link('', ApiItem::getEndpoint(), new Pencil(), array(), 'Preisvariante bearbeiten'))
                                ->ajaxPipelineOnClick(ApiItem::pipelineOpenEditVariantModal('editVariant',
                                    $tblItem->getId(), $tblItemVariant->getId()));
                        $PanelTitleButtons[] = (new Link(new DangerText(new Disable()), ApiItem::getEndpoint(), null, array(), 'Löschen der Preisvariante'))
                                ->ajaxPipelineOnClick(ApiItem::pipelineOpenDeleteVariantModal('deleteVariant',
                                    $tblItemVariant->getId()));
//                            .($tblItemVariant->getDescription() ? '<br/>'.$tblItemVariant->getDescription() : '');
                        $PanelContent = array();
                        $PriceAddButton = (new Link('Preis hinzufügen', ApiItem::getEndpoint(), new Plus()))
                            ->ajaxPipelineOnClick(ApiItem::pipelineOpenAddCalculationModal('addCalculation',
                                $tblItemVariant->getId()));
                        $isPanelOpen = false;
                        if(($tblItemCalculationList = Item::useService()->getItemCalculationByItemVariant($tblItemVariant))){
                            /** @var TblItemCalculation[] $tblItemCalculationList */
                            $tblItemCalculationList = $this->getSorter($tblItemCalculationList)->sortObjectBy('DateFrom',
                                new DateTimeSorter(), Sorter::ORDER_DESC);
                            $IsEmptyNowPrice = true;
                            foreach($tblItemCalculationList as $tblItemCalculation) {
                                if($tblItemCalculation->getId() == $tblItemCalculationId){
                                    $isPanelOpen = true;
                                }
                                //ToDO aktuellen Eintrag markieren
                                $IsNow = false;
                                if(new \DateTime($tblItemCalculation->getDateFrom()) <= new \DateTime()
                                    && (new \DateTime($tblItemCalculation->getDateTo()) >= new \DateTime() || !$tblItemCalculation->getDateTo())){
                                    $IsNow = true;
                                    $IsEmptyNowPrice = false;
                                }
                                $Price = $tblItemCalculation->getPriceString();
                                $Price = str_replace(' ', '&nbsp;', $Price);
                                $Span = ($tblItemCalculation->getDateFrom()
                                    ? $tblItemCalculation->getDateFrom().
                                    ($tblItemCalculation->getDateTo()
                                        ? ' - '.$tblItemCalculation->getDateTo()
                                        : '')
                                    : '');
                                $Option = '|'.(new Link(new DangerText(new Disable()), ApiItem::getEndpoint(), null, array(), 'Preis/Zeitraum löschen'))
                                    ->ajaxPipelineOnClick(ApiItem::pipelineOpenDeleteCalculationModal('deleteCalculation', $tblItemCalculation->getId()));
                                if($IsNow){
                                    $PanelTitle .= new Small(' ('.$Price.') ');
                                    $Price = new Bold('Preis: '.$Price);
                                    $Span = new Bold($Span);
                                } else {
                                    $Price = new Small(new Small(new Muted($Price)));
                                    $Span = new Small(new Small(new Muted($Span)));
                                }
                                $PanelContent[] = new Container(
                                    new Layout(new LayoutGroup(new LayoutRow(array(
                                        new LayoutColumn(new PullClear('<div style="width: 32px; float: left;">'.(new Link('', ApiItem::getEndpoint(), new Pencil(), array(), 'Preis/Zeitraum bearbeiten'))
                                            ->ajaxPipelineOnClick(ApiItem::pipelineOpenEditCalculationModal('editCalculation',
                                                $tblItemVariant->getId(), $tblItemCalculation->getId())).'&nbsp;</div>'.$Price), 4),
                                        new LayoutColumn($Span.' '.$Option, 8),
                                    ))))
                                );
                            }
                            $PanelContent[] = '&nbsp;&nbsp;&nbsp;'.$PriceAddButton;
                            if($IsEmptyNowPrice){
                                $PanelTitle .= new Small(' ('.new DangerText('Kein aktueller Preis').')');
                            }
                        } else {
                            $PanelContent[] = '&nbsp;&nbsp;&nbsp;'.$PriceAddButton;
                        }
                        $PanelTitle .= ' '.implode(' | ', $PanelTitleButtons);
                        $RowList[] = (new CustomPanel($PanelTitle, $PanelContent))
                            ->setAccordeon($isPanelOpen)->setHash($tblItemVariant->getId())->setHeadStyle('padding: 3px;');
                    }
                }

                $RowList[] = (new Link('Variante hinzufügen', ApiItem::getEndpoint(), new Plus()))
                    ->ajaxPipelineOnClick(ApiItem::pipelineOpenAddVariantModal('addVariant', $tblItem->getId()));

                $Item['Variant'] = implode('', $RowList);
//                $Item['Variant'] = new Listing($RowList);

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
                'columnDefs'     => array(
                    array("orderable" => false, "targets" => array(-1, -2)),
                ),
                "paging"         => false, // Deaktivieren Blättern
                "iDisplayLength" => -1,    // Alle Einträge zeigen
//                "searching"      => false, // Deaktivieren Suchen
                "info"           => false,  // Deaktivieren Such-Info
            )
        );
    }

    /**
     * @return Stage
     */
    public function frontendItemNotActive()
    {
        $Stage = new Stage('Deaktivierte Beitragsarten');
        $Stage->addButton(new Standard('Zurück', '/Billing/Inventory/Item', new ChevronLeft()));

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(new LayoutColumn(
            ApiItem::receiverItemTable($this->getItemDeactiveTable())
        )))));

        return $Stage;
    }

    /**
     * @return TableData
     */
    public function getItemDeactiveTable()
    {

        $TableContent = array();
        if(($tblItemList = Item::useService()->getItemAll(false))){
            array_walk($tblItemList, function(TblItem $tblItem) use (&$TableContent){
                $item = array();
                $item['name'] = $tblItem->getName();
                $updateDate = $tblItem->getEntityUpdate();
                $item['lastChange'] = $updateDate->format('d.m.Y');
                $updateDate->modify("+".Item::useService()::DEACTIVATE_TIME_SPAN." month");
                $item['lastPrint'] = $updateDate->format('d.m.Y');
                $item['option'] = (new Standard('', '', new FolderOpen(), array(), 'Beitragsart aktivieren'))->ajaxPipelineOnClick(ApiItem::pipelineActivateItem($tblItem->getId()));
                array_push($TableContent, $item);
            });
        }
        return new TableData($TableContent, null, array(
            'name' => 'Beitragsart',
            'lastChange' => 'Deaktiviert am',
            'lastPrint' => 'Druckbar bis (Schulbescheinigung)',
            'option' => '',
        ), array(
            'order' => array(
                array('1', 'desc'),
            ),
            'columnDefs'     => array(
                array("orderable" => false, "targets" => array(-1)),
                array('type' => 'de_date', 'targets' => array(1, 2)),
            ),
        ));
    }
}
