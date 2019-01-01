<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
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
 * @package SPHERE\Application\Billing\Bookkeeping\Basket
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendBasketList()
    {

        $Stage = new Stage('Abrechnung', 'Übersicht');
        $Stage->setMessage('Zeigt alle vorhandenen Abrechnungen an');

        //ToDO API
        $Stage->addButton((new Primary('Abrechnung hinzufügen', '#', new Plus()))
        ->ajaxPipelineOnClick(ApiBasket::pipelineOpenAddBasketModal('addBasket')));

        $Stage->setContent(
            ApiBasket::receiverModal('Erstellen einer neuen Abrechnung', 'addBasket')
            .ApiBasket::receiverModal('Bearbeiten der Abrechnung', 'editBasket')
            .ApiBasket::receiverModal('Entfernen der Abrechnung', 'deleteBasket')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            ApiBasket::receiverContent($this->getBasketTable())
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @return TableData
     */
    public function getBasketTable()
    {

        $tblBasketAll = Basket::useService()->getBasketAll();
        $TableContent = array();
        if (!empty( $tblBasketAll )) {
            array_walk($tblBasketAll, function (TblBasket &$tblBasket) use (&$TableContent) {

                $Item['Number'] = $tblBasket->getId();
                $Item['Name'] = $tblBasket->getName();
                $Item['Description'] = $tblBasket->getDescription();
//                $Item['CreateDate'] = $tblBasket->getCreateDate();
                $Item['CountDebtorSelection'] = '';
                $Count = Basket::useService()->countDebtorSelectionCountByBasket($tblBasket);
                if ($Count) {
                    $Item['CountDebtorSelection'] = $Count;
                }
                $Item['Item'] = '';
                $tblItemList = Basket::useService()->getItemAllByBasket($tblBasket);
                $ItemArray = array();
                if ($tblItemList) {
                    foreach ($tblItemList as $tblItem) {
                        $ItemArray[] = $tblItem->getName();
                    }
                    sort($ItemArray);
                    $Item['Item'] = implode(', ', $ItemArray);
                }

//                $tblBasketVerification = Basket::useService()->getBasketVerificationAllByBasket($tblBasket);

                $Item['Option'] =(new Standard('', ApiBasket::getEndpoint(), new Edit(), array(), 'Abrechnung bearbeiten'))
                    ->ajaxPipelineOnClick(ApiBasket::pipelineOpenEditBasketModal('editBasket', $tblBasket->getId()))
                    .(new Standard('', ApiBasket::getEndpoint(), new Remove(), array(), 'Abrechnung entfernen'))
                    ->ajaxPipelineOnClick(ApiBasket::pipelineOpenDeleteBasketModal('deleteBasket', $tblBasket->getId()));
                array_push($TableContent, $Item);
            });
        }

        return new TableData($TableContent, null,
            array(
                'Number'               => 'Nummer',
                'Name'                 => 'Name',
                'Description'          => 'Beschreibung',
                'CountDebtorSelection' => 'Anzahl Zahlungszuweisungen',
                'Item'                 => 'Artikel',
                'Option'               => ''
            )
        );
    }
}
