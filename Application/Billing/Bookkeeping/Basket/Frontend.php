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
use SPHERE\Application\Setting\Consumer\Consumer;
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
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Icon\Repository\Warning as WarningIcon;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Danger as DangerText;
use SPHERE\Common\Frontend\Text\Repository\Info as InfoText;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\ToolTip;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\RedirectScript;
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
        if(!empty($tblBasketAll)){
            array_walk($tblBasketAll, function(TblBasket &$tblBasket) use (&$TableContent){

                $Item['Number'] = $tblBasket->getId();
                $Item['Name'] = $tblBasket->getName().' '.new Muted(new Small($tblBasket->getDescription()));
//                $Item['CreateDate'] = $tblBasket->getCreateDate();

                $Item['TimeTarget'] = $tblBasket->getTargetTime();
                $Item['Time'] = $tblBasket->getYear().'.'.$tblBasket->getMonth(true);

                $Item['Item'] = '';
                $tblItemList = Basket::useService()->getItemAllByBasket($tblBasket);
                $ItemArray = array();
                if($tblItemList){
                    foreach($tblItemList as $tblItem) {
                        $ItemArray[] = $tblItem->getName();
                    }
                    sort($ItemArray);
                    $Item['Item'] = implode(', ', $ItemArray);
                }

//                $tblBasketVerification = Basket::useService()->getBasketVerificationAllByBasket($tblBasket);

                if($tblBasket->getIsDone()){
                    $Item['Option'] = new Standard('', __NAMESPACE__.'/View', new EyeOpen(),
                        array('BasketId' => $tblBasket->getId()),
                        'Inhalt der Abrechnung');
                } else {
                    $Item['Option'] = (new Standard('', ApiBasket::getEndpoint(), new Edit(), array(),
                            'Abrechnung bearbeiten'))
                            ->ajaxPipelineOnClick(ApiBasket::pipelineOpenEditBasketModal('editBasket',
                                $tblBasket->getId()))
                        .new Standard('', __NAMESPACE__.'/View', new EyeOpen(),
                            array('BasketId' => $tblBasket->getId()),
                            'Inhalt der Abrechnung')
                        .(new Standard('', ApiBasket::getEndpoint(), new Remove(), array(), 'Abrechnung entfernen'))
                            ->ajaxPipelineOnClick(ApiBasket::pipelineOpenDeleteBasketModal('deleteBasket',
                                $tblBasket->getId()));
                }

                array_push($TableContent, $Item);
            });
        }

        return new TableData($TableContent, null,
            array(
                'Number'     => 'Nr.',
                'Name'       => 'Name',
                'TimeTarget' => 'Fälligkeit',
                'Time'       => 'Abrechnungsmonat',
                'Item'       => 'Beitragsart(en)',
                'Option'     => ''
            ), array(
                'columnDefs' => array(
                    array('type' => 'natural', 'targets' => array(0)),
                    array('type' => 'de_date', 'targets' => array(2)),
                    array("orderable" => false, "targets" => -1),
                ),
                'order'      => array(
//                    array(1, 'desc'),
                    array(0, 'desc')
                ),
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

        // out of memory (Test with 3300 entrys)
        ini_set('memory_limit', '-1');
        $Stage = new Stage('Abrechnung', 'Inhalt');

        $Stage->addButton(new Standard('Zurück', __NAMESPACE__, new ChevronLeft()));

        $PanelHead = $Time = $TargetTime = '';
        if($tblBasket = Basket::useService()->getBasketById($BasketId)){
            $PanelHead = new Bold($tblBasket->getName()).' '.$tblBasket->getDescription();
            $Time = $tblBasket->getMonth(true).'.'.$tblBasket->getYear();
            $TargetTime = $tblBasket->getTargetTime();
        }

        $Stage->setContent(
            ApiBasketVerification::receiverModal('Bearbeiten')
            .ApiBasketVerification::receiverModal('Entfernen einer Zahlung', 'deleteDebtorSelection')
            .ApiBasketVerification::receiverService()
            .new Layout(
                new LayoutGroup(
                    new LayoutRow(
                        new LayoutColumn(
                            new Panel('', new Layout(new LayoutGroup(new LayoutRow(array(
                                new LayoutColumn(new InfoText('<span style="font-size: large">'.$PanelHead.'</span>'),
                                    6),
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
        if(!$tblBasket){
            return new Danger('Warenkorb wurde nicht gefunden')
                .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $CountArray = array();
        $TableContent = array();
        $PanelContent = '';
        $IsDebtorNumberNeed = false;
        if($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_IS_DEBTOR_NUMBER_NEED)){
            if($tblSetting->getValue() == 1){
                $IsDebtorNumberNeed = true;
            }
        }
        if(($tblBasketVerificationList = Basket::useService()->getBasketVerificationAllByBasket($tblBasket))){
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
                ){
                    $Item['PersonCauser'] = '';
                    $Item['PersonDebtorFail'] = '';
                    $Item['PersonDebtor'] = '';
                    $Item['Item'] = '';
                    $Item['Price'] = '';
                    $Item['Quantity'] = '';
                    $Item['Summary'] = '';
                    if(($tblPersonCauser = $tblBasketVerification->getServiceTblPersonCauser())){
                        $Item['PersonCauser'] = $tblPersonCauser->getLastFirstName();
                        if($tblBasket->getIsDone()){
                            $Item['PersonDebtor'] = '';
                        } else {
                            $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor(
                                new DangerText($tblPersonCauser->getLastFirstName().' '.
                                    new ToolTip(new WarningIcon(), 'Beitragszahler nicht gefunden')),
                                $tblBasketVerification->getId());
                        }
                        $Item['PersonDebtorFail'] = new DangerText(new WarningIcon());
                    }

                    $InfoDebtorNumber = '';
                    // new DebtorNumber
                    if($IsDebtorNumberNeed){
                        $InfoDebtorNumber = new ToolTip(new DangerText(new WarningIcon()), 'Debitoren-Nr. wird benötigt!');
                    }

                    if(($tblPersonDebtor = $tblBasketVerification->getServiceTblPersonDebtor())){
                        // ignore FailMessage if not necessary
                        if(Debtor::useService()->getDebtorNumberByPerson($tblPersonDebtor)){
                            $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor($tblPersonDebtor->getLastFirstName(),
                                $tblBasketVerification->getId());
                            $Item['PersonDebtorFail'] = '';
                        } else {
                            $DebtorNumberMiss++;
                            $Item['PersonDebtor'] = ApiBasketVerification::receiverDebtor($tblPersonDebtor->getLastFirstName().' '.$InfoDebtorNumber,
                                $tblBasketVerification->getId());
                            if(!$IsDebtorNumberNeed){
                                $Item['PersonDebtorFail'] = '';
                            }
                        }

                    } else {
                        $DebtorMiss++;
                    }
                    if(($tblItem = $tblBasketVerification->getServiceTblItem())){
                        $Item['Item'] = $tblItem->getName();
                    }
                    if(($Price = $tblBasketVerification->getPrice())){
                        // Hide Sort by Integer
                        $StringCount = strlen($Price) - 5;
                        $SortPrice = substr(str_replace(',', '', $Price), 0, $StringCount);
                        $Item['Price'] = '<span hidden>'.$SortPrice.'</span>'.ApiBasketVerification::receiverItemPrice($Price,
                                $tblBasketVerification->getId());
                        // Add ChangeButton to PersonDebtor
                        if(!$tblBasket->getIsDone()){
                            $Item['Price'] .= '&nbsp;'.new ToolTip((new Link('', ApiBasketVerification::getEndpoint(), new Pencil()))
                                    ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenEditDebtorSelectionModal($tblBasketVerification->getId()))
                                    , 'Preis ändern');
                        }
                    }
                    if(($Quantity = $tblBasketVerification->getQuantity())){
                        if($tblBasket->getIsDone()){
                            $Item['Quantity'] = $Quantity;
                        } else {
                            $Item['Quantity'] = ApiBasketVerification::receiverItemQuantity(
                                new Form(new FormGroup(new FormRow(new FormColumn(
                                    (new TextField('Quantity['.$tblBasketVerification->getId().']', '', ''))
                                        ->ajaxPipelineOnChange(ApiBasketVerification::pipelineChangeQuantity($tblBasketVerification->getId()))
                                ))))
                                , $tblBasketVerification->getId());
                            // setDefaultValue don't work -> use POST
                            $_POST['Quantity'][$tblBasketVerification->getId()] = $Quantity;
                        }
                    }
                    if(($Summary = $tblBasketVerification->getSummaryPrice())){
                        // Hide Sort by Integer
                        $StringCount = strlen($Summary) - 5;
                        $SortSummary = substr(str_replace(',', '', $Summary), 0, $StringCount);
                        $Item['Summary'] = '<span hidden>'.$SortSummary.'</span>'.ApiBasketVerification::receiverItemSummary($Summary,
                                $tblBasketVerification->getId());
                    }

                    // Add ChangeButton to PersonDebtor
                    if(!$tblBasket->getIsDone()){
                        $Item['PersonDebtor'] .= '&nbsp;'.new ToolTip((new Link('', ApiBasketVerification::getEndpoint(), new Pencil()))
                                ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenEditDebtorSelectionModal($tblBasketVerification->getId()))
                                , 'Beitragszahler ändern');
                    }

                    //ToDO API für's löschen
                    if($tblBasket->getIsDone()){
                        $Item['Option'] = '';
                    } else {
                        $Item['Option'] = (new Standard(new DangerText(new Disable()),
                            ApiBasketVerification::getEndpoint(), null
                        /*, array(),'Eintrag löschen'*/))
                            ->ajaxPipelineOnClick(ApiBasketVerification::pipelineOpenDeleteDebtorSelectionModal('deleteDebtorSelection',
                                $tblBasketVerification->getId()));
                    }

                    array_push($TableContent, $Item);
                });
            $CountArray['DebtorNumberMiss'] = $DebtorNumberMiss;
            $CountArray['DebtorMiss'] = $DebtorMiss;

            $Title = '';
            $DebtorNumberMissCount = 0;
            $DebtorMissCount = 0;
            foreach($CountArray as $Key => $Count) {
                switch($Key) {
                    case 'AllCount':
                        $Title = 'Anzahl der Zahlungszuordnungen:';
                        break;
                    case 'DebtorNumberMiss':
                        $Title = 'Anzahl fehlernder Debitoren-Nr.:';
                        if($Count > 0 && $IsDebtorNumberNeed){
                            $Title = new DangerText($Title);
                            $DebtorNumberMissCount = $Count;
                        }
                        break;
                    case 'DebtorMiss':
                        $Title = 'Anzahl fehlender Zahlungszuweisungen:';
                        if($Count > 0){
                            $Title = new DangerText($Title);
                            $DebtorMissCount = $Count;
                        }
                        break;
                }
                $PanelContent .= new Container(new Bold($Title).' '.$Count);
            }
            if($tblBasket->getIsDone()){
                $ButtonInvoice = '';
            } else {
                $ButtonInvoice = new Primary('Abrechnung starten', '/Billing/Bookkeeping/Basket/InvoiceLoad'
                    , null, array('BasketId' => $BasketId));
                $reloadButton = new Standard('', '', new Repeat(), array(), 'Kontrolle erneut starten');
                if($IsDebtorNumberNeed){
                    if($DebtorMissCount || $DebtorNumberMissCount){
                        $ButtonInvoice->setDisabled();
                        $ButtonInvoice .= $reloadButton;
//                    } else {
//                        $ButtonInvoice = (new Primary('Rechnungen erstellen', '/Billing/Bookkeeping/Basket/InvoiceLoad'
//                            , null, array('BasketId' => $BasketId)));
                    }
                } else {
                    if($DebtorMissCount){
                        $ButtonInvoice->setDisabled();
                        $ButtonInvoice .= $reloadButton;
//                    } else {
//                        $ButtonInvoice = (new Primary('Rechnungen erstellen', '/Billing/Bookkeeping/Basket/InvoiceLoad'
//                            , null, array('BasketId' => $BasketId)));
                    }
                }

            }

            $PanelContent = new Layout(new LayoutGroup(new LayoutRow(array(
                new LayoutColumn(
                    $PanelContent
                    , 10),
                new LayoutColumn(
                    $ButtonInvoice, 2)
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
                                'Item'             => 'Beitragsart',
                                'Price'            => 'Einzelpreis',
                                'Quantity'         => 'Anzahl',
                                'Summary'          => 'Gesamtpreis',
                                'Option'           => ''
                            ), array(
                                'columnDefs' => array(
                                    array('type'    => Consumer::useService()->getGermanSortBySetting(),
                                          'targets' => array(0, 2)
                                    ),
                                    array('type' => 'natural', 'targets' => array(4, 6)),
                                    array("orderable" => false, "targets" => array(5, -1)),
                                ),
                                'order'      => array(
                                    array(1, 'desc'),
                                    array(0, 'asc')
                                ),
                                // First column should not be with Tabindex
                                // solve the problem with responsive false
                                "responsive" => false,
                            )
                        )
                    ),
                ))
            )
        );
    }

    /**
     * @param string $BasketId
     *
     * @return Stage|string
     */
    public function frontendInvoiceLoad($BasketId = '')
    {

        $Stage = new Stage('Rechnungen', 'in Arbeit');

        if(!($tblBasket = Basket::useService()->getBasketById($BasketId))){
            return $Stage->setContent(new Danger('Der Warenkorb wird nicht mehr gefunden.'))
                .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        (new ProgressBar(0, 100, 0, 10))
                            ->setColor(ProgressBar::BAR_COLOR_SUCCESS, ProgressBar::BAR_COLOR_SUCCESS)
                    ),
                    new LayoutColumn(
                        new RedirectScript('/Billing/Bookkeeping/Basket/DoInvoice', 0, array('BasketId' => $BasketId))
                    ),
                ))
            )
        ));
        return $Stage;
    }

    /**
     * @param string $BasketId
     *
     * @return Stage|string
     */
    public function frontendDoInvoice($BasketId = '')
    {

        $Stage = new Stage('Rechnungen', 'in Arbeit');

        if(!($tblBasket = Basket::useService()->getBasketById($BasketId))){
            return $Stage->setContent(new Danger('Der Warenkorb wird nicht mehr gefunden.'))
                .new Redirect('/Billing/Bookkeeping/Basket', Redirect::TIMEOUT_ERROR);
        }
        $Stage->setContent(new Layout(
            new LayoutGroup(
                new LayoutRow(array(
                    new LayoutColumn(
                        new Success('Rechnungen erstellt')
                    ),
                    new LayoutColumn(
                        new Container(Invoice::useService()->createInvoice($tblBasket))
                    ),
                ))
            )
        ));
        return $Stage;
    }
}
