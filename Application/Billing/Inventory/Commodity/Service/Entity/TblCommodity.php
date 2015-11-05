<?php
namespace SPHERE\Application\Billing\Inventory\Commodity\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Commodity\Commodity;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblCommodity")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblCommodity extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="bigint")
     */
    protected $tblCommodityType;
    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @return bool|TblCommodityType
     */
    public function getTblCommodityType()
    {

        if (null === $this->tblCommodityType) {
            return false;
        } else {
            return Commodity::useService()->getCommodityTypeById($this->tblCommodityType);
        }
    }

    /**
     * @param null|TblCommodityType $tblCommodityType
     */
    public function setTblCommodityType(TblCommodityType $tblCommodityType = null)
    {

        $this->tblCommodityType = ( null === $tblCommodityType ? null : $tblCommodityType->getId() );
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }
}
