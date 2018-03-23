<?php

namespace SPHERE\Application\Setting\Consumer\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentCustody")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentCustody extends Element
{

    const ATTR_SERVICE_TBL_ACCOUNT_STUDENT = 'serviceTblAccountStudent';
    const ATTR_SERVICE_TBL_ACCOUNT_CUSTODY = 'serviceTblAccountCustody';
    const ATTR_SERVICE_TBL_ACCOUNT_BLOCKER = 'serviceTblAccountBlocker';

    /**
     * @Column(type="integer")
     */
    protected $serviceTblAccountStudent;
    /**
     * @Column(type="integer")
     */
    protected $serviceTblAccountCustody;
    /**
     * @Column(type="integer")
     */
    protected $serviceTblAccountBlocker;

    /**
     * @return bool|TblAccount
     */
    public function getServiceTblAccountStudent()
    {
        $tblAccount = ($this->serviceTblAccountStudent != null
            ? Account::useService()->getAccountById($this->serviceTblAccountStudent)
            : false);
        if ($tblAccount) {
            return $tblAccount;
        }
        return false;
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccountStudent(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccountStudent = (null === $tblAccount ? null : $tblAccount->getId());
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceTblAccountCustody()
    {
        $tblAccount = ($this->serviceTblAccountCustody != null
            ? Account::useService()->getAccountById($this->serviceTblAccountCustody)
            : false);
        if ($tblAccount) {
            return $tblAccount;
        }
        return false;
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccountCustody(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccountCustody = (null === $tblAccount ? null : $tblAccount->getId());
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceTblAccountBlocker()
    {
        $tblAccount = ($this->serviceTblAccountBlocker != null
            ? Account::useService()->getAccountById($this->serviceTblAccountBlocker)
            : false);
        if ($tblAccount) {
            return $tblAccount;
        }
        return false;
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccountBlocker(TblAccount $tblAccount = null)
    {

        $this->serviceTblAccountBlocker = (null === $tblAccount ? null : $tblAccount->getId());
    }
}