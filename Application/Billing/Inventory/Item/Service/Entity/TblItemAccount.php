<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Account\Account;
use SPHERE\Application\Billing\Accounting\Account\Service\Entity\TblAccount;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItemAccount")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblItemAccount extends Element
{

    const ATTR_TBL_ITEM = 'tblItem';
    const ATTR_SERVICE_BILLING_ACCOUNT = 'serviceBilling_Account';

    /**
     * @Column(type="bigint")
     */
    protected $tblItem;
    /**
     * @Column(type="bigint")
     */
    protected $serviceBilling_Account;

    /**
     * @return bool|TblItem
     */
    public function getTblItem()
    {

        if (null === $this->tblItem) {
            return false;
        } else {
            return Item::useService()->getItemById($this->tblItem);
        }
    }

    /**
     * @param null|TblItem $tblItem
     */
    public function setTblItem(TblItem $tblItem = null)
    {

        $this->tblItem = ( null === $tblItem ? null : $tblItem->getId() );
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceBilling_Account()
    {

        if (null === $this->serviceBilling_Account) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->serviceBilling_Account);
        }
    }

    /**
     * @param TblAccount $serviceBilling_Account
     */
    public function setTblAccount(TblAccount $serviceBilling_Account = null)
    {

        $this->serviceBilling_Account = ( null === $serviceBilling_Account ? null : $serviceBilling_Account->getId() );
    }
}
