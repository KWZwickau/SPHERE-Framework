<?php

namespace SPHERE\Application\RestApi\Public\Authorization;

use SPHERE\Application\Platform\Gatekeeper\Authentication\TwoFactorApp\TwoFactorApp;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Application\RestApi\IApiInterface;
use SPHERE\Common\Main;
use SPHERE\System\Extension\Extension;

class ApiAuthorization implements IApiInterface
{
    public static function registerApi(): void
    {
        Main::getRestApiDispatcher()->registerRoute(Main::getRestApiDispatcher()->createRoute(
           __NAMESPACE__ . '/Login' , __CLASS__  . '::getLogin',
        ));
    }

    public static function getLogin($Username = null, $Password = null, $CredentialKey = null): array
    {
        if (($tblAccount = Account::useService()->getAccountByCredential($Username, $Password))) {
            if ($tblAccount->getHasAuthentication(TblIdentification::NAME_SYSTEM)
                || $tblAccount->getHasAuthentication(TblIdentification::NAME_TOKEN)
                || $tblAccount->getHasAuthentication(TblIdentification::NAME_AUTHENTICATOR_APP)
            ) {
                if ($CredentialKey) {
                    if ($tblAccount->getHasAuthentication(TblIdentification::NAME_AUTHENTICATOR_APP) && strlen($CredentialKey) == 6) {
                        $twoFactorApp = new TwoFactorApp();
                        if ($twoFactorApp->verifyCode($tblAccount->getAuthenticatorAppSecret(), $CredentialKey)) {

                            return array('success' => true, "message" => "Access granted.", "data" => array("accountId" => $tblAccount->getId()));
                        } else {

                            return array("success" => false, "message" => "Access denied.");
                        }
                    } else {
                        // Search for matching Token
                        $Identifier = (new Extension())->getModHex($CredentialKey)->getIdentifier();
                        if (($tblToken = Token::useService()->getTokenByIdentifier($Identifier))
                            && $tblAccount->getServiceTblToken()
                            && $tblAccount->getServiceTblToken()->getId() == $tblToken->getId()
                            && Token::useService()->isTokenValid($CredentialKey)
                        ) {

                            return array("success" => true, "message" => "Access granted.", "data" => array("accountId" => $tblAccount->getId()));
                        } else {

                            return array("success" => false, "message" => "Access denied.");
                        }
                    }
                } else {

                    return array("success" => true, "message" => "2FA required.");
                }
            } else {

                return array("success" => true, "message" => "Access granted.", "data" => array("accountId" => $tblAccount->getId()));
            }
        }

        return array("success" => false, "message" => "Access denied.");
    }
}