<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use SPHERE\Application\Billing\Accounting\Account\Account;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Accounting\Basket\Basket;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoiceAccount")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblInvoiceAccount extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $tblInvoiceItem;

    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Account;

    /**
     * @return bool|TblInvoiceItem
     */
    public function getTblInvoiceItem()
    {

        if ( null === $this->tblInvoiceItem ) {
            return false;
        } else {
            return Basket::useService()->entityBasketItemById( $this->tblInvoiceItem );
        }
    }

    /**
     * @param null|TblInvoiceItem $tblInvoiceItem
     */
    public function setTblInvoiceItem( TblInvoiceItem $tblInvoiceItem = null )
    {

        $this->tblInvoiceItem = ( null === $tblInvoiceItem ? null : $tblInvoiceItem->getId() );
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceBilling_Account()
    {

        if ( null === $this->serviceBilling_Account ) {
            return false;
        } else {
            return Account::useService()->entityAccountById( $this->serviceBilling_Account );
        }
    }

    /**
     * @param TblAccount $tblAccount
     */
    public function setServiceBilling_Account( TblAccount $tblAccount = null )
    {

        $this->serviceBilling_Account = ( null === $tblAccount ? null : $tblAccount->getId() );
    }
}
