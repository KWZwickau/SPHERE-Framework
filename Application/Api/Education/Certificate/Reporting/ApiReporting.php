<?php

namespace SPHERE\Application\Api\Education\Certificate\Reporting;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Certificate\Reporting\Reporting;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

class ApiReporting extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadReportingOverview');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadReportingOverview(): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ReportingOverview'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadReportingOverview',
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function loadReportingOverview($Data = null): string
    {
        $tblYear = false;
        if (isset($Data['YearId'])) {
            $tblYear = Term::useService()->getYearById($Data['YearId']);
        }

        return Reporting::useFrontend()->loadReportingOverview($tblYear ?: null);
    }
}