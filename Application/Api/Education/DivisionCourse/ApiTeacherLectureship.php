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

class ApiTeacherLectureship  extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadTeacherLectureshipContent');
        $Dispatcher->registerMethod('loadCheckCoursesContent');
        $Dispatcher->registerMethod('saveTeacherLectureship');

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
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadTeacherLectureshipContent($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'TeacherLectureshipContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTeacherLectureshipContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Filter
     *
     * @return string
     */
    public function loadTeacherLectureshipContent($Filter = null): string
    {
        return DivisionCourse::useFrontend()->loadTeacherLectureshipTable($Filter);
    }

    /**
     * @param null $Filter
     * @param null $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadCheckCoursesContent($Filter = null, $PersonId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CheckCoursesContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadCheckCoursesContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter,
            'PersonId' => $PersonId,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Filter
     * @param null $PersonId
     * @param null $Data
     *
     * @return string
     */
    public function loadCheckCoursesContent($Filter = null, $PersonId = null, $Data = null): string
    {
        return DivisionCourse::useFrontend()->loadCheckCoursesContent($Filter, $PersonId, $Data);
    }

    /**
     * @param null $Filter
     * @param null $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineSaveTeacherLectureship($Filter = null, $PersonId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CheckCoursesContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveTeacherLectureship',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter,
            'PersonId' => $PersonId,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $Filter
     * @param null $PersonId
     * @param null $Data
     *
     * @return string
     */
    public function saveTeacherLectureship($Filter = null, $PersonId = null, $Data = null): string
    {
        return DivisionCourse::useService()->createTeacherLectureship($Filter, $PersonId, $Data);
    }
}