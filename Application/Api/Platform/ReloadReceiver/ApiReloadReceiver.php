<?php
namespace SPHERE\Application\Api\Platform\ReloadReceiver;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\System\Extension\Extension;

class ApiReloadReceiver extends Extension implements IApiInterface
{

    // registered method
    use ApiTrait;

    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        // reload
        $Dispatcher->registerMethod('getReload');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverReload($Content = '')
    {

        return (new BlockReceiver($Content))->setIdentifier('reload');
    }

    /**
     * @param string $Time // 10min -2sec.
     *
     * @return Pipeline
     */
    public static function pipelineReload($Time = (60 * 10 - 2))
    {
        $Pipeline = new Pipeline();
        // reload
        $Emitter = new ServerEmitter(self::receiverReload(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getReload'
        ));
//        $Emitter->setLoadingMessage('Speichern erfolgreich!');
        $Pipeline->appendEmitter($Emitter);
        $Pipeline->repeatPipeline($Time);
        return $Pipeline;
    }

    /**
     * @return string
     */
    public function getReload()
    {

//        $Timeout = '-NA-';
//        if(($tblAccount = Account::useService()->getAccountBySession())){
//            if(($tblSessionList = Account::useService()->getSessionAllByAccount($tblAccount))){
//                $tblSession = current($tblSessionList);
//                $Timeout = gmdate("H:i:s", $tblSession->getTimeout() - time());
//            }
//        }
        return ''; // 'Timout :'.$Timeout.' <br/>Time: &nbsp;&nbsp;&nbsp;'.(new \DateTime())->format('H.i.s');
    }
}