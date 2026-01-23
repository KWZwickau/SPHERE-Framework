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
        $Dispatcher->registerMethod('loadCertificatePreview');
        $Dispatcher->registerMethod('loadDownloadButton');

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
     * @param $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadContent($Filter): Pipeline
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverContent('', 'Content'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadContent',
        ));
        $emitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $emitter->setLoadingMessage('Bitte warten', 'Die Zeugnisvorlagen werden geladen');
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $Filter
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadContent($Filter = null): string
    {
        return (new FrontendPreviewCertificate())->loadContent($Filter);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadCertificatePreview(): Pipeline
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverContent('', 'CertificatePreview'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadCertificatePreview',
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadCertificatePreview($Data = null): string
    {
        return (new FrontendPreviewCertificate())->loadCertificatePreview($Data);
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadDownloadButton(): Pipeline
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverContent('', 'DownloadButton'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadDownloadButton',
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadDownloadButton($Data = null): string
    {
        return (new FrontendPreviewCertificate())->loadDownloadButton($Data);
    }
}