<?php
namespace SPHERE\Application\Billing\Bookkeeping\Balance\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Balance\Balance;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPayment")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblPayment extends Element
{

    const ATTR_TBL_BALANCE = 'tblBalance';

    /**
     * @Column(type="bigint")
     */
    protected $tblBalance;
    /**
     * @Column(type="decimal", precision=14, scale=4)
     */
    protected $Value;
    /**
     * @Column(type="date")
     */
    protected $Date;

    /**
     * @return bool|TblBalance
     */
    public function getTblBalance()
    {

        if (null === $this->tblBalance) {
            return false;
        } else {
            return Balance::useService()->getBalanceById($this->tblBalance);
        }
    }

    /**
     * @param null|TblBalance $tblBalance
     */
    public function setTblBalance($tblBalance = null)
    {

        $this->tblBalance = ( null === $tblBalance ? null : $tblBalance->getId() );
    }

    /**
     * @return string $Date
     */
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param \DateTime $Date
     */
    public function setDate(\DateTime $Date)
    {

        $this->Date = $Date;
    }

    /**
     * @return (type="decimal", precision=14, scale=4)
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @param $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return string
     */
    public function getValueString()
    {

        $result = sprintf("%01.2f", $this->Value);
        return str_replace('.', ',', $result)." €";
    }
}
