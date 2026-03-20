<?php

namespace SPHERE\Application\Api\Education\Competence;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Competence\Skill\Skill;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\System\Extension\Extension;

class ApiSkill extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param $Method
     *
     * @return string
     * @noinspection PhpMissingParamTypeInspection
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadSkillGridTable');
        $Dispatcher->registerMethod('loadSkillAreaContent');
        $Dispatcher->registerMethod('loadSkillContent');

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
     * @return ModalReceiver
     */
    public static function receiverModal(): ModalReceiver
    {
        return (new ModalReceiver(null, new Close()))->setIdentifier('ModalReceiver');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineClose(): Pipeline
    {
        $Pipeline = new Pipeline();
        $Pipeline->appendEmitter((new CloseModal(self::receiverModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $Filter
     *
     * @return Pipeline
     */
    public static function pipelineLoadSkillGridTable($SchoolTypeId = null, $Filter = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillGridTable'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSkillGridTable',
        ));
        $ModalEmitter->setPostPayload(array(
            'SchoolTypeId' => $SchoolTypeId,
            'Filter' => $Filter,
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $SchoolTypeId
     * @param null $Filter
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadSkillGridTable($SchoolTypeId = null, $Filter = null): string
    {
        return Skill::useFrontend()->loadSkillGridTable($SchoolTypeId, $Filter);
    }

    /**
     * @param $AreaRanking
     *
     * @return Pipeline
     */
    public static function pipelineLoadSkillAreaContent($AreaRanking): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillAreaContent_' . $AreaRanking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSkillAreaContent',
        ));

        $ModalEmitter->setPostPayload(array(
            'AreaRanking' => $AreaRanking,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AreaRanking
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadSkillAreaContent($AreaRanking): string
    {
        return Skill::useFrontend()->getSkillAreaContent($AreaRanking);
    }

    /**
     * @param $AreaRanking
     * @param $SkillRanking
     *
     * @return Pipeline
     */
    public static function pipelineLoadSkillContent($AreaRanking, $SkillRanking): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'SkillContent_' . $AreaRanking . '_' . $SkillRanking), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadSkillContent',
        ));

        $ModalEmitter->setPostPayload(array(
            'AreaRanking' => $AreaRanking,
            'SkillRanking' => $SkillRanking,
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $AreaRanking
     * @param $SkillRanking
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadSkillContent($AreaRanking, $SkillRanking): string
    {
        return Skill::useFrontend()->getSkillContent($AreaRanking, $SkillRanking);
    }
}