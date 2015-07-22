<?php
namespace SPHERE\Application\System\Gatekeeper\Account\Service\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\System\Gatekeeper\Account\Account;
use SPHERE\Application\System\Gatekeeper\Authorization\Authorization;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblRole;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblAuthorization")
 */
class TblAuthorization extends Element
{

    const ATTR_TBL_ACCOUNT = 'tblAccount';
    const SERVICE_TBL_ROLE = 'serviceTblRole';

    /**
     * @Column(type="bigint")
     */
    protected $tblAccount;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblRole;

    /**
     * @return bool|TblAccount
     */
    public function getTblAccount()
    {

        if (null === $this->tblAccount) {
            return false;
        } else {
            return Account::useService()->getAccountById( $this->tblAccount );
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
     * @return bool|TblRole
     */
    public function getServiceTblRole()
    {

        if (null === $this->serviceTblRole) {
            return false;
        } else {
            return Authorization::useService()->getRoleById( $this->serviceTblRole );
        }
    }

    /**
     * @param null|TblRole $tblRole
     */
    public function setServiceTblRole( TblRole $tblRole = null )
    {

        $this->serviceTblRole = ( null === $tblRole ? null : $tblRole->getId() );
    }
}
