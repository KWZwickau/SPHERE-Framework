<?php
namespace SPHERE\Application\Api\Billing\Sepa;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Bookkeeping\Basket\Basket;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItemDebtor;
use SPHERE\Application\Billing\Inventory\Setting\Service\Entity\TblSetting;
use SPHERE\Application\Billing\Inventory\Setting\Setting;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Form\Repository\Button\Primary as PrimaryForm;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Form\Repository\Field\HiddenField;
use SPHERE\Common\Frontend\Form\Repository\Field\TextField;
use SPHERE\Common\Frontend\Form\Structure\Form;
use SPHERE\Common\Frontend\Form\Structure\FormColumn;
use SPHERE\Common\Frontend\Form\Structure\FormGroup;
use SPHERE\Common\Frontend\Form\Structure\FormRow;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Link\Repository\ToggleCheckbox;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiSepa
 * @package SPHERE\Application\Api\Billing\Sepa
 */
class ApiSepa extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('showOpenInvoice');
        $Dispatcher->registerMethod('showEndPrice');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return ModalReceiver
     */
    public static function receiverModal()
    {

        return (new ModalReceiver(null, new Close()))->setIdentifier('SepaModal');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return InlineReceiver
     */
    public static function receiverEndPrice($Content = '', $Identifier = '')
    {

        return (new InlineReceiver($Content))->setIdentifier('EndPrice'.$Identifier);
    }

    /**
     * @param string $BasketId
     *
     * @return Pipeline
     */
    public static function pipelineOpenCauserModal($BasketId = '')
    {

        $Receiver = self::receiverModal();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showOpenInvoice'
        ));
        $Emitter->setPostPayload(array(
            'BasketId' => $BasketId
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param float  $SumPrice
     * @param string $Identifier
     *
     * @return Pipeline
     */
    public static function pipelineUpdateEndPrice($SumPrice, $Identifier = '')
    {

        $Receiver = self::receiverEndPrice('', $Identifier);
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'showEndPrice'
        ));
        $Emitter->setPostPayload(array(
            'SumPrice' => $SumPrice,
            'Identifier' => $Identifier
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $BasketId
     *
     * @return string
     */
    public function showOpenInvoice($BasketId = '')
    {

        if(Basket::useService()->getBasketById($BasketId)){
            $TableContent = array();

            $Value = 0;
            if(($Setting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_SEPA_FEE))){
                $Value = $Setting->getValue();
                $Value = str_replace(',', '.', $Value);
            }

            $FeeFieldList = array();
            if(($tblInvoiceItemDebtorList = Invoice::useService()->getInvoiceItemDebtorByIsPaid())){
                array_walk($tblInvoiceItemDebtorList, function(TblInvoiceItemDebtor $tblInvoiceItemDebtor) use (&$TableContent, &$FeeFieldList, $Value){
                    $CauserName = '';
                    $InvoiceTime = '';
                    $InvoiceNumber = '';
                    $FeeFieldList[] = $tblInvoiceItemDebtor->getId();
                    if(($tblInvoice = $tblInvoiceItemDebtor->getTblInvoice())){
                        $InvoiceNumber = $tblInvoice->getInvoiceNumber();
                        $CauserName = $tblInvoice->getLastName().', '.$tblInvoice->getFirstName();
                        $InvoiceTime = $tblInvoice->getYear().'.'.$tblInvoice->getMonth();
                    }

                    $item['Option'] = (new CheckBox('Invoice[CheckboxList][]', '&nbsp;', $tblInvoiceItemDebtor->getId()))->setTabIndex(999);
                    $item['Fee'] = (new TextField('Invoice[Fee]['.$tblInvoiceItemDebtor->getId().']', '', ''))
                    ->ajaxPipelineOnKeyUp(self::pipelineUpdateEndPrice(
                        (float)$tblInvoiceItemDebtor->getSummaryPriceInt(), $tblInvoiceItemDebtor->getId()
                    ));
                    $item['InvoiceNumber'] = $InvoiceNumber;
                    $item['CauserName'] = $CauserName;
                    $item['InvoiceTime'] = $InvoiceTime;
                    $item['Name'] = $tblInvoiceItemDebtor->getName();
//                    $Price = $tblInvoiceItemDebtor->getSummaryPrice();
//                    if(($tblSetting = Setting::useService()->getSettingByIdentifier(TblSetting::IDENT_SEPA_FEE))
//                        && $tblSetting->getValue()){
//                        $Value = str_replace(',', '.', $tblSetting->getValue());
//                        $Value = round($Value, 2);
//                        $Price = $Value + (float)$tblInvoiceItemDebtor->getSummaryPriceInt();
//                        $Price = $tblInvoiceItemDebtor->getSummaryPrice().' + '.$Value.' € ('.$Price.' €)';
//                    }
//                    $Price = str_replace('.', ',', $Price);
//                    $item['SummaryPrice'] = $Price;
                    $item['SummaryPrice'] = $tblInvoiceItemDebtor->getSummaryPrice();
                    $EndPrice = round((float)$tblInvoiceItemDebtor->getSummaryPriceInt() + (float)$Value, 2);
                    $EndPrice = number_format($EndPrice, 2, '.', '');
                    $EndPrice .= ' €';
                    $item['EndPrice'] = self::receiverEndPrice($EndPrice, $tblInvoiceItemDebtor->getId());
                    $item['Owner'] = $tblInvoiceItemDebtor->getOwner();
                    // Es werden nur Sepa-Lastschriften zur Verfügung gestellt
                    if(($tblPaymentType = $tblInvoiceItemDebtor->getServiceTblPaymentType())
                        && $tblPaymentType->getName() == 'SEPA-Lastschrift' ){
                        array_push($TableContent, $item);
                    }
                });
            }
            // set Post
            if(!empty($FeeFieldList)){
                foreach($FeeFieldList as $FeeId){
                    $_POST['Invoice']['Fee'][$FeeId] = $Value;
                }
            }

        } else {
            return new Warning('Der Warenkorb wurde nicht gefunden.');
        }

        $toggleCheckbox = '';
        $Warning = '';
        if(!empty($TableContent)){
            $Warning = new Warning('Offene Posten erneut in SEPA-Lastschrift aufnehmen');
            $FormColumnTable = new FormColumn(
                new TableData($TableContent, null, array(
                    'Option' => 'Erneut',
                    'Fee' => 'Gebühr',
                    'InvoiceNumber' => 'R.Nr.',
                    'CauserName' => 'Beitragsverursacher',
                    'InvoiceTime' => 'Abr. Monat',
                    'Name' => 'Beitragsart',
                    'SummaryPrice' => 'Preis',
                    'EndPrice' => 'Gesamtpreis',
                    'Owner' => 'Beitragszahler',
                ), // null
                    array(
                        "paging" => false, // Deaktiviert Blättern
                        "iDisplayLength" => -1,    // Alle Einträge zeigen
                        "searching" => false, // Deaktiviert Suche
                        "info" => false, // Deaktiviert Such-Info)
//                        "responsive" => false,
                    )
                )
            );
            $form = new Form(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(array(
                            new HiddenField('Invoice[BasketId]'),
                        )),
                        $FormColumnTable,
                    ))
                ), new PrimaryForm('&nbsp;SEPA Download', new Download(), true), '\Api\Billing\Sepa\Download'
            );
            $toggleCheckbox = new ToggleCheckbox( 'Alle wählen/abwählen', $form );
        } else {
            $form = new Form(
                new FormGroup(
                    new FormRow(array(
                        new FormColumn(array(
                            new HiddenField('Invoice[BasketId]'),
                        )),
                        new FormColumn(
                            new Success('Es sind keine Offenen Posten vorhanden, die in die SEPA-Lastschrift XML aufgenommen
                            werden könnten.')
                        ),
                    ))
                ), new PrimaryForm('&nbsp;SEPA Download', new Download(), true), '\Api\Billing\Sepa\Download'
            );
        }


        // set hidden POST
        $_POST['Invoice']['BasketId'] = $BasketId;

        return
            new Title('Offene Posten').$Warning.$toggleCheckbox.$form;
    }

    /**
     * @param float  $SumPrice
     * @param string $Identifier
     * @param array  $Invoice
     *
     * @return string
     */
    public function showEndPrice($SumPrice, $Identifier = '', $Invoice = array())
    {

        $EndPrice = $SumPrice;
        if(isset($Invoice['Fee'][$Identifier])){
            $Fee = str_replace(',', '.', $Invoice['Fee'][$Identifier]);
            $EndPrice = round($SumPrice + $Fee, 2);
            $EndPrice = number_format($EndPrice, 2, '.', '');
        }

        return $EndPrice.' €';
    }
}