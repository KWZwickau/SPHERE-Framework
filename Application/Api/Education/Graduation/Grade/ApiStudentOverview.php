<?php

namespace SPHERE\Application\Api\Education\Graduation\Grade;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

class ApiStudentOverview extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadViewStudentOverviewCourseSelect');
        $Dispatcher->registerMethod('loadStudentOverviewSelectCourseFilterContent');
        $Dispatcher->registerMethod('loadViewStudentOverviewCourseContent');
        $Dispatcher->registerMethod('loadViewStudentOverviewStudentContent');

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
    public static function pipelineLoadViewStudentOverviewCourseSelect($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewStudentOverviewCourseSelect',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    public function loadViewStudentOverviewCourseSelect($Filter): string
    {
        return Grade::useFrontend()->loadViewStudentOverviewCourseSelect($Filter);
    }

    /**
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadStudentOverviewSelectCourseFilterContent($Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'StudentOverviewSelectCourseFilterContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadStudentOverviewSelectCourseFilterContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Filter
     *
     * @return string
     */
    public function loadStudentOverviewSelectCourseFilterContent($Filter): string
    {
        return Grade::useFrontend()->loadStudentOverviewSelectCourseFilterContent($Filter);
    }

    /**
     * @param $DivisionCourseId
     * @param $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewStudentOverviewCourseContent($DivisionCourseId, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewStudentOverviewCourseContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'Filter' => $Filter
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $Filter
     *
     * @return string
     */
    public function loadViewStudentOverviewCourseContent($DivisionCourseId, $Filter): string
    {
        return Grade::useFrontend()->loadViewStudentOverviewCourseContent($DivisionCourseId, $Filter);
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param null $Filter
     * @param string $View
     *
     * @return Pipeline
     */
    public static function pipelineLoadViewStudentOverviewStudentContent($DivisionCourseId, $PersonId, $Filter = null, string $View = 'Parent'): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Content'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadViewStudentOverviewStudentContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'DivisionCourseId' => $DivisionCourseId,
            'PersonId' => $PersonId,
            'Filter' => $Filter,
            'View' => $View
        ));
        $ModalEmitter->setLoadingMessage("Daten werden geladen");
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $DivisionCourseId
     * @param $PersonId
     * @param $Filter
     * @param $View
     *
     * @return string
     */
    public function loadViewStudentOverviewStudentContent($DivisionCourseId, $PersonId, $Filter, $View): string
    {
        return Grade::useFrontend()->loadViewStudentOverviewStudentContent($DivisionCourseId, $PersonId, $Filter, $View);
    }
}