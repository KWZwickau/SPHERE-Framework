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

        $Dispatcher->registerMethod('loadYearChangeForCoreGroupContent');
        $Dispatcher->registerMethod('saveYearChangeForCoreGroupContent');

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

    /**
     * @return Pipeline
     */
    public static function pipelineLoadYearChangeForCoreGroupContent(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'YearChangeForCoreGroupContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadYearChangeForCoreGroupContent',
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
    public function loadYearChangeForCoreGroupContent($Data = null): string
    {
        return DivisionCourse::useFrontend()->loadYearChangeForCoreGroupContent($Data);
    }

    /**
     * @param $YearSourceId
     * @param $YearTargetId
     *
     * @return Pipeline
     */
    public static function pipelineSaveYearChangeForCoreGroupContent($YearSourceId, $YearTargetId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'YearChangeForCoreGroupContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveYearChangeForCoreGroupContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'YearSourceId' => $YearSourceId,
            'YearTargetId' => $YearTargetId,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden gespeichert');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $YearSourceId
     * @param $YearTargetId
     *
     * @return string
     */
    public function saveYearChangeForCoreGroupContent($YearSourceId, $YearTargetId): string
    {
        return DivisionCourse::useFrontend()->saveYearChangeForCoreGroupContent(
            $YearSourceId, $YearTargetId
        );
    }
}