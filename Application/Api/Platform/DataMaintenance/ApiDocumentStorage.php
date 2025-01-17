<?php

namespace SPHERE\Application\Api\Platform\DataMaintenance;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Check;
use SPHERE\Common\Frontend\Icon\Repository\History;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
use SPHERE\Common\Frontend\Link\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\System\Extension\Extension;

class ApiDocumentStorage extends Extension implements IApiInterface
{
    use ApiTrait;

    const STATUS_BUTTON = 'Button';
    const STATUS_WAITING = 'Waiting';
    const STATUS_FINISH = 'Finish';

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('status');
        $Dispatcher->registerMethod('updateFileSize');

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
     * @param $Status
     * @return Pipeline
     */
    public static function pipelineStatus($Status): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Status'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'status',
        ));
        $ModalEmitter->setPostPayload(array(
            'Status' => $Status
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $Status
     * @return string
     */
    public function status($Status): string
    {
        return match ($Status) {
            self::STATUS_BUTTON => (new Danger('Datei-Größen setzen', self::getEndpoint()))
                ->ajaxPipelineOnClick(self::pipelineStatus(self::STATUS_WAITING)),
            self::STATUS_WAITING => (new Warning('Bitte warten. Die Datei-Größen werden gesetzt.', new History()))
                . self::pipeLineUpdateFileSize(0),
            self::STATUS_FINISH => new Success('Alle Datei-Größen wurden erfolgreich gesetzt.', new Check()),
            default => '',
        };
    }

    /**
     * @param $StartId
     *
     * @return Pipeline
     */
    public static function pipeLineUpdateFileSize($StartId): Pipeline
    {
        $Pipeline = new Pipeline(true);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'FileSize_' . $StartId), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'updateFileSize',
        ));
        $ModalEmitter->setPostPayload(array(
            'StartId' => $StartId,
        ));

        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $StartId
     *
     * @return Pipeline|string
     */
    public function updateFileSize($StartId)
    {
        ini_set('memory_limit', '2G');

        if (($list = Storage::useService()->getBinariesWithoutFileSize(1000, $StartId))) {
            $count = count($list);
            $nextId = (end($list))['Id'] + 1;
            return new Success(
                    "Datei-Größen für $count Dateien erfolgreich gesetzt."
                    . new PullRight(Storage::useService()->updateFileSize($list) . ' Sekunden'),
                    new Check()
                )
                . self::receiverBlock('', 'FileSize_' . $nextId)
                . self::pipelineUpdateFileSize($nextId);
        } else {
            return self::pipelineStatus(self::STATUS_FINISH);
        }
    }
}