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
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

class ApiInvoiceIsHistory extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // setIsHistory
        $Dispatcher->registerMethod('setIsHistory');
        $Dispatcher->registerMethod('getIsPaidTable');
        // setClearHistory
        $Dispatcher->registerMethod('setClearHistory');
        $Dispatcher->registerMethod('getIsHistoryTable');

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
    public static function receiverIsHistory($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('IsHistory'.$Identifier);
    }

    /**
     * @param string $InvoiceItemDebtorId
     *
     * @return Pipeline
     */
    public static function pipelinesetIsHistory($InvoiceItemDebtorId = '')
    {
        $Pipeline = new Pipeline(false);
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'setIsHistory'
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
     * @return string
     */
    public function setIsHistory(string $InvoiceItemDebtorId): string
    {

        $tblInvoiceItemDebtor = Invoice::useService()->getInvoiceItemDebtorById($InvoiceItemDebtorId);
        if($tblInvoiceItemDebtor){
            Invoice::useService()->changeInvoiceItemDebtorIsHistory($tblInvoiceItemDebtor, true);
        }
        return self::pipelineReloadIsPaidTable();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineReloadIsPaidTable()
    {
        $Pipeline = new Pipeline(false);
        // reload the whole Table
        $Emitter = new ServerEmitter(ApiInvoiceIsPaid::receiverIsPaid('', 'table'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getIsPaidTable'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @return TableData
     */
    public function getIsPaidTable(): TableData
    {

        return Invoice::useFrontend()->getIsPaidTable();
    }

    /**
     * @param string $InvoiceItemDebtorId
     *
     * @return Pipeline
     */
    public static function pipelinesetClearHistory($InvoiceItemDebtorId = '')
    {
        $Pipeline = new Pipeline(false);
        // reload the whole Table
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'setClearHistory'
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
     * @return string
     */
    public function setClearHistory(string $InvoiceItemDebtorId): string
    {

        $tblInvoiceItemDebtor = Invoice::useService()->getInvoiceItemDebtorById($InvoiceItemDebtorId);
        if($tblInvoiceItemDebtor){
            Invoice::useService()->changeInvoiceItemDebtorIsHistory($tblInvoiceItemDebtor);
        }
        return self::pipelineReloadIsHistoryTable();
    }

    /**
     * @return Pipeline
     */
    public static function pipelineReloadIsHistoryTable()
    {
        $Pipeline = new Pipeline(false);
        // reload the whole Table
        $Emitter = new ServerEmitter(ApiInvoiceIsHistory::receiverIsHistory('', 'table'), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getIsHistoryTable'
        ));
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @return TableData
     */
    public function getIsHistoryTable(): TableData
    {

        return Invoice::useFrontend()->getIsPaidTable(true);
    }
}