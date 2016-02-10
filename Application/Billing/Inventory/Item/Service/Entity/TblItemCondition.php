<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItemCondition")
 * @Cache(usage="READ_ONLY")
 */
class TblItemCondition extends Element
{

    const ATTR_TBL_ITEM = 'tblItem';
    const SERVICE_SCHOOL_TYPE = 'serviceSchoolTblType';
    const SERVICE_SIBLING_RANK = 'serviceStudentSiblingRank';

    /**
     * @Column(type="bigint")
     */
    protected $tblItem;
    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Value;
    /**
     * @Column(type="bigint")
     */
    protected $serviceSchoolTblType;
    /**
     * @Column(type="bigint")
     */
    protected $serviceStudentSiblingRank;

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
     * @param TblItem $tblItem
     */
    public function setTblItem(TblItem $tblItem)
    {

        $this->tblItem = $tblItem->getId();
    }

    /**
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return string
     */
    public function getPriceString()
    {

        $result = sprintf("%01.4f", $this->Value);
        return str_replace('.', ',', $result)." €";
    }

    /**
     * @return bool|TblType
     */
    public function getServiceSchoolType()
    {

        if (null === $this->serviceSchoolTblType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceSchoolTblType);
        }
    }

    /**
     * @param TblType|null $tblType
     */
    public function setServiceSchoolType(TblType $tblType = null)
    {

        $this->serviceSchoolTblType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblSiblingRank
     */
    public function getServiceStudentChildRank()
    {

        if (null === $this->serviceStudentSiblingRank) {
            return false;
        } else {
            return Relationship::useService()->getSiblingRankById($this->serviceStudentSiblingRank);
        }
    }

    /**
     * @param null|TblSiblingRank $tblSiblingRank
     */
    public function setServiceStudentSiblingRank(TblSiblingRank $tblSiblingRank = null)
    {

        $this->serviceStudentSiblingRank = ( null === $tblSiblingRank ? null : $tblSiblingRank->getId() );
    }
}
