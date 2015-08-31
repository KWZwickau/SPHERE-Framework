<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblAuthorization")
 * @Cache(usage="READ_ONLY")
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
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole
     */
    public function getServiceTblRole()
    {

        if (null === $this->serviceTblRole) {
            return false;
        } else {
            return Access::useService()->getRoleById($this->serviceTblRole);
        }
    }

    /**
     * @param null|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole $tblRole
     */
    public function setServiceTblRole(TblRole $tblRole = null)
    {

        $this->serviceTblRole = ( null === $tblRole ? null : $tblRole->getId() );
    }
}
