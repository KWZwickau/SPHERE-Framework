<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket;

use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasket;
use SPHERE\Application\Api\Billing\Bookkeeping\ApiBasketVerification;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasket;
use SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity\TblBasketVerification;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\ChevronLeft;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Edit;
use SPHERE\Common\Frontend\Icon\Repository\EyeOpen;
use SPHERE\Common\Frontend\Icon\Repository\Pencil;
use SPHERE\Common\Frontend\Icon\Repository\Plus;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
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
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
        if(!empty($tblBasketAll)) {
            array_walk($tblBasketAll, function(TblBasket &$tblBasket) use (&$TableContent) {

                $Item['Number'] = $tblBasket->getId();
                $Item['Name'] = $tblBasket->getName().' '.new Muted(new Small($tblBasket->getDescription()));
//                $Item['CreateDate'] = $tblBasket->getCreateDate();

                $Item['TimeTarget'] = $tblBasket->getTargetTime();
                $Item['Time'] = $tblBasket->getMonth(true).'.'.$tblBasket->getYear();

                $Item['Item'] = '';
                $tblItemList = Basket::useService()->getItemAllByBasket($tblBasket);
                $ItemArray = array();
                if($tblItemList) {
                    foreach($tblItemList as $tblItem) {
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
                'Number'     => 'Nr.',
                'Name'       => 'Name',
                'TimeTarget' => 'Fälligkeit',
                'Time'       => 'Abrechnungsmonat',
                'Item'       => 'Artikel',
                'Option'     => ''
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

        $PanelHead = $Time = $TargetTime = '';
        if($tblBasket = Basket::useService()->getBasketById($BasketId)) {
            $PanelHead = new Bold($tblBasket->getName()).' '.$tblBasket->getDescription();
            $Time = $tblBasket->getMonth(true).'.'.$tblBasket->getYear();
            $TargetTime = $tblBasket->getTargetTime();
        }

        $Stage->setContent(
            ApiBasketVerification::receiverModal('Bearbeiten')
            .ApiBasketVerification::receiverService()
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('', new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(new InfoText('<span style="font-size: large">'.$PanelHead.'</span>'), 6),
                                new LayoutColumn('Abrechnungszeitraum: '.$Time, 3),
                                new LayoutColumn('Fälligkeitsdatum: '.$TargetTime, 3),
                            )))), Panel::PANEL_TYPE_INFO)
                        )
                    )
                )
            )
            .ApiBasketVerification::receiverTableLayout($this->getBasketVerificationLayout($BasketId))
        );

        return $Stage;
    }

    /**
     * @param null $BasketId
     *
     * @return Layout|string
     */
    public function getBasketVerificationLayout($BasketId = null)
    {

        $tblBasket = Basket::useService()->getBasketById($BasketId);
        if(!$tblBasket) {
            return new Danger('Warenkorb wurde nicht gefunden')
                .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $CountArray = array();
        $TableContent = array();
        $PanelContent = '';
        $IsDebtorNumberNeed = false;
        if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED)) {
            if($tblSetting->getValue() == 1) {
                $IsDebtorNumberNeed = true;
            }
        }
        if(($tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket))) {
            $CountArray['AllCount'] = count($tblBasketVerificationList);
            $DebtorMiss = 0;
            $DebtorNumberMiss = 0;
            array_walk($tblBasketVerificationList,
                function(TblBasketVerification $tblBasketVerification) use (
                    &$TableContent,
                    $tblBasket,
                    &$CountArray,
                    &$DebtorNumberMiss,
                    &$DebtorMiss,
                    $IsDebtorNumberNeed
                ) {
                    $Item['PersonCauser'] = '';
                    $Item['PersonDebtorFail'] = '';
                    $Item['PersonDebtor'] = '';
                    $Item['Item'] = '';
                    $Item['Price'] = '';
                    $Item['Quantity'] = '';
                    $Item['Summary'] = '';
                    if(($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())) {
                        $Item['PersonCauser'] = $tblPersonCauser->getLastFirstName();
                        $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor(
                            new DangerText($tblPersonCauser->getLastFirstName().' '.
                                new ToolTip(new WarningIcon(), 'Beitragszahler nicht gefunden')),
                            $tblBasketVerification->getId());
                        $Item['PersonDebtorFail'] = new DangerText(new WarningIcon());
                    }

                    $InfoDebtorNumber = '';
                    // new DebtorNumber
                    if($IsDebtorNumberNeed) {
                        $InfoDebtorNumber = new ToolTip(new DangerText(new WarningIcon()), 'Debitor-Nr. wird benötigt!');
                    }

                    if(($tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor())) {
                        // ignore FailMessage if not necessary
                        if(Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor)) {
                            $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor($tblPersonDebtor->getLastFirstName(),
                                $tblBasketVerification->getId());
                            $Item['PersonDebtorFail'] = '';
                        } else {
                            $DebtorNumberMiss++;
                            $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor($tblPersonDebtor->getLastFirstName().' '.$InfoDebtorNumber,
                                $tblBasketVerification->getId());
                            if(!$IsDebtorNumberNeed) {
                                $Item['PersonDebtorFail'] = '';
                            }
                        }
                    } else {
                        $DebtorMiss++;
                    }
                    if(($tblItem = $tblBasketVerification->getServiceTblItem())) {
                        $Item['Item'] = $tblItem->getName();
                    }
                    if(($Price = $tblBasketVerification->getPrice())) {
                        // Hide Sort by Integer
                        $StringCount = strlen($Price) - 5;
                        $SortPrice =  substr(str_replace(',','', $Price), 0, $StringCount);
                        $Item['Price'] = '<span hidden>'.$SortPrice.'</span>'.ApiBasketVerification::receiverItemPrice($Price,
                            $tblBasketVerification->getId());
                    }
                    if(($Quantity = $tblBasketVerification->getQuantity())) {
                        $Item['Quantity'] = ApiBasketVerification::receiverItemQuantity(
                            new Form(new FormGroup(new FormRow(new FormColumn(
                                    (new TextField('Quantity['.$tblBasketVerification->getId().']', '', ''))
                                        ->ajaxPipelineOnChange(ApiBasketVerification::pipelineChangeQuantity($tblBasketVerification->getId()))
                                ))))
                            , $tblBasketVerification->getId());
                        // setDefaultValue don't work -> use POST
                        $_POST['Quantity'][$tblBasketVerification->getId()] = $Quantity;
                    }
                    if(($Summary = $tblBasketVerification->getSummaryPrice())) {
                        // Hide Sort by Integer
                        $StringCount = strlen($Summary) - 5;
                        $SortSummary =  substr(str_replace(',','', $Summary), 0, $StringCount);
                        $Item['Summary'] = '<span hidden>'.$SortSummary.'</span>'.ApiBasketVerification::receiverItemSummary($Summary,
                            $tblBasketVerification->getId());
                    }

                    // Add ChangeButton to PersonDebtor
                    $Item['PersonDebtor'] = $Item['PersonDebtor'].
                        '&nbsp;'.new ToolTip((new Link('', ApiBasketVerification::getEndpoint(), new Pencil()))
                            ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenEditDebtorSelectionModal($tblBasketVerification->getId()))
                            , 'Beitragszahler ändern');

                    //ToDO API für's löschen
                    $Item['Option'] = (new Standard(new DangerText(new Disable()), ApiBasketVerification::getEndpoint(), null
                        /*, array(),'Eintrag löschen'*/))
                        ->ajaxPipelineOnClick(ApiBasketVerification::pipelineDeleteDebtorSelection($tblBasketVerification->getId()));

                    array_push($TableContent, $Item);
                });
            $CountArray['DebtorNumberMiss'] = $DebtorNumberMiss;
            $CountArray['DebtorMiss'] = $DebtorMiss;

            $Title = '';
            foreach($CountArray as $Key => $Count) {
                switch($Key) {
                    case 'AllCount':
                        $Title = 'Anzahl der Zahlungszuordnungen:';
                        break;
                    case 'DebtorNumberMiss':
                        $Title = 'Anzahl fehlernder Debitor Nummern:';
                        if($Count > 0 && $IsDebtorNumberNeed) {
                            $Title = new DangerText($Title);
                        }
                        break;
                    case 'DebtorMiss':
                        $Title = 'Anzahl fehlender Zahlungszuweisungen:';
                        if($Count > 0) {
                            $Title = new DangerText($Title);
                        }
                        break;
                }
                $PanelContent .= new Container(new Bold($Title).' '.$Count);
            }
            $PanelContent = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $PanelContent
                    , 10),
                new LayoutColumn(
                    new Standard('Rechnungen erstellen', '')
                    , 2)
            ))));
        }
        $PanelCount = new Panel('Übersicht', $PanelContent);
        return new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        $PanelCount
                    ),
                    new LayoutColumn(
                        new TableData($TableContent, null,
                            array(
                                'PersonCauser'     => 'Beitragsverursacher',
                                'PersonDebtorFail' => 'Fehler',
                                'PersonDebtor'     => 'Beitragszahler',
                                'Item'             => 'Artikel',
                                'Price'            => 'Einzelpreis',
                                'Quantity'         => 'Anzahl',
                                'Summary'          => 'Gesamtpreis',
                                'Option'           => ''
                            ), array(
                                'columnDefs' => array(
                                    array('type' => 'natural', 'targets' => array(4, 6)),
                                    array("orderable" => false, "targets"   => 5),
                                ),
                                'order'      => array(
                                    array(1, 'desc'),
                                    array(0, 'asc')
                                ),
                                // First column should not be with Tabindex
                                // solve the problem with responsive false
                                "responsive" => false,
                            ))
                    ),
                ))
            )
        );
    }

    /**
     * @param string $BasketId
     */
    public function frontendDoInvoice($BasketId = '')
    {

        if(($tblBasket = Basket::useService()->getBasketById($BasketId))){
            //ToDO überarbeitung Invoice
            Invoice::useService()->createInvoice($tblBasket);
        }
    }
}
