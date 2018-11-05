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
 * @Table(name="tblDebtorNumber")
 * @Cache(usage="READ_ONLY")
 */
class TblDebtorNumber extends Element
{


    const ATTR_TBL_DEBTOR = 'tblDebtor';
    const ATTR_DEBTOR_NUMBER = 'DebtorNumber';


    /**
     * @Column(type="bigint")
     */
    protected $tblDebtor;
    /**
     * @Column(type="string")
     */
    protected $DebtorNumber;

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
}
