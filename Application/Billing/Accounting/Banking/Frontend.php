<?php

namespace SPHERE\Application\Billing\Accounting\Banking;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Accounting\Banking
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendBanking()
    {

        $Stage = new Stage();
        $Stage->setTitle('Debitoren');
        $Stage->setDescription('Übersicht');

        return $Stage;
    }
}
