<?php

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\Common\Main;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Extension\Extension;

class Division extends Extension implements IApiInterface
{

    const API_DISPATCHER = 'MethodName';

    /**
     * @return Route
     */
    public static function getRoute()
    {
        return new Route(__CLASS__);
    }

    public static function registerApi()
    {
        Main::getDispatcher()->registerRoute(Main::getDispatcher()->createRoute(
            __CLASS__, __CLASS__.'::ApiDispatcher'
        ));
    }

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function ApiDispatcher($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('tableBasket');

        return $Dispatcher->callMethod($MethodName);
    }

    /**
     * @return Pipeline
     */
    public static function pipelinePlus()
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverBasket(), self::getRoute());
        $Emitter->setPostPayload(array(
            self::API_DISPATCHER => 'tableBasket',
            'Type'               => 1
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @return Pipeline
     */
    public static function pipelineMinus()
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverBasket(), self::getRoute());
        $Emitter->setPostPayload(array(
            self::API_DISPATCHER => 'tableBasket',
            'Type'               => 0
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverBasket($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('BasketReceiver');
    }

    /**
     * @param null $Id
     * @param null $Type
     *
     * @return TableData
     */
    public static function tableBasket($Id = null, $Type = null)
    {

        $Ids = array();


        // Service
        // Plus
        if ($Type == 1) {
            $Ids[$Id] = $Id;
        }
        // Minus
        if ($Type == 0 && isset($Ids[$Id])) {
            unset($Ids[$Id]);
        }

        // Select
        $Table = array();
        foreach ($Ids as $IdEntity) {
            $Table[] = array(
                'Artikel' => $IdEntity,
                'Option'  => (new Standard('-', '#', null,
                    array('Id' => $IdEntity)))->ajaxPipelineOnClick(Division::pipelineMinus())
            );
        }

        // Anzeige
        return new TableData($Table, null, array('Artikel' => 'Artikel', 'Option' => 'Option'));
    }
}