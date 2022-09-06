<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Document\Standard\StudentCard\Frontend;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiDownload
 *
 * @package SPHERE\Application\Api\Document\Standard\Repository\StudentCard
 */
class ApiDownload extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = '') : string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadTable');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     * @param string $Identifier
     *
     * @return BlockReceiver
     */
    public static function receiverBlock(string $Content = '',string $Identifier = '') : BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier($Identifier);
    }

    /**
     * @param bool $isLocked
     * @param array $filterYearList
     *
     * @return Pipeline
     */
    public static function pipelineLoadTable(bool $isLocked, array $filterYearList) : Pipeline
    {
        $Pipeline = new Pipeline();
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Table'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTable',
            'isLocked' => $isLocked,
            'filterYearList' => empty($filterYearList) ? null : $filterYearList
        ));

        $ModalEmitter->setPostPayload(array(
            'isLocked' => $isLocked,
            'filterYearList' => $filterYearList
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param string $isLocked
     * @param ?array $filterYearList
     *
     * @return string
     */
    public function loadTable(string $isLocked, ?array $filterYearList) : string
    {
        return (new Frontend())->loadTable($isLocked === 'true', $filterYearList);
    }
}