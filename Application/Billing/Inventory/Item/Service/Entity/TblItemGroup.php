<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\People\Group\Group;
use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItemGroup")
 * @Cache(usage="READ_ONLY")
 */
class TblItemGroup extends Element
{

    const ATTR_TBL_ITEM = 'tblItem';
    const ATTR_SERVICE_TBL_GROUP = 'serviceTblGroup';

    /**
     * @Column(type="bigint")
     */
    protected $tblItem;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGroup;

    /**
     * @return bool|TblItem
     */
    public function getTblItem()
    {

        if(null === $this->tblItem){
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

        $this->tblItem = (null === $tblItem ? null : $tblItem->getId());
    }

    /**
     * @return bool|TblGroup
     */
    public function getServiceTblGroup()
    {

        if(null === $this->serviceTblGroup){
            return false;
        } else {
            return Group::useService()->getGroupById($this->serviceTblGroup);
        }
    }

    /**
     * @param null|TblGroup $serviceTblGroup
     */
    public function setServiceTblGroup(TblGroup $serviceTblGroup)
    {

        $this->serviceTblGroup = (null === $serviceTblGroup ? null : $serviceTblGroup->getId());
    }

}
