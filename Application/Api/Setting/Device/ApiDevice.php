<?php
namespace SPHERE\Application\Api\Setting\Device;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Setting\Device\Device;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\System\Extension\Extension;

/**
 * Class ApiDevice
 *
 * @package SPHERE\Application\Api\Setting\Device
 */
class ApiDevice extends Extension implements IApiInterface
{
    use ApiTrait;

    /**
     * @param string $Method Callable Method
     *
     * @return string
     */
    public function exportApi($Method = '')
    {
        $Dispatcher = new Dispatcher(__CLASS__);

        $Dispatcher->registerMethod('getDeviceView');
        $Dispatcher->registerMethod('getDeviceModal');
        $Dispatcher->registerMethod('saveDeviceStatus');
        $Dispatcher->registerMethod('saveDeviceModal');

        return $Dispatcher->callMethod($Method);
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverDevice($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('DeviceReceiver');
    }

    /**
     * @param string $Content
     *
     * @return BlockReceiver
     */
    public static function receiverService($Content = '')
    {
        return (new BlockReceiver($Content))->setIdentifier('ServiceReceiver');
    }

    /**
     * @param string $Content
     *
     * @return ModalReceiver
     */
    public static function receiverDeviceModal()
    {
        return (new ModalReceiver('Gerät', new Close()))->setIdentifier('DeviceModalReceiver');
    }

    /**
     * @param null $Id
     * @param null $YearId
     * @param null $CompanyId
     *
     * @return Pipeline
     */
    public static function pipelineShowDevice()
    {
        $Pipeline = new Pipeline();

        // show/refresh Table
        $Emitter = new ServerEmitter(self::receiverDevice(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'getDeviceView',
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $deviceId
     * @param string $isActive
     * 
     * @return Pipeline
     */
    public static function pipelineChangeDevice(string $deviceId, string $isActive = '2')
    {
        $Pipeline = new Pipeline();

        // save active status
//        $Emitter = new ServerEmitter(self::receiverService(), self::getEndpoint());
        $Emitter = new ServerEmitter(self::receiverDevice(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'saveDeviceStatus',
            'deviceId' => $deviceId,
            'isActive' => $isActive,
        ));
        $Pipeline->appendEmitter($Emitter);
//        // show/refresh Table
//        $Emitter = new ServerEmitter(self::receiverDevice(), self::getEndpoint());
//        $Emitter->setPostPayload(array(
//            self::API_TARGET => 'getDeviceView',
//        ));
//        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $deviceId
     *
     * @return Pipeline
     */
    public static function pipelineShowModalDevice(string $deviceId): Pipeline
    {
        $Pipeline = new Pipeline();

        // show/refresh Table
        $Emitter = new ServerEmitter(self::receiverDeviceModal(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'getDeviceModal',
            'deviceId' => $deviceId,
        ));
        $Pipeline->appendEmitter($Emitter);

        return $Pipeline;
    }

    /**
     * @param string $deviceId
     *
     * @return Pipeline
     */
    public static function pipelineSaveModalDevice(string $deviceId): Pipeline
    {
        $Pipeline = new Pipeline();

        // show/refresh Table
        $Emitter = new ServerEmitter(self::receiverDeviceModal(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'saveDeviceModal',
            'deviceId' => $deviceId,
        ));
        $Pipeline->appendEmitter($Emitter);
        // show/refresh Table
        $Emitter = new ServerEmitter(self::receiverDevice(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'getDeviceView',
        ));
        $Pipeline->appendEmitter($Emitter);

        $Pipeline->appendEmitter((new CloseModal(self::receiverDeviceModal()))->getEmitter());

        return $Pipeline;
    }

    /**
     * @return Layout
     */
    public static function getDeviceView()
    {

        return Device::useFrontend()->getDevicePanelLayout();
    }

    /**
     * @param string $deviceId
     * @param string $isActive
     * @return Layout
     */
    public static function saveDeviceStatus(string $deviceId, string $isActive)
    {

        $tblDevice = Device::useService()->getDeviceById($deviceId);
        if($isActive == 3){
            Device::useService()->destroyDevice($tblDevice);
        } else {
            Device::useService()->updateDeviceStatus($tblDevice, $isActive);
        }
        return self::getDeviceView();
    }

    /**
     * @param $deviceId
     *
     * @return string
     */
    public function getDeviceModal($deviceId): string
    {
        $tblDevice = Device::useService()->getDeviceById($deviceId);
        if(!$tblDevice){
            return new Danger('Gerät nicht mehr vorhanden');
        }
        return Device::useService()->getDeviceForm($tblDevice);
    }

    /**
     * @param string $deviceId
     * @param array $Device
     *
     * @return string
     */
    public function saveDeviceModal(string $deviceId, array $Device = array())
    {

        $tblDevice = Device::useService()->getDeviceById($deviceId);
        $DeviceName = $Device['Name'];
        $DeviceStatus = '';
        if(isset($Device['Status'])){
            $DeviceStatus = $Device['Status'];
        }


        $tblDevice = Device::useService()->updateDevice($tblDevice, $DeviceName, $DeviceStatus);

        return ($tblDevice
            ? new Success('Änderung gespeichert')
            : new Danger('Änderung konnte nicht gespeichert werden')
        );
    }
}