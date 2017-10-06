<?php

namespace SPHERE\Application\Api\Document\Standard\Repository\StudentTransfer;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiStudentTransfer
 * @package SPHERE\Application\Api\Document\Standard\Repository\StudentTransfer
 */
class ApiStudentTransfer extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('serviceButton');
        $Dispatcher->registerMethod('serviceWait');

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
     * @param int  $PersonId
     * @param bool $IsReady
     *
     * @return Pipeline
     */
    public static function pipelineButtonRefresh($PersonId, $IsReady = false)
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());

        // save POST for second Emitter
        $Post = $_POST['Data'];
        // add PersonId to Data
        $PersonIdArray = array('PersonId' => $PersonId);
        $Post = array_merge($Post, $PersonIdArray);

        if ($IsReady) {
            $Emitter->setGetPayload(array(
                self::API_TARGET => 'serviceWait'
            ));
            $Pipeline->appendEmitter($Emitter);
        }

        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'serviceButton',
            'IsReady'        => $IsReady
        ));
        $Emitter->setPostPayload(array(
            'IsReady'  => $IsReady,
            'Data'     => $Post,
            'PersonId' => $PersonId
        ));
//        $Emitter->setLoadingMessage('Lädt');
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    /**
     * @return string
     */
    public function serviceWait()
    {
        return new Bold(new Muted('Eingaben bestätigen')).
            new Container(new ProgressBar(0, 100, 0, 10))
            .new Container('&nbsp;');
    }

    /**
     * @param array $Data
     * @param bool  $IsReady
     * @param int   $PersonId
     *
     * @return External|Standard|string
     */
    public function serviceButton($Data = array(), $IsReady = false, $PersonId)
    {

        return new External('Herunterladen',
                'SPHERE\Application\Api\Document\Standard\StudentTransfer\Create',
                new Download(), array('Data' => $Data),
                'Schulbescheinigung herunterladen')
            .new Container('&nbsp;');

//        if ($IsReady == 'true') {
//            return new External('Herunterladen',
//                    'SPHERE\Application\Api\Document\Standard\StudentTransfer\Create',
//                    new Download(), array('Data' => $Data),
//                    'Schulbescheinigung herunterladen')
//                .new Container('&nbsp;');
//        } else {
//            return (new Standard('Eingaben bestätigen',
//                    ApiStudentTransfer::getEndpoint()))->ajaxPipelineOnClick(ApiStudentTransfer::pipelineButtonRefresh($PersonId,
//                    true))
//                .new Container('&nbsp;');
//        }

//        return new Code(print_r($Data, true));
//        return new RedirectScript('/Api\/Document\/Standard\/StudentTransfer\/Create', false, array('Data' => $Data));
    }
}