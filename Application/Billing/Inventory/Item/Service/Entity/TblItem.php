<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItem")
 * @Cache(usage="READ_ONLY")
 */
class TblItem extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="text")
     */
    protected $Description;
    /**
     * @Column(type="bigint")
     */
    protected $tblItemType;
    /**
     * @Column(type="string")
     */
    protected $SepaRemark;
    /**
     * @Column(type="string")
     */
    protected $DatevRemark;

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

    /**
     * @return bool|TblItem
     */
    public function getTblItemType()
    {

        if(null === $this->tblItemType){
            return false;
        } else {
            return Item::useService()->getItemTypeById($this->tblItemType);
        }
    }

    /**
     * @param TblItemType $tblItemType
     */
    public function setTblItemType(TblItemType $tblItemType)
    {

        $this->tblItemType = (null === $tblItemType ? null : $tblItemType->getId());
    }

    /**
     * @return string
     */
    public function getDisplayDescription()
    {
        return nl2br($this->getDescription());
    }

    /**
     * @return string
     */
    public function getSepaRemark()
    {

        if($this->SepaRemark){
            return $this->SepaRemark;
        }
        return $this->getName();
    }

    /**
     * @param string $SepaRemark
     */
    public function setSepaRemark($SepaRemark = '')
    {
        $this->SepaRemark = $SepaRemark;
    }

    /**
     * @return string
     */
    public function getDatevRemark()
    {

        if($this->DatevRemark){
            return $this->DatevRemark;
        }
        return $this->getName();
    }

    /**
     * @param string $DatevRemark
     */
    public function setDatevRemark($DatevRemark = '')
    {
        $this->DatevRemark = $DatevRemark;
    }
}
