<?php

namespace SPHERE\Application\Billing\Bookkeeping\Basket\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Creditor\Creditor;
use SPHERE\Application\Billing\Accounting\Creditor\Service\Entity\TblCreditor;
use SPHERE\Application\Billing\Accounting\Debtor\Debtor;
use SPHERE\Application\Billing\Accounting\Debtor\Service\Entity\TblDebtorPeriodType;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblDivision;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBasket")
 * @Cache(usage="READ_ONLY")
 */
class TblBasket extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_MONTH = 'Month';
    const ATTR_YEAR = 'Year';
    const ATTR_SERVICE_TBL_CREDITOR = 'serviceTblCreditor';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_TYPE = 'serviceTblType';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="text")
     */
    protected $Description;
    /**
     * @Column(type="string")
     */
    protected $Year;
    /**
     * @Column(type="string")
     */
    protected $Month;
    /**
     * @Column(type="datetime")
     */
    protected $TargetTime;
    /**
     * @Column(type="boolean")
     */
    protected $IsDone;
    /**
     * @Column(type="datetime")
     */
    protected $SepaDate;
    /**
     * @Column(type="string")
     */
    protected $SepaUser;
    /**
     * @Column(type="datetime")
     */
    protected $DatevDate;
    /**
     * @Column(type="string")
     */
    protected $DatevUser;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCreditor;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblType;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDebtorPeriodType;

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
     * @return string
     */
    public function getYear()
    {
        return $this->Year;
    }

    /**
     * @param string $Year
     */
    public function setYear($Year)
    {
        $this->Year = $Year;
    }

    /**
     * @param bool $IsFrontend
     *
     * @return string
     */
    public function getMonth($IsFrontend = false)
    {
        if($IsFrontend){
            if(strlen($this->Month) == 1){
                $Month = '0'.$this->Month;
            } else {
                $Month = $this->Month;
            }
            return $Month;
        }
        return $this->Month;
    }

    /**
     * @param string $Month
     */
    public function setMonth($Month)
    {
        $this->Month = $Month;
    }

    /**
     * @return string
     */
    public function getTargetTime()
    {

        if(null === $this->TargetTime){
            return false;
        }
        /** @var \DateTime $TargetTime */
        $TargetTime = $this->TargetTime;
        if($TargetTime instanceof \DateTime){
            return $TargetTime->format('d.m.Y');
        } else {
            return (string)$TargetTime;
        }
    }

    /**
     * @param null|\DateTime $TargetTime
     */
    public function setTargetTime(\DateTime $TargetTime = null)
    {
        $this->TargetTime = $TargetTime;
    }

    /**
     * @return boolean
     */
    public function getIsDone()
    {
        return $this->IsDone;
    }

    /**
     * @param boolean $IsDone
     */
    public function setIsDone($IsDone)
    {
        $this->IsDone = $IsDone;
    }

    /**
     * @return mixed
     */
    public function getSepaDate()
    {

        if(null === $this->SepaDate){
            return false;
        }
        /** @var \DateTime $SepaDate */
        $SepaDate = $this->SepaDate;
        if($SepaDate instanceof \DateTime){
            return $SepaDate->format('d.m.Y');
        } else {
            return (string)$SepaDate;
        }
    }

    /**
     * @param \DateTime|null $SepaDate
     */
    public function setSepaDate(\DateTime $SepaDate = null)
    {
        $this->SepaDate = $SepaDate;
    }

    /**
     * @return mixed
     */
    public function getSepaUser()
    {
        return $this->SepaUser;
    }

    /**
     * @param mixed $SepaUser
     */
    public function setSepaUser($SepaUser)
    {
        $this->SepaUser = $SepaUser;
    }

    /**
     * @return mixed
     */
    public function getDatevDate()
    {
        if(null === $this->DatevDate){
            return false;
        }
        /** @var \DateTime $DatevDate */
        $DatevDate = $this->DatevDate;
        if($DatevDate instanceof \DateTime){
            return $DatevDate->format('d.m.Y');
        } else {
            return (string)$DatevDate;
        }
    }

    /**
     * @param \DateTime|null $DatevDate
     */
    public function setDatevDate(\DateTime $DatevDate = null)
    {
        $this->DatevDate = $DatevDate;
    }

    /**
     * @return mixed
     */
    public function getDatevUser()
    {
        return $this->DatevUser;
    }

    /**
     * @param mixed $DatevUser
     */
    public function setDatevUser($DatevUser)
    {
        $this->DatevUser = $DatevUser;
    }

    /**
     * @return bool|TblCreditor
     */
    public function getServiceTblCreditor()
    {

        if(null !== $this->serviceTblCreditor){
            return Creditor::useService()->getCreditorById($this->serviceTblCreditor);
        }
        return false;

    }

    /**
     * @param null|TblCreditor $serviceTblCreditor
     */
    public function setServiceTblCreditor(TblCreditor $serviceTblCreditor = null)
    {

        $this->serviceTblCreditor = ($serviceTblCreditor ? $serviceTblCreditor->getId() : null);
    }

    /**
     * @return TblDivision|false
     */
    public function getServiceTblDivision()
    {

        if(null !== $this->serviceTblDivision){
            return Division::useService()->getDivisionById($this->serviceTblDivision);
        }
        return false;
    }

    /**
     * @param TblDivision|bool $serviceTblDivision
     */
    public function setServiceTblDivision($serviceTblDivision)
    {

        $this->serviceTblDivision = ($serviceTblDivision ? $serviceTblDivision->getId() : null);
    }

    /**
     * @return TblType|false
     */
    public function getServiceTblType()
    {

        if(null !== $this->serviceTblType){
            return Type::useService()->getTypeById($this->serviceTblType);
        }
        return false;
    }

    /**
     * @param TblType|bool $serviceTblType
     */
    public function setServiceTblType($serviceTblType)
    {

        $this->serviceTblType = ($serviceTblType ? $serviceTblType->getId() : null);
    }

    /**
     * @return TblDebtorPeriodType|false
     */
    public function getServiceTblDebtorPeriodType()
    {

        if(null !== $this->serviceTblDebtorPeriodType){
            return Debtor::useService()->getDebtorPeriodTypeById($this->serviceTblDebtorPeriodType);
        }
        return false;
    }

    /**
     * @param TblDebtorPeriodType|bool $serviceTblDebtorPeriodType
     */
    public function setServiceTblDebtorPeriodType($serviceTblDebtorPeriodType)
    {

        $this->serviceTblDebtorPeriodType = ($serviceTblDebtorPeriodType ? $serviceTblDebtorPeriodType->getId() : null);
    }


}
