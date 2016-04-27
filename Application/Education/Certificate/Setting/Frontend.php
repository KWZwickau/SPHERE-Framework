<?php
namespace SPHERE\Application\Education\Certificate\Setting;

use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

class Frontend extends Extension implements IFrontendInterface
{

    public function frontendCertificateSetting($Certificate)
    {

        $tblCertificate = Generator::useService()->getCertificateById($Certificate);

        $Stage = new Stage('Einstellungen', $tblCertificate->getName().' '.$tblCertificate->getDescription());
        
        return $Stage;
    }
}
