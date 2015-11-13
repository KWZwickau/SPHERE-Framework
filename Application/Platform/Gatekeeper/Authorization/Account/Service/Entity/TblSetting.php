<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSetting")
 * @Cache(usage="READ_ONLY")
 */
class TblSetting extends Element
{

    const ATTR_TBL_ACCOUNT = 'tblAccount';
    const ATTR_IDENTIFIER = 'Identifier';

    /**
     * @Column(type="bigint")
     */
    protected $tblAccount;
    /**
     * @Column(type="string")
     */
    protected $Identifier;
    /**
     * @Column(type="text")
     */
    protected $Value;

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

        $this->tblAccount = ( null === $tblAccount ? null : $tblAccount->getId() );
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {

        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {

        $this->Identifier = $Identifier;
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {

        $this->Value = $Value;
    }
}
