<?php

namespace SPHERE\Application\Setting\Authorization\GroupRole\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Access;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Setting\Authorization\GroupRole\GroupRole;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGroupRoleLink")
 * @Cache(usage="READ_ONLY")
 */
class TblGroupRoleLink extends Element
{
    const ATTR_TBL_GROUP_ROLE = 'tblGroupRole';
    const SERVICE_TBL_ROLE = 'serviceTblRole';

    /**
     * @Column(type="bigint")
     */
    protected $tblGroupRole;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblRole;

    /**
     * @return bool|TblGroupRole
     */
    public function getTblGroupRole()
    {

        if (null === $this->tblGroupRole) {
            return false;
        } else {
            return GroupRole::useService()->getGroupRoleById($this->tblGroupRole);
        }
    }

    /**
     * @param null|TblGroupRole $tblGroupRole
     */
    public function setTblGroupRole(TblGroupRole $tblGroupRole = null)
    {
        $this->tblGroupRole = ( null === $tblGroupRole ? null : $tblGroupRole->getId() );
    }

    /**
     * @return bool|TblRole
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
     * @param null|TblRole $tblRole
     */
    public function setServiceTblRole(TblRole $tblRole = null)
    {
        $this->serviceTblRole = ( null === $tblRole ? null : $tblRole->getId() );
    }
}