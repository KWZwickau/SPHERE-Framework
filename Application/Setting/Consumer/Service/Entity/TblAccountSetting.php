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
 * @Table(name="tblAccountSetting")
 * @Cache(usage="READ_ONLY")
 */
class TblAccountSetting extends Element
{
    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const ATTR_IDENTIFIER = 'Identifier';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAccount;

    /**
     * @Column(type="string")
     */
    protected string $Identifier;

    /**
     * @Column(type="string")
     */
    protected string $Value;

    /**
     * @return TblAccount|false
     */
    public function getServiceTblAccount()
    {
        if (null === $this->serviceTblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById($this->serviceTblAccount);
        }
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount = null)
    {
        $this->serviceTblAccount = (null === $tblAccount ? null : $tblAccount->getId());
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier(string $Identifier): void
    {
        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue(string $Value): void
    {
        $this->Value = $Value;
    }
}