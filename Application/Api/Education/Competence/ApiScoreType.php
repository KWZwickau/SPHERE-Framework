<?php

namespace SPHERE\Application\Api\Education\Competence;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Competence\ScoreType\ScoreType;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\System\Extension\Extension;

class ApiScoreType extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('loadScoreTypeTable');
        $Dispatcher->registerMethod('loadEditScoreTypeContent');
        $Dispatcher->registerMethod('saveEditScoreType');

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
     * @return Pipeline
     */
    public static function pipelineLoadScoreTypeTable(): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'ScoreTypeTable'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadScoreTypeTable',
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     * @noinspection PhpUnused
     */
    public function loadScoreTypeTable(): string
    {
        return ScoreType::useFrontend()->loadScoreTypeTable();
    }

    /**
     * @param null $ScoreTypeId
     * @param null $Action
     * @param null $ActionId
     *
     * @return Pipeline
     */
    public static function pipelineLoadEditScoreTypeContent($ScoreTypeId = null, $Action = null, $ActionId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'EditScoreTypeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadEditScoreTypeContent',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId,
            'Action' => $Action,
            'ActionId' => $ActionId
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $ScoreTypeId
     * @param null $Action
     * @param null $ActionId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function loadEditScoreTypeContent($ScoreTypeId = null, $Action = null, $ActionId = null, $Data = null): string
    {
        return ScoreType::useFrontend()->loadEditScoreTypeContent(false, $ScoreTypeId, $Action, $ActionId, $Data);
    }

    /**
     * @param $ScoreTypeId
     *
     * @return Pipeline
     */
    public static function pipelineSaveEditScoreType($ScoreTypeId = null): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'EditScoreTypeContent'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'saveEditScoreType',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreTypeId' => $ScoreTypeId
        ));
        $ModalEmitter->setLoadingMessage('Daten werden geladen');
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $ScoreTypeId
     * @param null $Data
     *
     * @return string
     * @noinspection PhpUnused
     */
    public function saveEditScoreType($ScoreTypeId = null, $Data = null): string
    {
        $tblScoreType = null;
        if ($ScoreTypeId) {
            $tblScoreType = ScoreType::useService()->getScoreTypeById($ScoreTypeId);
        }

        return ScoreType::useService()->updateScoreType($Data, $tblScoreType);
    }
}