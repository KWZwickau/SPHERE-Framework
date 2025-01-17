<?php

namespace SPHERE\Application\Api\ParentStudentAccess;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\ParentStudentAccess\OnlineGradebook\OnlineGradebook;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\Exclamation;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\System\Extension\Extension;

class ApiOnlineGradebook extends Extension implements IApiInterface
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

        $Dispatcher->registerMethod('openScoreRuleModal');

        return $Dispatcher->callMethod($Method);
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
     * @param $ScoreRuleId
     *
     * @return Pipeline
     */
    public static function pipelineOpenScoreRuleModal($ScoreRuleId): Pipeline
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverModal(), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'openScoreRuleModal',
        ));
        $ModalEmitter->setPostPayload(array(
            'ScoreRuleId' => $ScoreRuleId
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param null $ScoreRuleId
     *
     * @return string
     */
    public function openScoreRuleModal($ScoreRuleId = null)
    {
        $tblScoreRule = Grade::useService()->getScoreRuleById($ScoreRuleId);

        if (!($tblScoreRule)) {
            return new Danger('Die Berechnungsvorschrift wurde nicht gefunden', new Exclamation());
        }

        return OnlineGradebook::useFrontend()->getScoreRuleModalContent($tblScoreRule);
    }
}