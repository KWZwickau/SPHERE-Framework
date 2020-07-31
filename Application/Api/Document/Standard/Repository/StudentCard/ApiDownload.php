<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentCard;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Document\Standard\StudentCard\Frontend;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Setting\Consumer\Consumer;
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
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('loadTable');
        $Dispatcher->registerMethod('checkLock');

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
     * @param bool $isLocked
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineLoadTable($isLocked, $YearId)
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'Table'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'loadTable',
        ));
        $ModalEmitter->setPostPayload(array(
            'isLocked' => $isLocked,
            'YearId' => $YearId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @param $YearId
     *
     * @return Pipeline
     */
    public static function pipelineCheckLock($YearId)
    {
        $Pipeline = new Pipeline(false);

        $ModalEmitter = new ServerEmitter(self::receiverBlock('', 'CheckLock'), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            self::API_TARGET => 'checkLock',
        ));
        $ModalEmitter->setPostPayload(array(
            'YearId' => $YearId
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        $Pipeline->repeatPipeline(2);

        return $Pipeline;
    }

    /**
     * @param string $isLocked
     * @param null $YearId
     *
     * @return string
     */
    public function loadTable($isLocked = '', $YearId = null)
    {
        return (new Frontend())->loadTable($isLocked === 'true', $YearId);
    }

    /**
     * @param null $YearId
     *
     * @return Pipeline|string
     */
    public function checkLock($YearId = null)
    {
        $isLocked = false;
        $isReload = false;
        if (($tblAccount = Account::useService()->getAccountBySession())
            && ($tblAccountDownloadLock = Consumer::useService()->getAccountDownloadLock($tblAccount, 'StudentCard'))
        ) {
            $isLocked = $tblAccountDownloadLock->getIsFrontendLocked();
            $isLockedLastLoad = $tblAccountDownloadLock->getIsLockedLastLoad();

            if ($isLocked != $isLockedLastLoad) {
                $isReload = true;
                Consumer::useService()->createAccountDownloadLock(
                    $tblAccount,
                    $tblAccountDownloadLock->getDateTime(),
                    'StudentCard',
                    $isLocked,
                    $isLocked
                );
            }
        } elseif ($tblAccount) {
            Consumer::useService()->createAccountDownloadLock(
                $tblAccount,
                new \DateTime('now'),
                'StudentCard',
                false,
                false
            );
            $isReload = true;
        }

        if ($isReload) {
            return self::pipelineLoadTable($isLocked, $YearId);
        }

        return '';
    }
}