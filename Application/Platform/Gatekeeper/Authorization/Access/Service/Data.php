<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service;

use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevelPrivilege;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilegeRight;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRight;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRoleLevel;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\DataCacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service
 */
class Data extends DataCacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

        /**
         * Administrator
         */
        // Role: Cloud-Administrator
        $tblRole = $this->createRole('Administrator');

        // Level: Platform
        $tblLevel = $this->createLevel('Platform');
        $this->addRoleLevel($tblRole, $tblLevel);
        // Privilege: Platform
        $tblPrivilege = $this->createPrivilege('Platform');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: System (Platform)
        $tblPrivilege = $this->createPrivilege('System (Platform)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/System');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: History (Platform,System)
        $tblPrivilege = $this->createPrivilege('History (Platform,System)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/System/Protocol');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Archive');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Cache (Platform,System)
        $tblPrivilege = $this->createPrivilege('Cache (Platform,System)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/System/Cache');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Database (Platform,System)
        $tblPrivilege = $this->createPrivilege('Database (Platform,System)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/System/Database');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Database/Setup/Simulation');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Database/Setup/Execution');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Test (Platform,System)
        $tblPrivilege = $this->createPrivilege('Test (Platform,System)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/System/Test');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Test/Frontend');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Test/Upload');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Test/Upload/Delete');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Test/Upload/Delete/Check');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);

        // Level: Authorization
        $tblLevel = $this->createLevel('Authorization');
        $this->addRoleLevel($tblRole, $tblLevel);
        // Privilege: Platform
        $tblPrivilege = $this->createPrivilege('Platform');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Gatekeeper (Platform)
        $tblPrivilege = $this->createPrivilege('Gatekeeper (Platform)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Authorization (Platform,Gatekeeper)
        $tblPrivilege = $this->createPrivilege('Authorization (Platform,Gatekeeper)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Access (Platform,Gatekeeper,Authorization)
        $tblPrivilege = $this->createPrivilege('Access (Platform,Gatekeeper,Authorization)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Access');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Access/Role');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Access/RoleGrantLevel');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Access/Level');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Access/LevelGrantPrivilege');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Access/Privilege');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Access/PrivilegeGrantRight');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Access/Right');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);

        // Level: Consumer
        $tblLevel = $this->createLevel('Consumer');
        $this->addRoleLevel($tblRole, $tblLevel);
        // Privilege: Platform
        $tblPrivilege = $this->createPrivilege('Platform');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Gatekeeper (Platform)
        $tblPrivilege = $this->createPrivilege('Gatekeeper (Platform)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Authorization (Platform,Gatekeeper)
        $tblPrivilege = $this->createPrivilege('Authorization (Platform,Gatekeeper)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Consumer (Platform,Gatekeeper,Authorization)
        $tblPrivilege = $this->createPrivilege('Consumer (Platform,Gatekeeper,Authorization)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Consumer');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);

        // Level: Token
        $tblLevel = $this->createLevel('Token');
        $this->addRoleLevel($tblRole, $tblLevel);
        // Privilege: Platform
        $tblPrivilege = $this->createPrivilege('Platform');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Gatekeeper (Platform)
        $tblPrivilege = $this->createPrivilege('Gatekeeper (Platform)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Authorization (Platform,Gatekeeper)
        $tblPrivilege = $this->createPrivilege('Authorization (Platform,Gatekeeper)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Token (Platform,Gatekeeper,Authorization)
        $tblPrivilege = $this->createPrivilege('Token (Platform,Gatekeeper,Authorization)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Token');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);

        // Level: Account
        $tblLevel = $this->createLevel('Account');
        $this->addRoleLevel($tblRole, $tblLevel);
        // Privilege: Platform
        $tblPrivilege = $this->createPrivilege('Platform');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Gatekeeper (Platform)
        $tblPrivilege = $this->createPrivilege('Gatekeeper (Platform)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Authorization (Platform,Gatekeeper)
        $tblPrivilege = $this->createPrivilege('Authorization (Platform,Gatekeeper)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        // Privilege: Account (Platform,Gatekeeper,Authorization)
        $tblPrivilege = $this->createPrivilege('Account (Platform,Gatekeeper,Authorization)');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Account');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);

    }

    /**
     * @param string $Name
     *
     * @return TblRole
     */
    public function createRole($Name)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblRole')->findOneBy(array(TblRole::ATTR_NAME => $Name));
        if (null === $Entity) {
            $Entity = new TblRole($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return TblLevel
     */
    public function createLevel($Name)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblLevel')->findOneBy(array(TblLevel::ATTR_NAME => $Name));
        if (null === $Entity) {
            $Entity = new TblLevel($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblRole  $tblRole
     * @param TblLevel $tblLevel
     *
     * @return TblRoleLevel
     */
    public function addRoleLevel(TblRole $tblRole, TblLevel $tblLevel)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblRoleLevel')
            ->findOneBy(array(
                TblRoleLevel::ATTR_TBL_ROLE  => $tblRole->getId(),
                TblRoleLevel::ATTR_TBL_LEVEL => $tblLevel->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblRoleLevel();
            $Entity->setTblRole($tblRole);
            $Entity->setTblLevel($tblLevel);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return TblPrivilege
     */
    public function createPrivilege($Name)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblPrivilege')->findOneBy(array(TblPrivilege::ATTR_NAME => $Name));
        if (null === $Entity) {
            $Entity = new TblPrivilege($Name);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblLevel     $tblLevel
     * @param TblPrivilege $tblPrivilege
     *
     * @return TblLevelPrivilege
     */
    public function addLevelPrivilege(TblLevel $tblLevel, TblPrivilege $tblPrivilege)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblLevelPrivilege')
            ->findOneBy(array(
                TblLevelPrivilege::ATTR_TBL_LEVEL     => $tblLevel->getId(),
                TblLevelPrivilege::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblLevelPrivilege();
            $Entity->setTblLevel($tblLevel);
            $Entity->setTblPrivilege($tblPrivilege);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param string $Route
     *
     * @return TblRight
     */
    public function createRight($Route)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblRight')->findOneBy(array(TblRight::ATTR_ROUTE => $Route));
        if (null === $Entity) {
            $Entity = new TblRight($Route);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPrivilege $tblPrivilege
     * @param TblRight     $tblRight
     *
     * @return TblPrivilegeRight
     */
    public function addPrivilegeRight(TblPrivilege $tblPrivilege, TblRight $tblRight)
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity('TblPrivilegeRight')
            ->findOneBy(array(
                TblPrivilegeRight::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId(),
                TblPrivilegeRight::ATTR_TBL_RIGHT     => $tblRight->getId()
            ));
        if (null === $Entity) {
            $Entity = new TblPrivilegeRight();
            $Entity->setTblPrivilege($tblPrivilege);
            $Entity->setTblRight($tblRight);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblRole  $tblRole
     * @param TblLevel $tblLevel
     *
     * @return bool
     */
    public function removeRoleLevel(TblRole $tblRole, TblLevel $tblLevel)
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblRoleLevel $Entity */
        $Entity = $Manager->getEntity('TblRoleLevel')
            ->findOneBy(array(
                TblRoleLevel::ATTR_TBL_ROLE  => $tblRole->getId(),
                TblRoleLevel::ATTR_TBL_LEVEL => $tblLevel->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPrivilege $tblPrivilege
     * @param TblRight     $tblRight
     *
     * @return bool
     */
    public function removePrivilegeRight(TblPrivilege $tblPrivilege, TblRight $tblRight)
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblPrivilegeRight $Entity */
        $Entity = $Manager->getEntity('TblPrivilegeRight')
            ->findOneBy(array(
                TblPrivilegeRight::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId(),
                TblPrivilegeRight::ATTR_TBL_RIGHT     => $tblRight->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblLevel     $tblLevel
     * @param TblPrivilege $tblPrivilege
     *
     * @return bool
     */
    public function removeLevelPrivilege(TblLevel $tblLevel, TblPrivilege $tblPrivilege)
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblLevelPrivilege $Entity */
        $Entity = $Manager->getEntity('TblLevelPrivilege')
            ->findOneBy(array(
                TblLevelPrivilege::ATTR_TBL_LEVEL     => $tblLevel->getId(),
                TblLevelPrivilege::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId()
            ));
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->Connection->getDatabase(), $Entity);
            $Manager->killEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblRight
     */
    public function getRightById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblRight', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblRight
     */
    public function getRightByName($Name)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->Connection->getEntityManager(), 'TblRight', array(
            TblRight::ATTR_ROUTE => $Name
        ));
    }

    /**
     * @return bool|TblRight[]
     */
    public function getRightAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblRight');
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblLevel
     */
    public function getLevelById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblLevel', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblLevel
     */
    public function getLevelByName($Name)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblLevel')
            ->findOneBy(array(TblLevel::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPrivilege
     */
    public function getPrivilegeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblPrivilege', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPrivilege
     */
    public function getPrivilegeByName($Name)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblPrivilege')
            ->findOneBy(array(TblPrivilege::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     *
     * @param TblLevel $tblLevel
     *
     * @return bool|TblLevelPrivilege[]
     */
    public function getPrivilegeAllByLevel(TblLevel $tblLevel)
    {

        /** @var TblLevelPrivilege[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity('TblLevelPrivilege')->findBy(array(
            TblLevelPrivilege::ATTR_TBL_LEVEL => $tblLevel->getId()
        ));
        array_walk($EntityList, function (TblLevelPrivilege &$V) {

            $V = $V->getTblPrivilege();
        });
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     *
     * @param TblPrivilege $tblPrivilege
     *
     * @return bool|TblRight[]
     */
    public function getRightAllByPrivilege(TblPrivilege $tblPrivilege)
    {

        /** @var TblPrivilegeRight[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity('TblPrivilegeRight')->findBy(array(
            TblPrivilegeRight::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId()
        ));
        array_walk($EntityList, function (TblPrivilegeRight &$V) {

            $V = $V->getTblRight();
        });
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @return bool|TblPrivilege[]
     */
    public function getPrivilegeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblPrivilege');
    }

    /**
     * @return bool|TblLevel[]
     */
    public function getLevelAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblLevel');
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblRole
     */
    public function getRoleById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblRole', $Id);
    }

    /**
     * @param string $Name
     *
     * @return bool|TblRole
     */
    public function getRoleByName($Name)
    {

        $Entity = $this->Connection->getEntityManager()->getEntity('TblRole')
            ->findOneBy(array(TblRole::ATTR_NAME => $Name));
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblRole[]
     */
    public function getRoleAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->Connection->getEntityManager(), 'TblRole');
    }

    /**
     *
     * @param TblRole $tblRole
     *
     * @return bool|TblRoleLevel[]
     */
    public function getLevelAllByRole(TblRole $tblRole)
    {

        $EntityList = $this->getCachedEntityListBy(__METHOD__, $this->Connection->getEntityManager(), 'TblRoleLevel',
            array(
                TblRoleLevel::ATTR_TBL_ROLE => $tblRole->getId()
            )
        );
        if ($EntityList) {
            array_walk($EntityList, function (TblRoleLevel &$V) {

                $V = $V->getTblLevel();
            });
        }
        return $EntityList;
    }
}
