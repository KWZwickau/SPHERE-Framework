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
}