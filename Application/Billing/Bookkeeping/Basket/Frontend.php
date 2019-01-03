<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Api\Billing\Accounting\ApiDebtorSelection;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasket;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketPerson;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Cog;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Info as InfoIcon;
use SPHERE\Common\Frontend\Icon\Repository\Person;
use SPHERE\Common\Frontend\Icon\Repository\PersonParent;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Success as SuccessText;
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

        $tblBasketVerification = false;
        if ($tblBasket = Basket::useService()->getBasketById($BasketId)) {
            $Stage->setMessage(new Bold($tblBasket->getName()).' '.$tblBasket->getDescription());
            $tblBasketVerification = Basket::useService()->getBasketVerificationAllByBasket($tblBasket);
        }

        $DebtorSelectionMessage = '';

        if($tblBasketVerification){
            $Content = $this->getBasketVerificationTable($BasketId);
        } else {
            $DebtorSelectionMessage = new Info( 'Alle Artikel die keine Zahlungszuweisung hinterlegt haben, werden nicht in Rechnung gestellt.'
                .new Standard('Berechnung erstellen', __NAMESPACE__.'/DebtorSelection', new Cog()
                    , array('BasketId' => $BasketId)));

            $Content = $this->getBasketViewTable($BasketId);
        }

        $Stage->setContent(
            ApiDebtorSelection::receiverModal('Hinzufügen der Zahlungszuweisung', 'addDebtorSelection')
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(array(
                        new LayoutColumn(
                            $DebtorSelectionMessage
                        ),
                        new LayoutColumn(
                            $Content
                        )
                    ))
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
    public function getBasketViewTable($BasketId = null)
    {
        $tblBasket = Basket::useService()->getBasketById($BasketId);
        if (!$tblBasket) {
            return new Danger('Warenkorb wurde nicht gefunden')
                .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $TableContent = array();
        if (($tblBasketPersonList = Basket::useService()->getBasketPersonAllByBasket($tblBasket))) {
            array_walk($tblBasketPersonList,
                function (TblBasketPerson $tblBasketPerson) use (&$TableContent, $tblBasket) {
                    if (($tblPerson = $tblBasketPerson->getServiceTblPerson())) {
                        $Item['Person'] = $tblPerson->getLastFirstName();
                        $Item['Price'] = '0,00 € '. new DangerText(new ToolTip(new Disable(),
                            'Ohne Zahlungszuweisung kann kein Preis ermittelt werden'));
                        if (($tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket))) {
                            foreach ($tblBasketItemList as $tblBasketItem) {
                                $DebtorSelection = '';
                                if (($tblItem = $tblBasketItem->getServiceTblItem())) {
                                    $tblDebtorSelectionList = Debtor::useService()->getDebtorSelectionByPersonCauserAndItem($tblPerson,
                                        $tblItem);
                                    if ($tblDebtorSelectionList) {
//                                        if($tblPerson->getFirstName() == 'Emilia'){
//                                            Debugger::screenDump($tblDebtorSelectionList);
//                                        }
                                        $DebtorSelection = new SuccessText(new ToolTip($tblItem->getName(),
                                            'Zahlungszuweisung vorhanden'));
                                        $PriceArray = array();
                                        foreach ($tblDebtorSelectionList as $tblDebtorSelection) {
                                            $tblPaymentType = $tblDebtorSelection->getServiceTblPaymentType();
                                            ($tblPaymentType->getName() != 'SEPA-Lastschrift'
                                                ? $ShowTypeInfo = false
                                                : $ShowTypeInfo = true);
                                            if(($tblItemVariant = $tblDebtorSelection->getServiceTblItemVariant())){
                                                // should be only one entry
                                                if(($tblItemCalculation = Item::useService()->getItemCalculationNowByItemVariant($tblItemVariant))){
                                                    $PriceArray[] = $tblItemCalculation->getPriceString().' '
                                                        .new ToolTip( new InfoIcon(), $tblPaymentType->getName());
                                                }
                                            } else {
                                                $PriceArray[] = $tblDebtorSelection->getValuePriceString().' '
                                                    .new ToolTip( new InfoIcon(), $tblPaymentType->getName());
                                            }
                                        }
                                        if(empty($PriceArray)){
                                            $PriceArray[] = 'Test';
                                        }
                                        $Item['Price'] = implode(', ', $PriceArray);
                                    } else {
                                        $Item['Option'] = (new Link(new PersonParent().' '.new ChevronRight().' '.new Person()
                                            , '', null, array(), 'Zahlungszuweisung festlegen'))
                                            ->ajaxPipelineOnClick(ApiDebtorSelection::pipelineOpenAddDebtorSelectionModal('addDebtorSelection'
                                                , $tblPerson->getId(), $tblItem->getId()));
                                        $DebtorSelection = new DangerText(new Disable().' '.new ToolTip($tblItem->getName()
                                                , 'Zahlungszuweisung nicht vorhanden'));
                                    }
                                }

                                $Item['Item'] = $DebtorSelection;
                                array_push($TableContent, $Item);
                            }
                        } else {
                            $Item['Item'] = '---';
                            $Item['Option'] = '';
                            array_push($TableContent, $Item);
                        }
                    }
                });
        }
        return new TableData($TableContent, null,
            array(
                'Person' => 'Person',
                'Item'   => 'Artikel Zuweisung',
                'Price'  => 'Preis',
                'Option' => ''
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(2))
                )
            ));
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
                    $Item['PersonDebtor'] = '';
                    $Item['Item'] = '';
                    $Item['Price'] = '';
                    $Item['Quantity'] = '';
                    $Item['SumPrice'] = '';
                    if(($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())) {
                        $Item['PersonCauser'] = $tblPersonCauser->getLastFirstName();
                    }
                    if(($tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor())) {
                        $Item['PersonDebtor'] = $tblPersonDebtor->getLastFirstName();
                    }
                    if(($tblItem = $tblBasketVerification->getServiceTblItem())) {
                        $Item['Item'] = $tblItem->getName();
                    }
                    if(($Price = $tblBasketVerification->getPrice())) {
                        // ToDO Receiver
                        $Item['Price'] = $Price;
                    }
                    if(($Quantity = $tblBasketVerification->getQuantity())) {
                        // ToDO Receiver
                        $Item['Quantity'] = $Quantity;
                    }
                    if(($SumPrice = $tblBasketVerification->getSummaryPrice())) {
                        // ToDO Receiver
                        $Item['SumPrice'] = $SumPrice;
                    }
                    //ToDO API
                    $Item['Option'] = new Standard('', '', new Edit(), array(), 'Preis / Anzahl bearbeiten');

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
                'SumPrice'  => 'Gesammt Preis',
                'Option' => ''
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(3,4,5))
                )
            ));
    }

    /**
     * @param null $BasketId
     *
     * @return Stage
     */
    public function frontendBasketDebtorSelection($BasketId = null)
    {

        $Stage = new Stage('Zahlungszuweisungen');
        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            if(($tblBasketItemList = Basket::useService()->getBasketItemAllByBasket($tblBasket))){
                foreach($tblBasketItemList as $tblBasketItem){
                    if(($tblItem = $tblBasketItem->getServiceTblItem())){
                        Basket::useService()->createBasketVerificationBulk($tblBasket, $tblItem);
                    }
                }
            }
        }
        $Stage->setContent(new Redirect('/Billing/Bookkeeping/Basket/View', Redirect::TIMEOUT_SUCCESS, array('BasketId' => $BasketId)));
        return $Stage;
    }
}
