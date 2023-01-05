<?php
namespace SPHERE\System\Extension\Repository;

use SPHERE\Common\Frontend\Layout\Repository\Listing;

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
            'idTokenIssuer'           => 'Test Landesportal',
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
            $errors = $provider->getValidatorChain()->getMessages();
            return new Listing($errors);
        }
        $accessToken    = $token->getToken();
        $refreshToken   = $token->getRefreshToken();
        $expires        = $token->getExpires();
        $hasExpired     = $token->hasExpired();
        $idToken        = $token->getIdToken();

        echo "<pre>";
        print_r($token);
        print_r($accessToken);
        print_r($refreshToken);
        print_r($expires);
        print_r($hasExpired);
        print_r($idToken);
        echo "</pre>";

        return 'kommt bis zum Ende';
    }
}