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
     *
     * @return Pipeline
     */
    public static function pipelineChangeIsPaid($InvoiceItemDebtorId = '')
    {
        $Pipeline = new Pipeline(false);
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'changeIsPaid'
        ));
        $Emitter->setPostPayload(array(
            'InvoiceItemDebtorId' => $InvoiceItemDebtorId
        ));
        $Emitter->setLoadingMessage('Speichern erfolgreich!');
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $InvoiceItemDebtorId
     *
     * @return Pipeline
     */
    public static function pipelineReloadIsPaid($InvoiceItemDebtorId = '')
    {
        $Pipeline = new Pipeline(false);
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverIsPaid('', $InvoiceItemDebtorId), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getColumnContent'
        ));

        $Emitter->setPostPayload(array(
            'InvoiceItemDebtorId' => $InvoiceItemDebtorId
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param string $InvoiceItemDebtorId
     *
     * @return CheckBox|string
     */
    public function getColumnContent($InvoiceItemDebtorId = '')
    {

        $tblInvoiceItemDebtor = Invoice::useService()->getInvoiceItemDebtorById($InvoiceItemDebtorId);
        $content = '';
        if($tblInvoiceItemDebtor){
            $content = (new CheckBox('IsPaid', ' ', $InvoiceItemDebtorId))->ajaxPipelineOnClick(
                self::pipelineChangeIsPaid($InvoiceItemDebtorId));
            if(!$tblInvoiceItemDebtor->getIsPaid()){
                $content->setChecked();
            }
        }
        return $content;
    }

    /**
     * @param $InvoiceItemDebtorId
     *
     * @return string
     */
    public function changeIsPaid($InvoiceItemDebtorId)
    {

        $tblInvoiceItemDebtor = Invoice::useService()->getInvoiceItemDebtorById($InvoiceItemDebtorId);
        if($tblInvoiceItemDebtor){
            Invoice::useService()->changeInvoiceItemDebtorIsPaid($tblInvoiceItemDebtor, !$tblInvoiceItemDebtor->getIsPaid());
        }
        return self::pipelineReloadIsPaid($InvoiceItemDebtorId);
    }
}