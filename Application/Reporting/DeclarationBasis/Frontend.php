<?php

namespace SPHERE\Application\Reporting\DeclarationBasis;

use SPHERE\Common\Frontend\Icon\Repository\Download;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Primary;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Reporting\DeclarationBasis
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendDeclarationBasis()
    {
        $Stage = new Stage('Stichtagsmeldung', '');
        $Stage->addButton(
            new Primary('Herunterladen',
                '/Api/Reporting/DeclarationBasis/Download', new Download())
        );
        return $Stage;
    }
}