<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasket;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
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
        if (!empty($tblBasketAll)) {
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

                $Item['Option'] = (new Standard('', ApiBasket::getEndpoint(), new Edit(), array(),
                        'Abrechnung bearbeiten'))
                        ->ajaxPipelineOnClick(ApiBasket::pipelineOpenEditBasketModal('editBasket', $tblBasket->getId()))
                    .new Standard('', __NAMESPACE__.'/View', new EyeOpen(), array('BasketId' => $tblBasket->getId()),
                        'Inhalt der Abrechnung')
                    .(new Standard('', ApiBasket::getEndpoint(), new Remove(), array(), 'Abrechnung entfernen'))
                        ->ajaxPipelineOnClick(ApiBasket::pipelineOpenDeleteBasketModal('deleteBasket',
                            $tblBasket->getId()));
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


    /**
     * @param null $BasketId
     *
     * @return Stage
     */
    public function frontendBasketView($BasketId = null)
    {

        $Stage = new Stage('Abrechnung', 'Inhalt');

        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        if ($tblBasket = Basket::useService()->getBasketById($BasketId)) {
            $Stage->setMessage(new Bold($tblBasket->getName()).' '.$tblBasket->getDescription());
        }

        $Stage->setContent(
            ApiBasketVerification::receiverModal('Bearbeiten')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            $this->getBasketVerificationTable($BasketId)
                        )
                    )
                )
            )
        );

        return $Stage;
    }

    /**
     * @param null $BasketId
     *
     * @return array|string
     */
    public function getBasketVerificationTable($BasketId = null)
    {
        $tblBasket = Basket::useService()->getBasketById($BasketId);
        if (!$tblBasket) {
            return new Danger('Warenkorb wurde nicht gefunden')
                .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $TableContent = array();
        if (($tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket))) {
            array_walk($tblBasketVerificationList,
                function (TblBasketVerification $tblBasketVerification) use (&$TableContent, $tblBasket) {
                    $Item['PersonCauser'] = '';
                    $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor(new DangerText(new WarningIcon().' Beitragszahler Fehlt'),
                        $tblBasketVerification->getId()) ;
                    $Item['Item'] = '';
                    $Item['Price'] = '';
                    $Item['Quantity'] = '';
                    $Item['Summary'] = '';
                    if(($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())) {
                        $Item['PersonCauser'] = $tblPersonCauser->getLastFirstName();
                    }
                    if(($tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor())) {
                        $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor($tblPersonDebtor->getLastFirstName(),
                            $tblBasketVerification->getId());
                    }
                    if(($tblItem = $tblBasketVerification->getServiceTblItem())) {
                        $Item['Item'] = $tblItem->getName();
                    }
                    if(($Price = $tblBasketVerification->getPrice())) {
                        $Item['Price'] = ApiBasketVerification::receiverItemPrice($Price, $tblBasketVerification->getId());
                    }
                    if(($Quantity = $tblBasketVerification->getQuantity())) {
                        $Item['Quantity'] = ApiBasketVerification::receiverItemQuantity($Quantity, $tblBasketVerification->getId());
                    }
                    if(($Summary = $tblBasketVerification->getSummaryPrice())) {
                        $Item['Summary'] = ApiBasketVerification::receiverItemSummary($Summary, $tblBasketVerification->getId());
                    }
                    $Item['Price'] .= '&nbsp;'.(new Link('', ApiBasketVerification::getEndpoint(), new Pencil(), array(), 'Preis / Anzahl bearbeiten'))
                            ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenEditPrice($tblBasketVerification->getId()));
                    $Item['Quantity'] .= '&nbsp;'.(new Link('', ApiBasketVerification::getEndpoint(), new Pencil(), array(), 'Preis / Anzahl bearbeiten'))
                            ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenEditPrice($tblBasketVerification->getId()));

                    // Add ChangeButton to PersonDebtor
                    $Item['PersonDebtor'] = $Item['PersonDebtor'].
                        '&nbsp;'.new ToolTip((new Link('', ApiBasketVerification::getEndpoint(), new Pencil()))
                            ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenEditDebtorSelectionModal($tblBasketVerification->getId()))
                            , 'Beitragszahler ändern');

                    array_push($TableContent, $Item);
                });
        }
        return new TableData($TableContent, null,
            array(
                'PersonCauser' => 'Beitragsverursacher',
                'PersonDebtor' => 'Beitragszahler',
                'Item'   => 'Artikel',
                'Price'  => 'Einzelpreis',
                'Quantity'  => 'Anzahl',
                'Summary'  => 'Gesamtpreis',
                'Option' => ''
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(3,4,5))
                ),
                'order'      => array(
                    array(1, 'asc')
                )
            ));
    }
}
