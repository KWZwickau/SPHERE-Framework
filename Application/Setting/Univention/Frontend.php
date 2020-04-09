<?php
namespace SPHERE\Application\Setting\Univention;

use SPHERE\Common\Frontend\Icon\Repository\Upload;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 *
 * @package SPHERE\Application\Setting\MyAccount
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendUnivention()
    {

        $Stage = new Stage('Univention', 'Verbindung');
        $Stage->addButton(new Standard('Zurück', '/Setting', new Upload()));
        $Stage->addButton(new Standard('Accounts übertragen', '', new Upload()));

        return $Stage;
    }
}