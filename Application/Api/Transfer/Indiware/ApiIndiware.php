<?php
namespace SPHERE\Application\Api\Transfer\Indiware;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\ClassRegister\Timetable\Timetable;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Transfer\Indiware\ErrorLog\ErrorLog;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Text\Repository\Bold;
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
        $Dispatcher->registerMethod('showlastJSONContent');

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

    /**
     * @param string $fileName
     *
     * @return Pipeline
     */
    public static function pipelineShowLastJSONContent(): Pipeline
    {

        $Receiver = Self::receiverContent('', 'ShowJSON');
        $FieldPipeline = new Pipeline();
        $FieldEmitter = new ServerEmitter($Receiver, ApiIndiware::getEndpoint());
        $FieldEmitter->setGetPayload(array(
            ApiIndiware::API_TARGET => 'showlastJSONContent'
        ));
        $FieldPipeline->appendEmitter($FieldEmitter);
        $FieldPipeline->setLoadingMessage('Lädt...');

        return $FieldPipeline;
    }

    /**
     * @return string
     */
    public function showlastJSONContent()
    {

        $Time = 'Kein Upload vorhanden';
        $Json = '';
        if(($tblReplacementPut = Timetable::useService()->getTimetableReplacementPutLast())){
            $CreateTime = $tblReplacementPut->getEntityCreate();
            $Time = $CreateTime->format('H:i:s d.m.Y');
            $Json = '<pre>'.$tblReplacementPut->getValue().'</pre>';
        }

        return new Container('JSON Zeitpunkt: &nbsp;'.new Bold($Time)). new Well(new Container($Json));
    }
}