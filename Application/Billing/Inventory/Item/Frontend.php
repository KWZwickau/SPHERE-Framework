<?php

namespace SPHERE\Application\Billing\Inventory\Item;

use SPHERE\Application\Api\Billing\Inventory\ApiItem;
use SPHERE\Application\Billing\Inventory\Item\Service\Entity\TblItem;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\ListingTable;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
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

        $Stage->setContent(
            ApiItem::receiverModal('Anlegen einer neuen Beitragsart', 'AddItem')
            .ApiItem::receiverModal('Beitragsart bearbeiten', 'EditItem')
            .ApiItem::receiverModal('Anlegen einer neuen Beitrags-Variante', 'AddVariant')
            .ApiItem::receiverModal('Entfernen einer Beitragsart', 'deleteItem')
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

                $Item['Name'] = $tblItem->getName();
                $Item['PersonGroup'] = '';
//                $Item['ItemType'] = $tblItem->getTblItemType()->getName();
                $Item['Variant'] = '';

                $Item['Option'] =
                    (new Standard('', ApiItem::getEndpoint(), new Edit(), array(), 'Bearbeiten'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenEditItemModal('EditItem', $tblItem->getId()))
                    . (new Standard('', ApiItem::getEndpoint(), new Plus(), array(), 'Varianten hinzufügen'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenAddVariantModal('AddVariant'))
                    . (new Standard('', ApiItem::getEndpoint(), new Remove(), array(), 'Löschen'))
                        ->ajaxPipelineOnClick(ApiItem::pipelineOpenDeleteItemModal('deleteItem', $tblItem->getId()));

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
                    $Item['PersonGroup'] = implode(', ', $GroupList);
                }

                array_push($TableContent, $Item);
            });
        }

        return new TableData($TableContent, null,
            array(
                'Name'        => 'Name',
                'PersonGroup' => 'zugewiesene Personengruppen',
                'Variant'     => 'Preis-Varianten',
                'Option'      => ''
            )
        );
    }
}
