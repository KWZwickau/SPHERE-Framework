<?php

namespace SPHERE\Application\Api\Reporting\Individual;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiIndividual
 * @package SPHERE\Application\Api\Reporting\Individual
 */
class ApiIndividual extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('getFilter');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverNavigation($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverNavigation');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverFilter($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverFilter');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverResult($Content = '')
    {
        return (new BlockReceiver($Content))
            ->setIdentifier('ReceiverResult');
    }

    public static function pipelineNavigation()
    {

        $Pipeline = new Pipeline();
        $Emitter = new ServerEmitter(self::receiverNavigation(), self::getEndpoint());
        $Emitter->setGetPayload(array(
            self::API_TARGET => 'getFilter'
        ));
        $Emitter->setLoadingMessage('Lädt');
        $Pipeline->appendEmitter($Emitter);
        return $Pipeline;
    }

    public function getFilter()
    {
        return new Panel('Filter', 'Content');
    }
}