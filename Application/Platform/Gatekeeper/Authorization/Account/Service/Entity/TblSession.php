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
 * @Table(name="tblSession")
 * @Cache(usage="READ_ONLY")
 */
class TblSession extends Element
{

    const ATTR_SESSION = 'Session';
    const ATTR_TIMEOUT = 'Timeout';
    const ATTR_TBL_ACCOUNT = 'tblAccount';

    /**
     * @Column(type="string")
     */
    protected $Session;
    /**
     * @Column(type="integer")
     */
    protected $Timeout;
    /**
     * @Column(type="bigint")
     */
    protected $tblAccount;

    /**
     * @param string $Session
     */
    public function __construct($Session)
    {

        $this->Session = $Session;
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

    /**
     * @return integer
     */
    public function getTimeout()
    {

        return $this->Timeout;
    }

    /**
     * @param integer $Timeout
     */
    public function setTimeout($Timeout)
    {

        $this->Timeout = $Timeout;
    }

    /**
     * @return string
     */
    public function getSession()
    {

        return $this->Session;
    }

    /**
     * @param string $Session
     */
    public function setSession($Session)
    {

        $this->Session = $Session;
    }
}
