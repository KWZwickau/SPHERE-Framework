<?php
namespace SPHERE\Application\Api\Setting\ItsLearning;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\ItsLearning\ItsLearning;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;

/**
 * Class ApiItsLearning
 * @package SPHERE\Application\Api\Setting\ItsLearning
 */
class ApiItsLearning implements IApiInterface
{

    // registered method
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('loadContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverContent(string $Content = ''): BlockReceiver
    {

        return (new BlockReceiver($Content))->setIdentifier('ItsLearningExport');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoad(): Pipeline
    {

        $Receiver = self::receiverContent();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItsLearning::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'loadContent'
        ));
//        $Emitter->setPostPayload(array());
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function loadContent(): string
    {
        return ItsLearning::useFrontend()->loadContentComplete();
    }
}