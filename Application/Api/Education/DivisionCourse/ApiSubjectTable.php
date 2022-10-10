<?php

namespace SPHERE\Application\Api\Education\DivisionCourse;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

class ApiSubjectTable extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadSubjectTableContent');

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
     * @param null $SchoolTypeId
     *
     * @return Pipeline
     */
    public static function pipelineLoadSubjectTableContent($SchoolTypeId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SubjectTableContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSubjectTableContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     *
     * @return string
     */
    public function loadSubjectTableContent($SchoolTypeId = null): string
    {
        return DivisionCourse::useFrontend()->loadSubjectTableContent($SchoolTypeId);
    }
}