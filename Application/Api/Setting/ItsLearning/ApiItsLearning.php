<?php
namespace SPHERE\Application\Api\Setting\ItsLearning;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\ItsLearning\ItsLearning;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Message\Repository\Info;

/**
 * Class ApiItsLearning
 * @package SPHERE\Application\Api\Setting\ItsLearning
 */
class ApiItsLearning implements IApiInterface
{

    // registered method
    use ApiTrait;

    /**
     * @param string $Method
     *
     * @return string
     */
    public function exportApi($Method = ''): string
    {
        $Dispatcher = new Dispatcher(__CLASS__);
        $Dispatcher->registerMethod('waitContent');
        $Dispatcher->registerMethod('loadContent');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverContent(string $Content = ''): BlockReceiver
    {

        return (new BlockReceiver($Content))->setIdentifier('ItsLearningExport');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoad($tblYearId = null): Pipeline
    {

        $Receiver = self::receiverContent();
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter($Receiver, ApiItsLearning::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'waitContent'
        ));
        $Pipeline->appendEmitter($Emitter);
        $Emitter = new ServerEmitter($Receiver, ApiItsLearning::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'loadContent'
        ));
        // Jahr wird bei Seitenaufruf aus der Pipeline gezogen, sonst aus Form (on change)
        if($tblYearId){
            $Emitter->setGetPayload(array(
                self::API_TARGET => 'loadContent',
                'Year' => $tblYearId
            ));
        }
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public function waitContent(): string
    {
        return new Info('Inhalt lädt...'.new ProgressBar(0, 100, 0, 12));
    }

    /**
     * @return string
     */
    public function loadContent($Year = null): string
    {
        return ItsLearning::useFrontend()->loadContentComplete($Year);
    }
}