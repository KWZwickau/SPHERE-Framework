<?php

namespace SPHERE\Application\Platform\Gatekeeper\Authentication\TwoFactorApp;

require_once( __DIR__.'/../../../../../Library/TwoFactorAuth/demo/loader.php' );
\Loader::register('../lib','RobThree\\Auth');

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use RobThree\Auth\TwoFactorAuth;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;

require_once(__DIR__.DIRECTORY_SEPARATOR.'../../../../../Library/QrCode/endroid/vendor/autoload.php');
//require_once(__DIR__.DIRECTORY_SEPARATOR.'../../../../../Library/QrCode/2.0.8/vendor/autoload.php');
//require_once ( __DIR__.'/../../../../../Library/QrCode/autoload_function.php');
//require_once ( __DIR__.'/../../../../../Library/QrCode/autoload_register.php');

/**
 * Class TwoFactorApp
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authentication\TwoFactorApp
 */
class TwoFactorApp
{
    const LABEL = 'Schulsoftware';
    private $tfa;

    /**
     * TwoFactorAuth constructor.
     */
    public function __construct()
    {
        $this->tfa = new TwoFactorAuth(self::LABEL);
    }

    /**
     * @return string
     */
    public function createSecret()
    {
        // Though the default is an 80 bits secret (for backwards compatibility reasons) we recommend creating 160+ bits secrets (see RFC 4226 - Algorithm Requirements)
        return $this->tfa->createSecret(160);
    }

    /**
     * @param $secret
     *
     * @return string
     */
    public function getCode($secret)
    {
        return $this->tfa->getCode($secret);
    }

    /**
     * @param $secret
     * @param $code
     *
     * @return bool
     */
    public function verifyCode($secret, $code)
    {
        // Since TOTP codes are based on time("slices") it is very important that the server (but also client) have a correct date/time.
        // But because the two may differ a bit we usually allow a certain amount of leeway. Because generated codes are
        // valid for a specific period (remember the $period argument in the TwoFactorAuth's constructor?) we usually
        // check the period directly before and the period directly after the current time when validating codes.
        // So when the current time is 14:34:21, which results in a 'current timeslice' of 14:34:00 to 14:34:30 we also
        // calculate/verify the codes for 14:33:30 to 14:34:00 and for 14:34:30 to 14:35:00. This gives us a 'window'
        // of 14:33:30 to 14:35:00. The $discrepancy argument specifies how many periods (or: timeslices) we check
        // in either direction of the current time. The default $discrepancy of 1 results in (max.) 3 period checks: -1,
        // current and +1 period. A $discrepancy of 4 would result in a larger window (or: bigger time difference between
        // client and server) of -4, -3, -2, -1, current, +1, +2, +3 and +4 periods.
        $discrepancy = 1;

        return $this->tfa->verifyCode($secret, $code, $discrepancy);
    }

//    /**
//     * example '<img src="' . $twoFactorApp->getQRCodeImageAsDataUri($secret, 180) . '">'
//     *
//     * @param $secret
//     * @param int $size
//     *
//     * @return string
//     */
//    public function getQRCodeImageAsDataUri($secret, $size = 200)
//    {
//        return $this->tfa->getQRCodeImageAsDataUri(self::LABEL, $secret, $size);
//    }

    /**
     * @param TblAccount $tblAccount
     * @param string     $secret
     * @param int        $size sharp in 196, 147, 245
     *
     * @return string
     */
    public function getBaconQrCode(TblAccount $tblAccount, $secret, $size = 190)
    {

        $User = $tblAccount->getUsername();
//        // use beacon directly (contra sharpness)
//        // FarbenTest
////        $eye = SquareEye::instance();
////        $squareModule = SquareModule::instance();
////        $eyeFill = new EyeFill(new Rgb(71, 180, 154), new Rgb(53, 142, 124));
////        $gradient = new Gradient(new Rgb(90, 210, 190), new Rgb(53, 142, 124), GradientType::DIAGONAL());
//        $renderer = new ImageRenderer(
//            new RendererStyle($size
////                , 4, $squareModule, $eye, Fill::withForegroundGradient(new Rgb(255, 255, 255), $gradient, $eyeFill, $eyeFill, $eyeFill)
//            ),
//            new ImagickImageBackEnd('png', 100)
//        );
//        $writer = new Writer($renderer);
//        $qr_image = base64_encode($writer->writeString($this->tfa->getQRText('Schulsoftware:'.$User, $secret)));
//        return '<img src="data:image/png;base64, ' . $qr_image . '" />';

//        // use endroid with builder:
//        $result = Builder::create()
//            ->writer(new PngWriter())
//            ->writerOptions([])
//            ->data($this->tfa->getQRText('Schulsoftware:'.$User, $secret))
//            ->encoding(new Encoding('UTF-8'))
//            ->errorCorrectionLevel(ErrorCorrectionLevel::Low)
//            ->size($size)
//            ->margin(4)
//            ->roundBlockSizeMode(RoundBlockSizeMode::Margin)
////            ->logoPath(__DIR__.'/assets/symfony.png')
////            ->logoResizeToWidth(50)
////            ->logoPunchoutBackground(true)
//            ->labelText($size)
//            ->labelFont(new NotoSans(13))
//            ->labelAlignment(LabelAlignment::Center)
////            ->validateResult(false)
//            ->build();
//        return '<img src="data:image/png;base64, ' . base64_encode($result->getString()) . '" />';

        // use without builder:
        $writer = new PngWriter();
        // Create QR code
        $qrCode = QrCode::create($this->tfa->getQRText('Schulsoftware:'.$User, $secret))
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::Low)
            ->setSize($size)
            ->setMargin(0)
            ->setRoundBlockSizeMode(RoundBlockSizeMode::Margin)
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));
//        $Label = Label::create($size);
//        $result = $writer->write($qrCode, null, $Label);
        $result = $writer->write($qrCode);
        return '<img src="data:image/png;base64, ' . base64_encode($result->getString()) . '" />';
    }
}