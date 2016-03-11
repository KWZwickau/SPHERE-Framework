<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendBalance()
    {

        $Stage = new Stage();
        $Stage->setTitle('Posten');
        $Stage->setDescription('Offen');
        new Backward();

        return $Stage;
    }
}
