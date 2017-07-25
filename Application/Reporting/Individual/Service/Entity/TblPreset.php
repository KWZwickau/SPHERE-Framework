<?php

namespace SPHERE\Application\Reporting\Individual\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblPreset")
 * @Cache(usage="READ_ONLY")
 */
class TblPreset extends Element
{

    const ATTR_TBL_ACCOUNT = 'tblAccount';
    const ATTR_NAME = 'Name';

    /**
     * @Column(type="bigint")
     */
    protected $tblAccount;
    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @return bool|TblAccount
     */
    public function getTblAccount()
    {

        if (null === $this->tblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->tblAccount);
        }
    }

    /**
     * @param null|TblAccount $tblAccount
     */
    public function setTblAccount(TblAccount $tblAccount = null)
    {

        $this->tblAccount = (null === $tblAccount ? null : $tblAccount->getId());
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param mixed $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }
}