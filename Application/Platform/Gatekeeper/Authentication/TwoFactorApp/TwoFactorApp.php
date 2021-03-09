<?php

namespace SPHERE\Application\Platform\Gatekeeper\Authentication\TwoFactorApp;

require_once( __DIR__.'/../../../../../Library/TwoFactorAuth/demo/loader.php' );
\Loader::register('../lib','RobThree\\Auth');

use RobThree\Auth\TwoFactorAuth;

class TwoFactorApp
{
    private $tfa;

    /**
     * TwoFactorAuth constructor.
     */
    public function __construct()
    {
        $this->tfa = new TwoFactorAuth('Schulsoftware');
    }

    public function createSecret()
    {
        // Though the default is an 80 bits secret (for backwards compatibility reasons) we recommend creating 160+ bits secrets (see RFC 4226 - Algorithm Requirements)
        return $this->tfa->createSecret(160);
    }

    public function getCode($secret)
    {
        return $this->tfa->getCode($secret);
    }

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

    public function getQRCodeImageAsDataUri($secret, $size = 200)
    {
        return $this->tfa->getQRCodeImageAsDataUri('Schulsoftware', $secret, $size);
    }
}