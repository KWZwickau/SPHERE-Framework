<?php
namespace SPHERE\Application\Billing\Bookkeeping\Invoice\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Billing\Accounting\Banking\Banking;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblBankReference;
use SPHERE\Application\Billing\Accounting\Banking\Service\Entity\TblDebtor as BankingDebtor;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblDebtor")
 * @Cache(usage="READ_ONLY")
 */
class TblDebtor extends Element
{

    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';

    /**
     * @Column(type="string")
     */
    protected $DebtorNumber;
    /**
     * @Column(type="string")
     */
    protected $DebtorPerson;
    /**
     * @Column(type="string")
     */
    protected $Reference;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDebtor;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblBankReference;

    /**
     * @return string
     */
    public function getDebtorNumber()
    {

        return $this->DebtorNumber;
    }

    /**
     * @param string $DebtorNumber
     */
    public function setDebtorNumber($DebtorNumber)
    {

        $this->DebtorNumber = $DebtorNumber;
    }

    /**
     * @return string
     */
    public function getDebtorPerson()
    {

        return $this->DebtorPerson;
    }

    /**
     * @param TblPerson $tblPerson
     */
    public function setDebtorPerson(TblPerson $tblPerson)
    {

        $this->DebtorPerson = ( $tblPerson !== false ? $tblPerson->getFullName() : '' );
    }

    /**
     * @return string
     */
    public function getReference()
    {

        $this->Reference;
    }

    /**
     * @param $Reference
     */
    public function setReference($Reference)
    {

        $this->Reference = $Reference;
    }

    /**
     * @return bool|BankingDebtor
     */
    public function getServiceTblDebtor()
    {

        if (null === $this->serviceTblDebtor) {
            return false;
        } else {
            return Banking::useService()->getDebtorById($this->serviceTblDebtor);
        }
    }

    /**
     * @param BankingDebtor|null $tblDebtor
     */
    public function setServiceTblDebtor(BankingDebtor $tblDebtor = null)
    {

        $this->serviceTblDebtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }

    /**
     * @return bool|TblBankReference
     */
    public function getServiceTblBankReference()
    {

        if (null === $this->serviceTblBankReference) {
            return false;
        } else {
            return Banking::useService()->getBankReferenceById($this->serviceTblBankReference);
        }
    }

    /**
     * @param TblBankReference|null $tblBankReference
     */
    public function setServiceTblBankReference(TblBankReference $tblBankReference = null)
    {

        $this->serviceTblBankReference = ( null === $tblBankReference ? null : $tblBankReference->getId() );
    }
}