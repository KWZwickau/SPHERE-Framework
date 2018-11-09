<?php
namespace SPHERE\Application\Billing\Accounting\Banking\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblBankReference")
 * @Cache(usage="READ_ONLY")
 */
class TblBankReference extends Element
{

    const ATTR_REFERENCE_NUMBER = 'ReferenceNumber';
    const ATTR_REFERENCE_DATE = 'ReferenceDate';
    const ATTR_TBL_DEBTOR = 'tblDebtor';
    const ATTR_TBL_BANK_ACCOUNT = 'tblBankAccount';
    const ATTR_IS_STANDARD = 'IsStandard';

    /**
     * @Column(type="string")
     */
    protected $ReferenceNumber;
    /**
     * @Column(type="date")
     */
    protected $ReferenceDate;
    /**
     * @Column(type="bigint")
     */
    protected $tblDebtor;
    /**
     * @Column(type="bigint")
     */
    protected $tblBankAccount;
    /**
     * @Column(type="boolean")
     */
    protected $IsStandard;


    /**
     * @return string $ReferenceNumber
     */
    public function getReferenceNumber()
    {

        return $this->ReferenceNumber;
    }

    /**
     * @param string $ReferenceNumber
     */
    public function setReference($ReferenceNumber)
    {

        $this->ReferenceNumber = $ReferenceNumber;
    }

    /**
     * @return string
     */
    public function getReferenceDate()
    {

        if (null === $this->ReferenceDate) {
            return false;
        }
        /** @var \DateTime $ReferenceDate */
        $ReferenceDate = $this->ReferenceDate;
        if ($ReferenceDate instanceof \DateTime) {
            return $ReferenceDate->format('d.m.Y');
        } else {
            return (string)$ReferenceDate;
        }
    }

    /**
     * @param \DateTime $ReferenceDate
     */
    public function setReferenceDate(\DateTime $ReferenceDate)
    {

        $this->ReferenceDate = $ReferenceDate;
    }

    /**
     * @return false|TblDebtor
     */
    public function getTblDebtor()
    {

        if (null === $this->tblDebtor) {
            return false;
        } else {
            return Banking::useService()->getDebtorById($this->tblDebtor);
        }
    }

    /**
     * @param TblDebtor|null $tblDebtor
     */
    public function setTblDebtor(TblDebtor $tblDebtor = null)
    {

        if(null !== $tblDebtor){
            $this->tblDebtor = $tblDebtor->getId();
        }
    }

    /**
     * @return false|TblBankAccount
     */
    public function getTblBankAccount()
    {
        if (null === $this->tblBankAccount) {
            return false;
        } else {
            return Banking::useService()->getBankAccountById($this->tblBankAccount);
        }
    }

    /**
     * @param TblBankAccount|null $tblBankAccount
     */
    public function setTblBankAccount(TblBankAccount $tblBankAccount = null)
    {
        if(null !== $tblBankAccount) {
            $this->tblBankAccount = $tblBankAccount->getId();
        }
    }

    /**
     * @return mixed
     */
    public function getisStandard()
    {
        return $this->IsStandard;
    }

    /**
     * @param mixed $IsStandard
     */
    public function setIsStandard($IsStandard)
    {
        $this->IsStandard = $IsStandard;
    }

}
