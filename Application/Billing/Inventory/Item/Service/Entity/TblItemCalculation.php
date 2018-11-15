<?php
namespace SPHERE\Application\Billing\Inventory\Item\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Inventory\Item\Item;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblItemCalculation")
 * @Cache(usage="READ_ONLY")
 */
class TblItemCalculation extends Element
{

    const ATTR_SERVICE_TBL_TYPE = 'serviceTblType';
    const ATTR_TBL_ITEM_VARIANT = 'tblItemVariant';

    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Value;
    /**
     * @Column(type="date")
     */
    protected $DateFrom;
    /**
     * @Column(type="date")
     */
    protected $DateTo;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblType;
    /**
     * @Column(type="bigint")
     */
    protected $tblItemVariant;

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
     * @return bool|string
     */
    public function getDateFrom()
    {

        if (null === $this->DateFrom) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->DateFrom;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $DateFrom
     */
    public function setDateFrom(\DateTime $DateFrom = null)
    {
        $this->DateFrom = $DateFrom;
    }

    /**
     * @return bool|string
     */
    public function getDateTo()
    {
        if (null === $this->DateTo) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->DateTo;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $DateTo
     */
    public function setDateTo(\DateTime $DateTo = null)
    {
        $this->DateTo = $DateTo;
    }



    /**
     * @return string
     */
    public function getPriceString()
    {

        $result = sprintf("%01.2f", $this->Value);
        return str_replace('.', ',', $result)." €";
    }

    /**
     * @return bool|TblType
     */
    public function getServiceTblType()
    {

        if (null === $this->serviceTblType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblType);
        }
    }

    /**
     * @param TblType|null $tblType
     */
    public function setServiceTblType(TblType $tblType = null)
    {

        $this->serviceTblType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return false|TblItemVariant
     */
    public function getTblVariant()
    {
        if(null === null){
            return false;
        } else {
            return Item::useService()->getItemVariantById($this->tblItemVariant);
        }
    }

    /**
     * @param null|TblItemVariant $tblItemVariant
     */
    public function setTblVariant(TblItemVariant $tblItemVariant = null)
    {
        $this->tblItemVariant = ( null === $tblItemVariant ? null : $tblItemVariant->getId() );
    }


}
