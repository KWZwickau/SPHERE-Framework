<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\SignOutCertificate;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiSignOutCertificate
 * @package SPHERE\Application\Api\Document\Standard\Repository\SignOutCertificate
 */
class ApiSignOutCertificate extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('downloadButton');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverService($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('Download StudentTransfer');
    }

    /**
     * @param int $PersonId
     *
     * @return Pipeline
     */
    public static function pipelineButtonRefresh($PersonId)
    {

        $Pipeline = new Pipeline();

        // POST Data in variable
        $Data = $_POST['Data'];
        // add PersonId to Data
        $PersonIdArray = array('PersonId' => $PersonId);
        $Data = array_merge($Data, $PersonIdArray);

        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'downloadButton'
        ));
        $Emitter->setPostPayload(array(
            'Data' => $Data,
        ));
//        $Emitter->setLoadingMessage('Lädt');
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @param array $Data
     *
     * @return External|Standard|string
     */
    public function downloadButton($Data = array())
    {

        return new External('Herunterladen',
                'SPHERE\Application\Api\Document\Standard\SignOutCertificate\Create',
                new Download(), array('Data' => $Data),
                'Schulbescheinigung herunterladen')
            .new Container('&nbsp;');
    }
}