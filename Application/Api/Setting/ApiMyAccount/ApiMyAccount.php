<?php

namespace SPHERE\Application\Api\Setting\ApiMyAccount;

use SPHERE\Application\Api\ApiTrait;
use SPHERE\Application\Api\Dispatcher;
use SPHERE\Application\IApiInterface;
use SPHERE\Common\Frontend\Ajax\Emitter\ServerEmitter;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\Common\Frontend\Ajax\Receiver\BlockReceiver;
use SPHERE\Common\Frontend\Layout\Repository\Container;
use SPHERE\Common\Frontend\Layout\Repository\Panel;
use SPHERE\Common\Frontend\Layout\Repository\ProgressBar;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\System\Extension\Extension;

class ApiMyAccount extends Extension implements IApiInterface
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
        $Dispatcher->registerMethod('comparePassword');

        return $Dispatcher->callMethod($Method);
    }

    public static function receiverComparePassword()
    {

        return new BlockReceiver();
    }

    /**
     * @param AbstractReceiver $Receiver
     *
     * @return Pipeline
     */
    public static function pipelineComparePassword(AbstractReceiver $Receiver)
    {
        $ComparePasswordPipeline = new Pipeline(false);
        $ComparePasswordEmitter = new ServerEmitter($Receiver, ApiMyAccount::getEndpoint());
        $ComparePasswordEmitter->setGetPayload(array(
            ApiMyAccount::API_TARGET => 'comparePassword'
        ));
        $ComparePasswordPipeline->appendEmitter($ComparePasswordEmitter);

        return $ComparePasswordPipeline;
    }

    /**
     * @param string $CredentialLock
     * @param string $CredentialLockSafety
     *
     * @return Panel
     */
    public function comparePassword($CredentialLock = '', $CredentialLockSafety = '')
    {

        $Step = 0;
        if (preg_match('![a-z]!s', $CredentialLock)) {
            $Step++;
        }
        if (preg_match('![A-Z]!s', $CredentialLock)) {
            $Step++;
        }
        if (preg_match('![0-9]!s', $CredentialLock)) {
            $Step++;
        }
        if (preg_match('![^\w\d]!s', $CredentialLock)) {
            $Step++;
        }

//        $countCriteria = '';
//        if($Step >= 1){
//            $countCriteria = '('.new Warning($Step).')';
//            if($Step >= 3){
//                $countCriteria = '('.new Success($Step.' '.new SuccessIcon()).')';
//            }
//        }
//        $countLength = '';

        $D = 100 / 4 * ($Step);

        $W = 100 / 4 * (3 - $Step);
        $W = $W < 0 ? 0 : $W;

        $P = 100 / 4 * 1;
        $P = $Step == 4 ? 0 : $P;

        $L = strlen($CredentialLock);
//        if($L >= 1){
//            $countLength = '('.new Warning($L).')';
//            if($L >= 8){
//                $countLength = '('.new Success($L.' '.new SuccessIcon()).')';
//            }
//        }

        $DL = 100 / 8 * $L;
        $WL = 100 / 8 * (8 - $L);
        $DL = $L > 8 ? 100 : $DL;
        $WL = $WL < 0 ? 0 : $WL;

        //warning if both exist
        if ($CredentialLock != '' && $CredentialLockSafety != '') {
            $DC = $PC = 0;
            $WC = 100;
            $Compare = new ProgressBar($DC, $WC, $PC, 10);
        } else {
            $DC = $WC = 0;
            $PC = 100;
            $Compare = new ProgressBar($DC, $WC, $PC, 10);
        }
        // if identical set done
        if ($CredentialLock === $CredentialLockSafety && $CredentialLock != '' && $CredentialLockSafety != '') {
            $WC = $PC = 0;
            $DC = 100;
            $Compare = new ProgressBar($DC, $WC, $PC, 10);
        }

        return new Panel('Passwort Richtlinien',
            array(
                new Bold('Das Passwort muss mindestens 8 Zeichen lang sein '). // $countLength).
                new Container((new ProgressBar($DL, $WL, 0, 10))),
                new Bold('Das Passwort muss 3 von 4 Kriterien erfüllen '). // $countCriteria).
                new Container(new ProgressBar($D, $W, $P, 10)),
                '1. mindestens ein Kleinbuchstabe',
                '2. mindestens ein Großbuchstabe',
                '3. mindestens ein Sonderzeichen',
                '4. mindestens eine Zahl',
                new Bold('Passwort Übereinstimmung ').
                new Container($Compare)
            ),
            Panel::PANEL_TYPE_SUCCESS);
    }
}