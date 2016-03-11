<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\Repository\Backward;
use SPHERE\Common\Frontend\Link\Repository\Standard;
use SPHERE\Common\Window\Stage;
use SPHERE\System\Extension\Extension;

/**
 * Class Frontend
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
 */
class Frontend extends Extension implements IFrontendInterface
{

    /**
     * @return Stage
     */
    public function frontendInvoiceList()
    {

        $Stage = new Stage();
        $Stage->setTitle('Rechnungen');
        $Stage->setDescription('Übersicht');
        $Stage->setMessage('Zeigt alle vorhandenen Rechnungen an');
        $Stage->addButton(new Standard('Aufträge', '/Billing/Bookkeeping/Invoice/Control', new Ok(), null, 'Freigeben von Rechnungen'));
        new Backward();

        return $Stage;
    }
}
