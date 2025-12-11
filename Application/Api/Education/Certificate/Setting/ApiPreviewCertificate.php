<?php

namespace SPHERE\Application\Api\Education\Certificate\Setting;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Certificate\Setting\FrontendPreviewCertificate;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

class ApiPreviewCertificate extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverContent(string $Content = '', string $Identifier = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadContent(): Pipeline
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverContent('', 'Content'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadContent',
        ));
//        $emitter->setPostPayload(array(
//            'PrepareId' => $PrepareId
//        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $Data
     *
     * @return string
     */
    public function loadContent($Data = null): string
    {
        return (new FrontendPreviewCertificate())->loadContent($Data);
    }
}