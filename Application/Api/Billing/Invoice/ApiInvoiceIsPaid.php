<?php
namespace SPHERE\Application\Api\Billing\Invoice;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Form\Repository\Field\CheckBox;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\System\Extension\Extension;

class ApiInvoiceIsPaid extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload ColumnContent
        $Dispatcher->registerMethod('getColumnContent');
        // change IsPaid
        $Dispatcher->registerMethod('changeIsPaid');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverService()
    {

        return (new InlineReceiver())->setIdentifier('IsPaidService');
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverIsPaid($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('IsPaid'.$Identifier);
    }

    /**
     * @param string $InvoiceItemDebtorId
     * @param string $IsDocumentWarning
     *
     * @return Pipeline
     */
    public static function pipelineChangeIsPaid($InvoiceItemDebtorId = '', $IsDocumentWarning = 'false')
    {
        $Pipeline = new Pipeline(false);
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'changeIsPaid'
        ));
        $Emitter->setPostPayload(array(
            'InvoiceItemDebtorId' => $InvoiceItemDebtorId,
            'IsDocumentWarning'   => $IsDocumentWarning
        ));
        $Emitter->setLoadingMessage('Speichern erfolgreich!');
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $InvoiceItemDebtorId
     * @param string $IsDocumentWarning
     *
     * @return Pipeline
     */
    public static function pipelineReloadIsPaid($InvoiceItemDebtorId = '', $IsDocumentWarning = 'false')
    {
        $Pipeline = new Pipeline(false);
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverIsPaid('', $InvoiceItemDebtorId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getColumnContent'
        ));

        $Emitter->setPostPayload(array(
            'InvoiceItemDebtorId' => $InvoiceItemDebtorId,
            'IsDocumentWarning' => $IsDocumentWarning
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $InvoiceItemDebtorId
     * @param string   $IsDocumentWarning
     *
     * @return CheckBox|string
     */
    public function getColumnContent($InvoiceItemDebtorId = '', $IsDocumentWarning = 'false')
    {

        $tblInvoiceItemDebtor = Invoice::useService()->getInvoiceItemDebtorById($InvoiceItemDebtorId);
        $content = '';
        if($tblInvoiceItemDebtor){
            $content = (new CheckBox('IsPaid', ' ', $InvoiceItemDebtorId))->ajaxPipelineOnClick(
                self::pipelineChangeIsPaid($InvoiceItemDebtorId, $IsDocumentWarning));
            if(!$tblInvoiceItemDebtor->getIsPaid()){
                $content->setChecked();
            }

            // Mahnung nur bei Offenen Posten
            if($IsDocumentWarning !== 'false'){
                $content = $content.'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.(new External('', '/Api/Document/Standard/BillingDocumentWarning/Create',
                        new Download(), array('Data' => array('InvoiceItemDebtorId' => $tblInvoiceItemDebtor->getId()))
                        , 'Download Mahnung', External::STYLE_BUTTON_PRIMARY));
            }
        }
        return $content;
    }

    /**
     * @param string $InvoiceItemDebtorId
     * @param string $IsDocumentWarning
     *
     * @return string
     */
    public function changeIsPaid($InvoiceItemDebtorId, $IsDocumentWarning = 'false')
    {

        $tblInvoiceItemDebtor = Invoice::useService()->getInvoiceItemDebtorById($InvoiceItemDebtorId);
        if($tblInvoiceItemDebtor){
            Invoice::useService()->changeInvoiceItemDebtorIsPaid($tblInvoiceItemDebtor,
                !$tblInvoiceItemDebtor->getIsPaid());
        }
        return self::pipelineReloadIsPaid($InvoiceItemDebtorId, $IsDocumentWarning);
    }
}