<?php

namespace SPHERE\Application\Billing\Bookkeeping\Invoice;

use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Data;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblDebtor;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoice;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblInvoiceItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity\TblItem;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Setup;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 * @package SPHERE\Application\Billing\Bookkeeping\Invoice
 */
class Service extends AbstractService
{

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }

        return $Protocol;
    }

    /**
     * @return bool|TblInvoice[]
     */
    public function getInvoiceAll()
    {

        return (new Data($this->getBinding()))->getInvoiceAll();
    }

    /**
     * @param $Id
     *
     * @return bool|TblInvoice
     */
    public function getInvoiceById($Id)
    {

        return (new Data($this->getBinding()))->getInvoiceById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblItem
     */
    public function getItemById($Id)
    {

        return (new Data($this->getBinding()))->getItemById($Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblDebtor
     */
    public function getDebtorById($Id)
    {

        return (new Data($this->getBinding()))->getDebtorById($Id);
    }

    /**
     * @param TblInvoice $tblInvoice
     *
     * @return bool|TblInvoiceItem[]
     */
    public function getInvoiceItemAllByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getInvoiceItemAllByInvoice($tblInvoice);
    }

    public function getItemByInvoice(TblInvoice $tblInvoice)
    {

        return (new Data($this->getBinding()))->getItemByInvoice($tblInvoice);
    }
}
