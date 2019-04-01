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
 * @Table(name="tblAccountInitial")
 * @Cache(usage="READ_ONLY")
 */
class TblAccountInitial extends Element
{

    const ATTR_TBL_ACCOUNT = 'tblAccount';
    const ATTR_PASSWORD = 'Password';
    /**
     * @Column(type="bigint")
     */
    protected $tblAccount;
    /**
     * @Column(type="string")
     */
    protected $Password;

    /**
     * @return string
     */
    public function getPassword()
    {

        return $this->Password;
    }

    /**
     * @param string $Password
     */
    public function setPassword($Password)
    {

        $this->Password = $Password;
    }

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
}
