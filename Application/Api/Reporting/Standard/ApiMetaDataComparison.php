<?php
namespace SPHERE\Application\Api\Reporting\Standard;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Reporting\Standard\Person\Frontend;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

class ApiMetaDataComparison extends Extension implements IApiInterface
{

    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('reloadCourseSelectbox');

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
    public static function pipelineReloadCourseSelectbox() : Pipeline
    {
        $FieldPipeline = new Pipeline(false);
        $FieldEmitter = new ServerEmitter(self::receiverBlock('', 'reloadCourseSelectbox'), self::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            self::API_TARGET => 'reloadCourseSelectbox'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Kursliste wird aktualisiert');

        return $FieldPipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function reloadCourseSelectbox($Data = null) : string
    {

        $YearId = false;
        if(isset($Data['YearId'])){
            $YearId = $Data['YearId'];
        }
        return (new Frontend())->getDivisionCourseSelectBox($YearId);
    }

}