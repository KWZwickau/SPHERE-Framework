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

class ApiYearChange extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadYearChangeContent');
        $Dispatcher->registerMethod('saveYearChangeContent');

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
    public static function pipelineLoadYearChangeContent(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'YearChangeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadYearChangeContent',
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Data
     *
     * @return string
     */
    public function loadYearChangeContent($Data = null): string
    {
        return DivisionCourse::useFrontend()->loadYearChangeContent($Data);
    }

    /**
     * @param $SchoolTypeId
     * @param $YearSourceId
     * @param $YearTargetId
     * @param $hasOptionTeacherLectureship
     *
     * @return Pipeline
     */
    public static function pipelineSaveYearChangeContent($SchoolTypeId, $YearSourceId, $YearTargetId, $hasOptionTeacherLectureship): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'YearChangeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveYearChangeContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'YearSourceId' => $YearSourceId,
            'YearTargetId' => $YearTargetId,
            'hasOptionTeacherLectureship' => $hasOptionTeacherLectureship
        ));
        $ModalEmitter->setLoadingMessage('Daten werden gespeichert');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $SchoolTypeId
     * @param $YearSourceId
     * @param $YearTargetId
     * @param $hasOptionTeacherLectureship
     *
     * @return string
     */
    public function saveYearChangeContent($SchoolTypeId, $YearSourceId, $YearTargetId, $hasOptionTeacherLectureship): string
    {
        return DivisionCourse::useFrontend()->saveYearChangeContent(
            $SchoolTypeId, $YearSourceId, $YearTargetId, $hasOptionTeacherLectureship === 'true'
        );
    }
}