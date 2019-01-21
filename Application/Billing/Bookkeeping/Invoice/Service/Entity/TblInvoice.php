<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Bookkeeping\Invoice\Invoice;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblInvoice")
 * @Cache(usage="READ_ONLY")
 */
class TblInvoice extends Element
{

    const ATTR_INVOICE_NUMBER = 'InvoiceNumber';
    const ATTR_INTEGER_NUMBER = 'IntegerNumber';
    const ATTR_YEAR = 'Year';
    const ATTR_MONTH = 'Month';
    const ATTR_TARGET_TIME = 'TargetTime';
    const ATTR_FIRST_NAME = 'FirstName';
    const ATTR_LAST_NAME = 'LastName';
    const ATTR_BASKET_NAME = 'BasketName';
    const ATTR_SERVICE_TBL_PERSON_CAUSER = 'serviceTblPersonCauser';
    const ATTR_TBL_INVOICE_CREDITOR = 'tblInvoiceCreditor';

    /**
     * @Column(type="string")
     */
    protected $InvoiceNumber;
    /**
     * @Column(type="bigint")
     */
    protected $IntegerNumber;
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
     * @Column(type="string")
     */
    protected $FirstName;
    /**
     * @Column(type="string")
     */
    protected $LastName;
    /**
     * @Column(type="string")
     */
    protected $BasketName;
//    /**
//     * @Column(type="decimal", precision=14, scale=4)
//     */
//    protected $Discount;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonCauser;
    /**
     * @Column(type="bigint")
     */
    protected $tblInvoiceCreditor;

    /**
     * @return string
     */
    public function getInvoiceNumber()
    {

        return $this->InvoiceNumber;
    }

    /**
     * @param string $InvoiceNumber
     */
    public function setInvoiceNumber($InvoiceNumber)
    {

        $this->InvoiceNumber = $InvoiceNumber;
    }

    /**
     * @return int
     */
    public function getIntegerNumber()
    {
        return $this->IntegerNumber;
    }

    /**
     * @param int $IntegerNumber
     */
    public function setIntegerNumber($IntegerNumber)
    {
        $this->IntegerNumber = $IntegerNumber;
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
            if(strlen($this->Month) == 1) {
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
     * @return bool|string
     */
    public function getTargetTime()
    {

        if (null === $this->TargetTime) {
            return false;
        }
        /** @var \DateTime $InvoiceDate */
        $TargetDate = $this->TargetTime;
        if ($TargetDate instanceof \DateTime) {
            return $TargetDate->format('d.m.Y');
        } else {
            return (string)$TargetDate;
        }
    }

    /**
     * @param \DateTime|null $Date
     */
    public function setTargetTime(\DateTime $Date = null)
    {

        $this->TargetTime = $Date;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->FirstName;
    }

    /**
     * @param string $FirstName
     */
    public function setFirstName($FirstName)
    {
        $this->FirstName = $FirstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->LastName;
    }

    /**
     * @param string $LastName
     */
    public function setLastName($LastName)
    {
        $this->LastName = $LastName;
    }

    /**
     * @return string
     */
    public function getBasketName()
    {
        return $this->BasketName;
    }

    /**
     * @param string $BasketName
     */
    public function setBasketName($BasketName)
    {
        $this->BasketName = $BasketName;
    }

//    /**
//     * @return (type="decimal", precision=14, scale=4)
//     */
//    public function getDiscount()
//    {
//
//        return $this->Discount;
//    }
//
//    /**
//     * @param (type="decimal", precision=14, scale=4) $Price
//     */
//    public function setDiscount($Discount)
//    {
//
//        $this->Discount = $Discount;
//    }



    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonCauser()
    {

        if (null === $this->serviceTblPersonCauser) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonCauser);
        }
    }

    /**
     * @param TblPerson|null $tblPersonCauser
     */
    public function setServiceTblPersonCauser(TblPerson $tblPersonCauser = null)
    {

        $this->serviceTblPersonCauser = ( null === $tblPersonCauser ? null : $tblPersonCauser->getId() );
    }

    /**
     * @return TblInvoiceCreditor|false
     */
    public function getTblInvoiceCreditor()
    {
        if (null === $this->tblInvoiceCreditor) {
            return false;
        } else {
            return Invoice::useService()->getInvoiceCreditorById($this->tblInvoiceCreditor);
        }
    }

    /**
     * @param TblInvoiceCreditor $tblInvoiceCreditor
     */
    public function setTblInvoiceCreditor(TblInvoiceCreditor $tblInvoiceCreditor)
    {
        $this->tblInvoiceCreditor = $tblInvoiceCreditor->getId();
    }
}
