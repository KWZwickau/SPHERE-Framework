<?php

namespace SPHERE\Application\Api\People\Person;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\People\Person\Frontend\FrontendBasic;
use SPHERE\Application\People\Person\Frontend\FrontendCommon;
use SPHERE\Application\People\Person\Frontend\FrontendProspect;
use SPHERE\Application\People\Person\Frontend\FrontendTeacher;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiPersonReadOnly
 *
 * @package SPHERE\Application\Api\People\Person
 */
class ApiPersonReadOnly extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadBasicContent');
        $Dispatcher->registerMethod('loadCommonContent');
        $Dispatcher->registerMethod('loadProspectTitle');
        $Dispatcher->registerMethod('loadProspectContent');
        $Dispatcher->registerMethod('loadTeacherTitle');
        $Dispatcher->registerMethod('loadTeacherContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock($Content = '', $Identifier = '')
    {

        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadBasicContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'BasicContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadBasicContent',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadCommonContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'CommonContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadCommonContent',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadProspectTitle($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ProspectContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadProspectTitle',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadProspectContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'ProspectContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadProspectContent',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadTeacherTitle($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'TeacherContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadTeacherTitle',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineLoadTeacherContent($PersonId)
    {
        $pipeline = new Pipeline(false);

        $emitter = new ServerEmitter(self::receiverBlock('', 'TeacherContent'), self::getEndpoint());
        $emitter->setGetPayload(array(
            self::API_TARGET => 'loadTeacherContent',
        ));
        $emitter->setPostPayload(array(
            'PersonId' => $PersonId
        ));
        $pipeline->appendEmitter($emitter);

        return $pipeline;
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadBasicContent($PersonId = null)
    {

        return FrontendBasic::getBasicContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadCommonContent($PersonId = null)
    {

        return FrontendCommon::getCommonContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadProspectTitle($PersonId = null)
    {

        return FrontendProspect::getProspectTitle($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadProspectContent($PersonId = null)
    {

        return FrontendProspect::getProspectContent($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadTeacherTitle($PersonId = null)
    {

        return FrontendTeacher::getTeacherTitle($PersonId);
    }

    /**
     * @param null $PersonId
     *
     * @return string
     */
    public function loadTeacherContent($PersonId = null)
    {

        return FrontendTeacher::getTeacherContent($PersonId);
    }
}