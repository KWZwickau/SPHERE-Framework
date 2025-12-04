<?php
namespace SPHERE\Application\Api\Transfer\Indiware;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSetting;
use SPHERE\Application\Transfer\Indiware\ErrorLog\ErrorLog;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Layout\Repository\Headline;
use SPHERE\System\Extension\Extension;

class ApiIndiware extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('showUrl');
        $Dispatcher->registerMethod('hideButton');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return InlineReceiver
     */
    public static function receiverContent(string $Content = '', $Identifer = ''): InlineReceiver
    {

        return (new InlineReceiver($Content))->setIdentifier($Identifer);
    }


    public static function pipelineShowUrl()
    {

        $FieldPipeline = new Pipeline();
        $Receiver = self::receiverContent('', 'HideButton');
        $FieldEmitter = new ServerEmitter($Receiver, ApiIndiware::getEndpoint());
        $FieldEmitter->setGetPayload(array(ApiIndiware::API_TARGET => 'hideButton'));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $Receiver = self::receiverContent('', 'ShowURL');
        $FieldEmitter = new ServerEmitter($Receiver, ApiIndiware::getEndpoint());
        $FieldEmitter->setGetPayload(array(ApiIndiware::API_TARGET => 'showUrl'));
        $FieldPipeline->appendEmitter($FieldEmitter);
        return $FieldPipeline;
    }

    public function showUrl()
    {

        $Errorlog = new ErrorLog();
        return $Errorlog->getStyledApiURL();
    }

    public function hideButton()
    {

        return '';
    }
}