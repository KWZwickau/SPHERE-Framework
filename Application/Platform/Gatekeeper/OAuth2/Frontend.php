<?php
namespace SPHERE\Application\Platform\Gatekeeper\OAuth2;
use SPHERE\System\Extension\Repository\phpOAuth2;

/**
 * Class Frontend
 * @package SPHERE\Application\Platform\Gatekeeper\OAuth2
 */
class Frontend
{

    public function frontendOAuthRequest()
    {

        return (new phpOAuth2())->getLoginProcess();
    }

    public function frontendVidis()
    {

        echo "<pre>";
        print_r($_GET);
        echo "</pre>";

        return
            // '<script src="https://repo.vidis.schule/repository/vidis-cdn/latest/vidisLogin.umd.js"></script>'.
            (new phpOAuth2())->getLoginProcess()
            .'Test';
    }
}