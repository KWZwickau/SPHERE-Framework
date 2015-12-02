<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Relationship\Relationship;
use SPHERE\Application\People\Relationship\Service\Entity\TblSiblingRank;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItem")
 * @Cache(usage="READ_ONLY")
 */
class TblItem extends Element
{

    const ATTR_SERVICE_SCHOOL_TYPE = 'serviceSchoolTblType';
    const ATTR_SERVICE_STUDENT_SIBLING_RANK = 'serviceStudentSiblingRank';
    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Price;
    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $CostUnit;
    /**
     * @Column(type="bigint")
     */
    protected $serviceSchoolTblType;
    /**
     * @Column(type="bigint")
     */
    protected $serviceStudentSiblingRank;

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
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getPrice()
    {

        return $this->Price;
    }

    /**
     * @param (type="decimal", precision=14, scale=4) $Price
     */
    public function setPrice($Price)
    {

        $this->Price = $Price;
    }

    /**
     * @return string
     */
    public function getPriceString()
    {

        $result = sprintf("%01.4f", $this->Price);
        return str_replace('.', ',', $result)." €";
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
    public function getCostUnit()
    {

        return $this->CostUnit;
    }

    /**
     * @param string $CostUnit
     */
    public function setCostUnit($CostUnit)
    {

        $this->CostUnit = $CostUnit;
    }

    /**
     * @return bool|TblType
     */
    public function getServiceStudentType()
    {

        if (null === $this->serviceSchoolTblType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceSchoolTblType);
        }
    }

    /**
     * @param null|TblType $tblType
     */
    public function setServiceStudentType(TblType $tblType = null)
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
