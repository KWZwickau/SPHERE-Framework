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
 * @Table(name="tblAccount")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblAccount extends Element
{

    const ATTR_TBL_DEBTOR = 'tblDebtor';
    const ATTR_TBL_ACTIVE = 'Active';

//    /**
//     * @Column(type="integer")
//     */
//    protected $LeadTimeFirst;
//    /**
//     * @Column(type="integer")
//     */
//    protected $LeadTimeFollow;
    /**
     * @Column(type="string")
     */
    protected $BankName;
    /**
     * @Column(type="string")
     */
    protected $IBAN;
    /**
     * @Column(type="string")
     */
    protected $BIC;
    /**
     * @Column(type="string")
     */
    protected $Owner;
    /**
     * @Column(type="string")
     */
    protected $CashSign;
    /**
     * @Column(type="bigint")
     */
    protected $tblDebtor;
    /**
     * @Column(type="boolean")
     */
    protected $Active;

//    /**
//     * @return integer $LeadTimeFirst
//     */
//    public function getLeadTimeFirst()
//    {
//
//        return $this->LeadTimeFirst;
//    }
//
//    /**
//     * @param integer $LeadTimeFirst
//     */
//    public function setLeadTimeFirst($LeadTimeFirst)
//    {
//
//        $this->LeadTimeFirst = $LeadTimeFirst;
//    }
//
//    /**
//     * @return integer $LeadTimeFollow
//     */
//    public function getLeadTimeFollow()
//    {
//
//        return $this->LeadTimeFollow;
//    }
//
//    /**
//     * @param integer $LeadTimeFollow
//     */
//    public function setLeadTimeFollow($LeadTimeFollow)
//    {
//
//        $this->LeadTimeFollow = $LeadTimeFollow;
//    }

    /**
     * @return string $BankName
     */
    public function getBankName()
    {

        return $this->BankName;
    }

    /**
     * @param string $BankName
     */
    public function setBankName($BankName)
    {

        $this->BankName = $BankName;
    }

    /**
     * @return string $IBAN
     */
    public function getIBAN()
    {

        return $this->IBAN;
    }

    /**
     * @param string $IBAN
     */
    public function setIBAN($IBAN)
    {

        $this->IBAN = strtoupper(substr(str_replace(' ', '', $IBAN), 0, 34));
    }

    /**
     * @return string $BIC
     */
    public function getBIC()
    {

        return $this->BIC;
    }

    /**
     * @param string $BIC
     */
    public function setBIC($BIC)
    {

        $this->BIC = strtoupper(substr(str_replace(' ', '', $BIC), 0, 11));
    }

    /**
     * @return string $Owner
     */
    public function getOwner()
    {

        return $this->Owner;
    }

    /**
     * @param string $Owner
     */
    public function setOwner($Owner)
    {

        $this->Owner = $Owner;
    }

    /**
     * @return string $CashSign
     */
    public function getCashSign()
    {

        return $this->CashSign;
    }

    /**
     * @param string $CashSign
     */
    public function setCashSign($CashSign)
    {

        $this->CashSign = $CashSign;
    }

    /**
     * @return bool|TblDebtor
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

        $this->tblDebtor = ( null === $tblDebtor ? null : $tblDebtor->getId() );
    }

    /**
     * @return bool
     */
    public function getActive()
    {

        return $this->Active;
    }

    /**
     * @param bool|$Active
     */
    public function setActive($Active)
    {

        $this->Active = $Active;
    }

}
