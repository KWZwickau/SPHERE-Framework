<?php
namespace SPHERE\Application\System\Gatekeeper\Account\Data\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\System\Gatekeeper\Account\Data\DataBinding;

/**
 * @Entity
 * @Table(name="tblSession")
 */
class TblSession extends \SPHERE\System\Database\Fitting\Entity
{

    const ATTR_TBL_ACCOUNT = 'tblAccount';
    const ATTR_SESSION = 'Session';
    const ATTR_TIMEOUT = 'Timeout';

    /**
     * @Column(type="bigint")
     */
    protected $tblAccount;
    /**
     * @Column(type="string")
     */
    protected $Session;
    /**
     * @Column(type="integer")
     */
    protected $Timeout;

    /**
     * @param string $Session
     */
    function __construct( $Session )
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
            return ( new DataBinding() )->getAccountById( $this->tblAccount );
        }
    }

    /**
     * @param null|TblAccount $tblAccount
     */
    public function setTblAccount( TblAccount $tblAccount = null )
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
    public function setTimeout( $Timeout )
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
    public function setSession( $Session )
    {

        $this->Session = $Session;
    }
}
