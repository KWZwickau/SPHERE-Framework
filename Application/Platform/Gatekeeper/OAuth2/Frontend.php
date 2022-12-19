<?php
namespace SPHERE\Application\Platform\Gatekeeper\OAuth2;
use SPHERE\System\Extension\Repository\phpOAuth2;

/**
 * Class Frontend
 * @package SPHERE\Application\Platform\Gatekeeper\OAuth2
 */
class Frontend
{

    public function frontendOAuthTestSite()
    {

        return (new phpOAuth2())->getLoginProcess();
    }

    public function frontendOAuthTestRequest()
    {


        print_r($_REQUEST);
        print_r($_POST);
        print_r($_GET);
        return 'Test'
            .'<script src="https://repo.vidis.schule/repository/vidis-cdn/latest/vidisLogin.umd.js"></script>'
            .'<vidis-login size = "L" cookie = "false" loginurl="http://192.168.150.128/Platform/Gatekeeper/OAuth2/OAuthTestSite"></vidis-login>';
    }

    public function frontendVidis()
    {

        print_r($_REQUEST);
        print_r($_POST);
        print_r($_GET);
        return 'Test';
    }
}