<?php
namespace SPHERE\Application\Api\People\Meta\Integration;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\Education\Integration\Integration;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\System\Extension\Extension;

/**
 *
 */
class ApiIntegration extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('loadFilter');
        $Dispatcher->registerMethod('loadSupport');
        $Dispatcher->registerMethod('loadSpecial');
        $Dispatcher->registerMethod('loadHandyCap');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverFilter($Content)
    {

        return (new InlineReceiver($Content))->setIdentifier('Filter');
    }

    /**
     * @return InlineReceiver
     */
    public static function receiverTable($Content)
    {

        return (new InlineReceiver($Content))->setIdentifier('Table');
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadFilter($FilterSelect = '')
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverFilter(''), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiIntegration::API_TARGET => 'loadFilter',
        ));
        $ModalEmitter->setPostPayload(array(
//            'PersonId' => $PersonId
            'FilterSelect' => $FilterSelect
        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadSupport()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverTable(''), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiIntegration::API_TARGET => 'loadSupport',
        ));
//        $ModalEmitter->setPostPayload(array(
//            'PersonId' => $PersonId
//        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadSpecial()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverTable(''), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiIntegration::API_TARGET => 'loadSpecial',
        ));
//        $ModalEmitter->setPostPayload(array(
//            'PersonId' => $PersonId
//        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineLoadHandyCap()
    {
        $Pipeline = new Pipeline(false);
        $ModalEmitter = new ServerEmitter(self::receiverTable(''), self::getEndpoint());
        $ModalEmitter->setGetPayload(array(
            ApiIntegration::API_TARGET => 'loadHandyCap',
        ));
//        $ModalEmitter->setPostPayload(array(
//            'PersonId' => $PersonId
//        ));
        $Pipeline->appendEmitter($ModalEmitter);

        return $Pipeline;
    }

    /**
     * @return string
     */
    public static function loadFilter($FilterSelect = '')
    {

        if($FilterSelect == 1){
            return (new Well(new Center(Integration::useFrontend()->getFilterChange())))->setPadding('5px')->setMarginBottom('5px').
                self::pipelineLoadSupport();
        } elseif($FilterSelect == 2){
            return (new Well(new Center(Integration::useFrontend()->getFilterChange())))->setPadding('5px')->setMarginBottom('5px').
                self::pipelineLoadSpecial();
        } elseif ($FilterSelect == 3){
            return (new Well(new Center(Integration::useFrontend()->getFilterChange())))->setPadding('5px')->setMarginBottom('5px').
                self::pipelineLoadHandyCap();
        }
        return new Well('Filter Platzhalter '.$FilterSelect);
    }

    /**
     * @return string
     */
    public static function loadTable()
    {

        return 'Tabelle ist leer';
    }

    /**
     * @return string
     */
    public static function loadSupport()
    {

        return Integration::useFrontend()->getTableSupport();
    }

    /**
     * @return string
     */
    public static function loadSpecial()
    {

        return Integration::useFrontend()->getTableSpecial();
    }

    /**
     * @return string
     */
    public static function loadHandyCap()
    {

        return Integration::useFrontend()->getTableHandyCap();
    }
}