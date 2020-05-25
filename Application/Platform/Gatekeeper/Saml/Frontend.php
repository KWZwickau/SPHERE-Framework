<?php
namespace SPHERE\Application\Platform\Gatekeeper\Saml;

use SPHERE\Common\Frontend\Link\Repository\External;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Repository\phpSaml;

/**
 * Class Frontend
 * @package SPHERE\Application\Platform\Gatekeeper\Saml
 */
class Frontend
{

    public function frontendSaml()
    {

        $Stage = new Stage('Saml 2');

        $Stage->addButton(new External('MetaData', 'SPHERE\Application\Platform\Gatekeeper\Saml\MetaData'));
        $Stage->addButton(new External('Saml 2 Login', 'SPHERE\Application\Platform\Gatekeeper\Saml\Login'));

        return $Stage;

    }

    /**
     * @throws \OneLogin_Saml2_Error
     */
    public function XMLMetaData()
    {

        $PhpSaml = new phpSaml();
        echo $PhpSaml->getMetaData();
        exit;
    }

    public function frontendLogin()
    {

        $PhpSaml = new phpSaml();
        return $PhpSaml->samlLogin();
    }
}