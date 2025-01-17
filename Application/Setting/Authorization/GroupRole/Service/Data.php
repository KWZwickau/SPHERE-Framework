<?php

namespace SPHERE\Application\Setting\Authorization\GroupRole\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\Application\Setting\Authorization\GroupRole\Service\Entity\TblGroupRole;
use SPHERE\Application\Setting\Authorization\GroupRole\Service\Entity\TblGroupRoleLink;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Setting\Authorization\GroupRole\Service
 */
class Data extends AbstractData
{
    public function setupDatabaseContent()
    {

    }

    /**
     * @param $Name
     *
     * @return TblGroupRole
     */
    public function createGroupRole(
        $Name
    ) {
        $Manager = $this->getEntityManager();

        $Entity = new TblGroupRole();
        $Entity->setName($Name);

        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblGroupRole $tblGroupRole
     * @param $Name
     *
     * @return bool
     */
    public function updateGroupRole(
        TblGroupRole $tblGroupRole,
        $Name
    ) {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGroupRole $Entity */
        $Entity = $Manager->getEntityById('TblGroupRole', $tblGroupRole->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName($Name);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblGroupRole $tblGroupRole
     *
     * @return bool
     */
    public function destroyGroupRole(TblGroupRole $tblGroupRole)
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGroupRole $Entity */
        $Entity = $Manager->getEntityById('TblGroupRole', $tblGroupRole->getId());
        if (null !== $Entity) {
            $Manager->killEntity($Entity);
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblGroupRole
     */
    public function getGroupRoleById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblGroupRole', $Id);
    }

    /**
     * @return false|TblGroupRole[]
     */
    public function getGroupRoleAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblGroupRole');
    }

    /**
     * @param TblGroupRole $tblGroupRole
     *
     * @return false|TblGroupRoleLink[]
     */
    public function getGroupRoleLinkAllByGroupRole(TblGroupRole $tblGroupRole)
    {
        return $this->getCachedEntityListBy(__METHOD__, $this->getEntityManager(), 'TblGroupRoleLink', array(
            TblGroupRoleLink::ATTR_TBL_GROUP_ROLE => $tblGroupRole->getId()
        ));
    }

    /**
     * @param TblGroupRole $tblGroupRole
     * @param TblRole $tblRole
     *
     * @return TblGroupRoleLink
     */
    public function addGroupRoleLink(TblGroupRole $tblGroupRole, TblRole $tblRole)
    {
        $Manager = $this->getConnection()->getEntityManager();
        $Entity = $Manager->getEntity('TblGroupRoleLink')
            ->findOneBy(array(
                TblGroupRoleLink::ATTR_TBL_GROUP_ROLE  => $tblGroupRole->getId(),
                TblGroupRoleLink::SERVICE_TBL_ROLE => $tblRole->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblGroupRoleLink();
            $Entity->setTblGroupRole($tblGroupRole);
            $Entity->setServiceTblRole($tblRole);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblGroupRole $tblGroupRole
     * @param TblRole $tblRole
     *
     * @return bool
     */
    public function removeGroupRoleLink(TblGroupRole $tblGroupRole, TblRole $tblRole)
    {
        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblGroupRoleLink $Entity */
        $Entity = $Manager->getEntity('TblGroupRoleLink')
            ->findOneBy(array(
                TblGroupRoleLink::ATTR_TBL_GROUP_ROLE     => $tblGroupRole->getId(),
                TblGroupRoleLink::SERVICE_TBL_ROLE => $tblRole->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);

            $Manager->killEntity($Entity);

            return true;
        }
        return false;
    }
}