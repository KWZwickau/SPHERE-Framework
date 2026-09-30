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
use SPHERE\Application\App\Authentication\Authentication;
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
use SPHERE\Common\Frontend\Icon\Repository\Repeat;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Link\Repository\CopyButton;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Frontend\Message\Repository\Danger;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Frontend\Text\Repository\Center;
use SPHERE\Common\Frontend\Text\Repository\Italic;
use SPHERE\Common\Frontend\Text\Repository\Small;
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
        return (new ModalReceiver('', new Close()))->setIdentifier('DeviceModalReceiver');
    }

    public static function receiverQrCodeModal(): ModalReceiver
    {
        return (new ModalReceiver('Geräte Login über QR-Code',
            (new Primary('Geräte aktualisieren & Schließen', '#', new Repeat()))->ajaxPipelineOnClick(self::pipelineReloadDevice())
        ))->setIdentifier('QRModal');
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
        // close modal (Identifier statt Receiver-Objekt, sonst Endlosrekursion über receiverQrCodeModal())
        $Pipeline->appendEmitter((new CloseModal('QRModal'))->getEmitter());

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
//        $DeviceStatus = '';
//        if(isset($Device['Status'])){
//            $DeviceStatus = $Device['Status'];
//        }


        $tblDevice = Device::useService()->updateDevice($tblDevice, $DeviceName);

        return ($tblDevice
            ? new Success('Änderung gespeichert')
            : new Danger('Änderung konnte nicht gespeichert werden')
        );
    }

    public function getQrModal(): string
    {
        $timeToActivate = 60; // second

        $qrCodeString = '';
        $tblAccount = Account::useService()->getAccountBySession();
        if($tblAccount){
            // QR-Login
            $qrCodeString = TokenGenerator::createToken(
                self::getRequest()->getHost(), $timeToActivate, [
//                    'credentialIdentifier' => $tblAccount->getUsername(),
//                    'credentialPassword' => $tblAccount->getPassword(),
                    'credentialHash' => hash('sha512',uniqid('app',true))
                ]
            );
            Authentication::useService()->createLoginToken($tblAccount, $qrCodeString);
        } else {
            return new Danger('Fehler bei dem Aufrufen Ihrer Accountinformationen');
        }

        $LayoutColumnInfo = '';
        if(Account::useService()->getHasAuthenticationByAccountAndIdentificationName($tblAccount, TblIdentification::NAME_SYSTEM)){
            $LayoutColumnInfo = new LayoutColumn(new Center(
                new Container(new Small(new Italic('Kopieren nur für System Admin')).new Container(new CopyButton($qrCodeString)))
            ));
        }

        // use without builder:
        $writer = new PngWriter();
        $size = 350; // a lot of data -> recommended size 500px
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

        $countdownId = 'QrCountdown'.uniqid();
        $timeToActivate--; // neugenerierung 1.sec früher als abgelaufen
        $countdown = '<h2 id="'.$countdownId.'">QR-Code läuft in <span>'.$timeToActivate.'</span> Sekunden ab</h2>'
            .self::getCountdownScript($countdownId, $timeToActivate);

        return new Layout(new LayoutGroup(array(
            new LayoutRow(array(
                new LayoutColumn(new Center($countdown)),
                new LayoutColumn('<div style="height: 20px"></div>')
            )),
            new LayoutRow(array(
                new LayoutColumn(new Center($qrCode))
            )),
            new LayoutRow(array(
                new LayoutColumn('<div style="height: 40px"></div>'),
                $LayoutColumnInfo
            )),
        )));
    }

    /**
     * Countdown for element $countdownId (expects a <span> for the seconds)
     * stops itself when the element is gone or hidden (modal closed / reloaded)
     * on expiry the modal content is reloaded with a new QR code
     */
    private static function getCountdownScript(string $countdownId, int $seconds): string
    {
        $reloadScript = self::pipelineQrModal()->parseScript();

        return <<<JS
            <script>(function(){
                var s = {$seconds},
                    h = document.getElementById("{$countdownId}"),
                    i = setInterval(function(){
                        if (!document.body.contains(h) || !jQuery(h).is(":visible")) { clearInterval(i); return; }
                        if (--s <= 0) {
                            clearInterval(i);
                            h.textContent = "QR-Code wird erneuert ...";
                            Client.Use("ModAjax", function(){ {$reloadScript} });
                            return;
                        }
                        h.querySelector("span").textContent = s;
                    }, 1000);
            })();</script>
        JS;
    }
}