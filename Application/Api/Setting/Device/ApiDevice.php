<?php
namespace SPHERE\Application\Api\Setting\Device;

require_once(__DIR__.'/../../../../Library/QrCode/endroid/vendor/autoload.php');
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Setting\Device\Device;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\ModalReceiver;
use SPHERE\Common\Frontend\Ajax\Template\CloseModal;
use SPHERE\Common\Frontend\Form\Repository\Button\Close;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\System\Extension\Extension;
use SPHERE\System\Token\Jwt\TokenGenerator;

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
        $Dispatcher->registerMethod('getQrModal');

        return $Dispatcher->callMethod($Method);
    }

    public static function receiverDevice(string $Content = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier('DeviceReceiver');
    }

    public static function receiverService(string $Content = ''): BlockReceiver
    {
        return (new BlockReceiver($Content))->setIdentifier('ServiceReceiver');
    }

    public static function receiverDeviceModal(): ModalReceiver
    {
        return (new ModalReceiver('Gerät', new Close()))->setIdentifier('DeviceModalReceiver');
    }

    public static function receiverQrCodeModal(): ModalReceiver
    {
        return (new ModalReceiver('Geräte Login über QR-Code',
            (new Primary('Geräte-Seite aktualisieren', '#', new Repeat()))->ajaxPipelineOnClick(self::pipelineShowDevice())
            .new Close()))->setIdentifier('DeviceQrCodeReceiver');
    }

    public static function pipelineShowDevice(): Pipeline
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

    public static function pipelineReloadDevice(): Pipeline
    {
        $Pipeline = new Pipeline();

        // refresh device
        $Emitter = new ServerEmitter(self::receiverDevice(), self::getEndpoint());
        $Emitter->setPostPayload(array(self::API_TARGET => 'getDeviceView',));
        $Pipeline->appendEmitter($Emitter);
        // close modal
        $Pipeline->appendEmitter((new CloseModal(self::receiverQrCodeModal()))->getEmitter());

        return $Pipeline;
    }

    public static function pipelineChangeDevice(string $deviceId, string $isActive = '2'): Pipeline
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

        return $Pipeline;
    }

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

    public static function pipelineQrModal(): Pipeline
    {
        $Pipeline = new Pipeline();

        // show/refresh Table
        $Emitter = new ServerEmitter(self::receiverQrCodeModal(), self::getEndpoint());
        $Emitter->setPostPayload(array(
            self::API_TARGET => 'getQrModal',
        ));
        $Pipeline->appendEmitter($Emitter);
//        // show/refresh Table
//        $Emitter = new ServerEmitter(self::receiverDevice(), self::getEndpoint());
//        $Emitter->setPostPayload(array(
//            self::API_TARGET => 'getDeviceView',
//        ));
//        $Pipeline->appendEmitter($Emitter);
//
//        $Pipeline->appendEmitter((new CloseModal(self::receiverDeviceModal()))->getEmitter());

        return $Pipeline;
    }

    public static function getDeviceView(): Layout
    {

        return Device::useFrontend()->getDevicePanelLayout();
    }

    public static function saveDeviceStatus(string $deviceId, string $isActive): Layout
    {

        $tblDevice = Device::useService()->getDeviceById($deviceId);
        if($isActive == 3){
            Device::useService()->destroyDevice($tblDevice);
        } else {
            Device::useService()->updateDeviceStatus($tblDevice, $isActive);
        }
        return self::getDeviceView();
    }

    public function getDeviceModal(string $deviceId): string
    {
        $tblDevice = Device::useService()->getDeviceById($deviceId);
        if(!$tblDevice){
            return new Danger('Gerät nicht mehr vorhanden');
        }
        return Device::useService()->getDeviceForm($tblDevice);
    }

    public function saveDeviceModal(string $deviceId, array $Device = array()): String
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

    public function getQrModal(): string
    {
        $timeToActivate = 300; // second

        $qrCodeString = '';
        $tblAccount = Account::useService()->getAccountBySession();
        if($tblAccount){
            // QR-Login
            $qrCodeString = TokenGenerator::createToken(
                self::getRequest()->getHost(), $timeToActivate, [
                    'credentialIdentifier' => $tblAccount->getUsername(),
                    'credentialPassword' => $tblAccount->getPassword(),
                ]
            );
        } else {
            return new Danger('Fehler bei dem Aufrufen Ihrer Accountinformationen');
        }

        $info = new Container('- Nicht mehr genutzte Geräte entfernen')
            .new Container('- Gerät sperren, um zukünftige Login zu unterbinden');
        if(Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_TOKEN)
         || Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_AUTHENTICATOR_APP)){
            $info .= new Container('- Nach dem Scannen bitte die '.new Bold('Seite aktualisieren').' und das gewünschte Gerät unter „Meine Geräte" '
                .new Bold('aktivieren'));
            $info .= new Container(' '.new Bold(new ChevronRight().' Scannen Sie den QR-Code anschließend erneut für einen erlaubten Login.'));
        }

        // use without builder:
        $writer = new PngWriter();
        $size = 500; // a lot of data -> recommended size
        // Create QR code
        $qrCode = QrCode::create($qrCodeString)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize($size)
            ->setMargin(0)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
        $result = $writer->write($qrCode);

        $qrCode = '<img src="data:image/png;base64, ' . base64_encode($result->getString()) . '" />';

        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(new Center('<h2> QR-Code läuft in 5 Minuten ab </h2>'))
            )),
            new LayoutRow(array(
                new LayoutColumn(new Center($qrCode))
            )),
            new LayoutRow(array(
                new LayoutColumn(new Center(new Info($info)))
            )),
        )));
    }
}