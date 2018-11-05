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
 * @Table(name="tblBankAccount")
 * @Cache(usage="READ_ONLY")
 */
class TblBankAccount extends Element
{


    const ATTR_TBL_DEBTOR = 'tblDebtor';
    const ATTR_BANK_NAME = 'BankName';
    const ATTR_IBAN = 'IBAN';
    const ATTR_BIC = 'BIC';
    const ATTR_Owner = 'Owner';

    /**
     * @Column(type="bigint")
     */
    protected $tblDebtor;
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
     * @param TblDebtor $tblDebtor
     */
    public function setTblDebtor(TblDebtor $tblDebtor)
    {

        $this->tblDebtor = $tblDebtor->getId();
    }

    /**
     * @return string
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
     * @return string
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
        $this->IBAN = $IBAN;
    }

    /**
     * @return string
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
        $this->BIC = $BIC;
    }

    /**
     * @return string
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
     * @return string
     */
    public function getIBANFrontend()
    {

        $IBAN = $this->IBAN;
        $tmp = array();
        for ($i = 0, $j = strlen($IBAN); $i < $j; $i += 4) {
            array_push($tmp, substr($IBAN, $i, 4));
        }
        $result = implode(' ', $tmp);
        return $result;
    }

    /**
     * @return string
     */
    public function getBICFrontend()
    {

        $BIC = $this->BIC;
        $tmp = array();
        array_push($tmp, substr($BIC, 0, 4));
        array_push($tmp, substr($BIC, 4, 2));
        array_push($tmp, substr($BIC, 6, 2));
        array_push($tmp, substr($BIC, 8, 3));
        $result = implode(' ', $tmp);
        return $result;
    }
}
