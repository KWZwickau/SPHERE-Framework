<?php

namespace SPHERE\Application\Api\Education\Division;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Icon\Repository\MinusSign;
use SPHERE\Common\Frontend\Icon\Repository\PlusSign;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Frontend\Table\Structure\TableData;
use SPHERE\System\Extension\Extension;

class Division extends Extension implements IApiInterface
{

    use ApiTrait;
    const API_DISPATCHER = 'MethodName';

    /**
     * @param string $MethodName Callable Method
     *
     * @return string
     */
    public function exportApi($MethodName = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('tableAvailableSubject');
        $Dispatcher->registerMethod('tableUsedSubject');

        return $Dispatcher->callMethod($MethodName);
    }

    /**
     * @return Pipeline
     */
    public static function pipelinePlus()
    {
        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverAvailable(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_DISPATCHER => 'tableAvailableSubject',
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
        $Emitter = new ServerEmitter(self::receiverUsed(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_DISPATCHER => array('tableUsedSubject'),
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
    public static function receiverUsed($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('UsedReceiver');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverAvailable($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('AvailableReceiver');
    }

    /**
     * @param array $UsedList
     *
     * @return TableData
     */
    public static function tableUsedSubject($UsedList = array())
    {

//        $Ids = array();

        // Service
        // Plus
//        if ($Type == 1) {
//            $Ids[$Id] = $Id;
//        }
//        // Minus
//        if ($Type == 0 && isset($Ids[$Id])) {
//            unset($Ids[$Id]);
//        }

        // Select
        $Table = array();
        foreach ($UsedList as $Subject) {
            $Table[] = array(
                'Acronym'     => $Subject['Acronym'],
                'Name'        => $Subject['Name'],
                'Description' => $Subject['Description'],
                'Option'      => (new Standard('', '#', new MinusSign(),
                    array('Id' => $Subject['Id'])))->ajaxPipelineOnClick(Division::pipelineMinus())
            );
        }

        // Anzeige
        return new TableData($Table, null, array(
            'Acronym'     => 'Kürzel',
            'Name'        => 'Name',
            'Description' => 'Beschreibung',
            'Option'      => 'Option'
        ),
            array(
                'columnDefs' => array(
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
            )
        );
    }

    /**
     * @param array $AvailableList
     *
     * @return TableData
     */
    public static function tableAvailableSubject($AvailableList = array())
    {

//        $Ids = array();


        // Service
        // Plus
//        if ($Type == 1) {
//            $Ids[$Id] = $Id;
//        }
//        // Minus
//        if ($Type == 0 && isset($Ids[$Id])) {
//            unset($Ids[$Id]);
//        }

        // Select
        $Table = array();
        foreach ($AvailableList as $Subject) {
            $Table[] = array(
                'Acronym'     => $Subject['Acronym'],
                'Name'        => $Subject['Name'],
                'Description' => $Subject['Description'],
                'Option'      => (new Standard('', '#', new PlusSign(),
                    array('Id' => $Subject['Id'])))->ajaxPipelineOnClick(Division::pipelinePlus())
            );
        }

        // Anzeige
        return new TableData($Table, null, array(
            'Acronym'     => 'Kürzel',
            'Name'        => 'Name',
            'Description' => 'Beschreibung',
            'Option'      => 'Option'
        ),
            array(
                'columnDefs' => array(
                    array('orderable' => false, 'width' => '1%', 'targets' => -1)
                ),
            )
        );
    }
}