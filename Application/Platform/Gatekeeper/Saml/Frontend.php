<?php
namespace SPHERE\Application\Platform\Gatekeeper\Saml;

use SPHERE\Application\Platform\Gatekeeper\Authentication\Saml\SamlDLLP;
use SPHERE\Application\Platform\Gatekeeper\Authentication\Saml\SamlDLLPDemo;
use SPHERE\Application\Platform\Gatekeeper\Authentication\Saml\SamlPlaceholder;
use SPHERE\System\Extension\Repository\phpSaml;

/**
 * Class Frontend
 * @package SPHERE\Application\Platform\Gatekeeper\Saml
 */
class Frontend
{

    public function XMLMetaDataPlaceholder()
    {

        // no config needed
        $PhpSaml = new phpSaml(SamlPlaceholder::getSAML());
        echo $PhpSaml->getMetaData();
        exit;
    }

    public function XMLMetaDataDLLP()
    {

        // no config needed
        $PhpSaml = new phpSaml(SamlDLLP::getSAML());
        echo $PhpSaml->getMetaData();
        exit;
    }

    public function XMLMetaDataDLLPDemo()
    {

        // no config needed
        $PhpSaml = new phpSaml(SamlDLLPDemo::getSAML());
        echo $PhpSaml->getMetaData();
        exit;
    }

    public function frontendLoginPlaceholder()
    {

        $PhpSaml = new phpSaml(SamlPlaceholder::getSAML());
        $PhpSaml->samlLogin();
    }

    public function frontendLoginDLLP()
    {

        $PhpSaml = new phpSaml(SamlDLLP::getSAML());
        $PhpSaml->samlLogin();
    }

    public function frontendLoginDLLPDemo()
    {

        $PhpSaml = new phpSaml(SamlDLLPDemo::getSAML());
        $PhpSaml->samlLogin();
    }

//    /**
//     * @return null
//     * @throws \OneLogin_Saml2_Error
//     */
//    public function frontendLogoutDLLPDemo()
//    {
//
//        $Stage = new Stage('Logout');
//        Account::useService()->destroySession();
//        $Auth = new phpSaml(SamlDLLPDemo::getSAML());
//        // aktives Logout erzeugt ein permanentes Redirect mit DLLP
////        $Auth->samlLogout();
//        $Stage->setContent(new Layout(new LayoutGroup(new LayoutRow(array(
//            new LayoutColumn('', 4),
//            new LayoutColumn(new Info(new Center('Erfolgreich ausgeloggt')), 4),
//            new LayoutColumn('', 4)
//        )))));
//        return $Stage;
//    }
}