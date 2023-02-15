<?php
namespace SPHERE\System\Extension\Repository;

use SPHERE\Application\Platform\Gatekeeper\Authentication\Authentication;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Common\Frontend\Icon\Repository\Nameplate;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Bold;
use SPHERE\Common\Window\Redirect;
use SPHERE\Common\Window\Stage;

class phpOAuth2
{

//    private $config = array();

    /**
     * PdfMerge constructor.
     */
    public function __construct()
    {

        require_once(__DIR__
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . '..'
            . DIRECTORY_SEPARATOR . 'Library'
            . DIRECTORY_SEPARATOR . 'OAuth2-openId-connect'
            . DIRECTORY_SEPARATOR . 'vendor'
            . DIRECTORY_SEPARATOR . 'autoload.php');
    }

    /**
     * @return string|void
     */
    public function getLoginProcess()
    {

        $signer   = new \Lcobucci\JWT\Signer\Rsa\Sha256();
        $provider = new \OpenIDConnectClient\OpenIDConnectProvider([
            'clientId'                => 'edsi-o',
            'clientSecret'            => 'HkpLEqKdrAROaXQ49AeSKB6wa96FXlqP',
            // the issuer of the identity token (id_token) this will be compared with what is returned in the token.
//            'idTokenIssuer'           => 'Schulsoftware',
            'idTokenIssuer'           => 'https://aai-test.vidis.schule/auth/realms/vidis',
            // Your server
            'redirectUri'             => 'https://demo.schulsoftware.schule/Platform/Gatekeeper/OAuth2/Vidis',
            'urlAuthorize'            => 'https://aai-test.vidis.schule/auth/realms/vidis/protocol/openid-connect/auth',
            'urlAccessToken'          => 'https://aai-test.vidis.schule/auth/realms/vidis/protocol/openid-connect/token',
            'urlResourceOwnerDetails' => 'https://aai-test.vidis.schule/auth/realms/vidis/.well-known/openid-configuration',
            // Find the public key here: https://github.com/bshaffer/oauth2-demo-php/blob/master/data/pubkey.pem
            // to test against brentertainment.com
//            'publicKey'                 => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0R+ToA+jxpgyjygqcUf0PFRggMYoKEYsxkk1Bv54oVBUpDaY/2HNgk1MGXFPcA736reP3eAfLVDoq9Ni1b7NsjcXMUIsCkeJ/Oou37TXjZmj0aS2z/93Fd8OqX6FDfIyTgQRZvavSHdNs1YoElFpmwr/HD7WslmdNzYk/4Bw35FBK+vVNVXEkJawc0nr+sTied9uih7E8Pz9Fg2ApIQnBu3SgXNsXNnlVRHz2D/dfwT/9HiSLJNXMj72qZpvSZ8KG+QUlfbWyVj4GE35RpdNMmkDkAZpmhqXgrNfP8HsSUD5Jg8AenkroGfiBoSYwCvxhnSuJsEmQ9rakXnLDlQOlQIDAQAB',
            'publicKey'                 => '-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0R+ToA+jxpgyjygqcUf0
PFRggMYoKEYsxkk1Bv54oVBUpDaY/2HNgk1MGXFPcA736reP3eAfLVDoq9Ni1b7N
sjcXMUIsCkeJ/Oou37TXjZmj0aS2z/93Fd8OqX6FDfIyTgQRZvavSHdNs1YoElFp
mwr/HD7WslmdNzYk/4Bw35FBK+vVNVXEkJawc0nr+sTied9uih7E8Pz9Fg2ApIQn
Bu3SgXNsXNnlVRHz2D/dfwT/9HiSLJNXMj72qZpvSZ8KG+QUlfbWyVj4GE35RpdN
MmkDkAZpmhqXgrNfP8HsSUD5Jg8AenkroGfiBoSYwCvxhnSuJsEmQ9rakXnLDlQO
lQIDAQAB
-----END PUBLIC KEY-----',
        ],
            ['signer' => $signer]
        );

        // send the authorization request
        if (empty($_GET['code'])) {
            $redirectUrl = $provider->getAuthorizationUrl();
            header(sprintf('Location: %s', $redirectUrl), true, 302);
            return;
        }

        // receive authorization response
        try {
            $token = $provider->getAccessToken('authorization_code', [
                'code' => $_GET['code']
            ]);
        } catch (\OpenIDConnectClient\Exception\InvalidTokenException $e) {

            echo "<pre>";
            var_dump('Catch: '.$e->getMessage());
            echo "</pre>";
            $errors = $provider->getValidatorChain()->getMessages();
            return new Listing($errors);
        }
        $hasExpired     = $token->hasExpired();
        $idToken        = $token->getIdToken();

//        echo "<pre>";
//        print_r('idToken'.'<br/>');
//        print_r($idToken->claims()->get('preferred_username').'<br/>');
//        print_r(gmdate('Y.m.d H:i:s', $idToken->claims()->get('auth_time')));
//        echo "</pre>";

        $Stage = new Stage(new Nameplate().' Anmelden', '', 'demo.schulsoftware.schule');

        $AccountMessage = '';
        if($idToken && $hasExpired === false){
            $UserName = $idToken->claims()->get('preferred_username');
            $tblAccount = Account::useService()->getAccountByUsername($UserName);
            if($tblAccount) {
                if(($ExistSessionAccount = Account::useService()->getAccountBySession())){
                    // is requested account the same like session account go to welcome
                    if($tblAccount && $ExistSessionAccount->getId() == $tblAccount->getId()){
                        $Stage->setContent(new Redirect('/', Redirect::TIMEOUT_SUCCESS));
                        return $Stage;
                    }
                    // remove existing Session if User is not the same
                    if($Session = session_id()){
                        Account::useService()->destroySession(null, $Session);
                    }
                }

                $tblIdentification = null;
                if($tblAccount
                    && ($tblAuthentication = Account::useService()->getAuthenticationByAccount($tblAccount))){
                    $tblIdentification = $tblAuthentication->getTblIdentification();
                }

                // Matching Account found?
                if ($tblAccount && $tblIdentification) {
                    // Anfragen von SAML müssen Cookies aktiviert haben
                    $isCookieAvailable = true;
                    switch ($tblIdentification->getName()) {
                        case TblIdentification::NAME_AUTHENTICATOR_APP:
                        case TblIdentification::NAME_TOKEN:
                        case TblIdentification::NAME_SYSTEM:
                            return Authentication::useFrontend()->frontendIdentificationToken($tblAccount->getId(), $tblIdentification->getId(), null, $isCookieAvailable);
                        case TblIdentification::NAME_CREDENTIAL:
                        case TblIdentification::NAME_USER_CREDENTIAL:
                            return Authentication::useFrontend()->frontendIdentificationAgb($tblAccount->getId(), $tblIdentification->getId(), 0, $isCookieAvailable);
                    }
                }
            } else {
                $AccountMessage = new Bold('"No SSW User"');
            }
        }

        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(
            new LayoutColumn(new Warning('Ihr Login ist nicht möglich. '.$AccountMessage))
        ))));

        return $Stage;
    }
}