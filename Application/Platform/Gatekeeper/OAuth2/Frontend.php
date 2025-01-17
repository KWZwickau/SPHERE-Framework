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

        return (new phpOAuth2())->getLoginProcess();
    }
}