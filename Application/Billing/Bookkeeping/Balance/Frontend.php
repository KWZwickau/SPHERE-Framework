<?php

namespace SPHERE\Application\Billing\Bookkeeping\Balance;

use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Balance
 */
class Frontend extends Extension implements IFrontendInterface
{

    public function frontendBalance()
    {

        $Stage = new Stage('Beleg-Druck');

        return $Stage;
    }
}
