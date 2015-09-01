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
use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\IApiInterface;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Cache\Type\Memory;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service
 */
class Data
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
        $tblRole = $this->createRole('Administrator');

        // Mandanten
        $tblLevel = $this->createLevel('Mandanten');
        $this->addRoleLevel($tblRole, $tblLevel);

        $tblPrivilege = $this->createPrivilege('Mandanten');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Consumer');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);

        // Hardware-Token
        $tblLevel = $this->createLevel('Hardware-Token');
        $this->addRoleLevel($tblRole, $tblLevel);

        $tblPrivilege = $this->createPrivilege('Hardware-Token');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Token');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);

        // Benutzerkonten
        $tblLevel = $this->createLevel('Benutzerkonten');
        $this->addRoleLevel($tblRole, $tblLevel);

        $tblPrivilege = $this->createPrivilege('Benutzerkonten');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization/Account');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);

        // Rechteverwaltung
        $tblLevel = $this->createLevel('Rechteverwaltung');
        $this->addRoleLevel($tblRole, $tblLevel);

        $tblPrivilege = $this->createPrivilege('Rechteverwaltung');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/Gatekeeper/Authorization');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
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

        // Plattform
        $tblLevel = $this->createLevel('Plattform');
        $this->addRoleLevel($tblRole, $tblLevel);

        $tblPrivilege = $this->createPrivilege('System');
        $this->addLevelPrivilege($tblLevel, $tblPrivilege);
        $tblRight = $this->createRight('/Platform');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Cache');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Database');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Database/Setup/Simulation');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Database/Setup/Execution');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);
        $tblRight = $this->createRight('/Platform/System/Protocol');
        $this->addPrivilegeRight($tblPrivilege, $tblRight);

        /**
         * Schüler / Eltern
         */
        $tblRole = $this->createRole('Schüler / Eltern');

        $tblLevel = $this->createLevel('Zensuren');
        $this->addRoleLevel($tblRole, $tblLevel);
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

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $Entity = $Cache->getValue(__METHOD__.'::'.$Id) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById('TblRight', $Id);
            $Cache->setValue(__METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500);
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblRight
     */
    public function getRightByName($Name)
    {

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memory()))->getCache();
        if (!( $Entity = $Cache->getValue(__METHOD__.'::'.$Name) )) {
            $Entity = $this->Connection->getEntityManager()->getEntity('TblRight')
                ->findOneBy(array(TblRight::ATTR_ROUTE => $Name));
            $Cache->setValue(__METHOD__.'::'.$Name, ( null === $Entity ? false : $Entity ), 300);
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblRight[]
     */
    public function getRightAll()
    {

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $EntityList = $Cache->getValue(__METHOD__) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity('TblRight')->findAll();
            $Cache->setValue(__METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblLevel
     */
    public function getLevelById($Id)
    {

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $Entity = $Cache->getValue(__METHOD__.'::'.$Id) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById('TblLevel', $Id);
            $Cache->setValue(__METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 300);
        }
        return ( null === $Entity ? false : $Entity );
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

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $Entity = $Cache->getValue(__METHOD__.'::'.$Id) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById('TblPrivilege', $Id);
            $Cache->setValue(__METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 300);
        }
        return ( null === $Entity ? false : $Entity );
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

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $EntityList = $Cache->getValue(__METHOD__) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity('TblPrivilege')->findAll();
            $Cache->setValue(__METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @return bool|TblLevel[]
     */
    public function getLevelAll()
    {

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $EntityList = $Cache->getValue(__METHOD__) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity('TblLevel')->findAll();
            $Cache->setValue(__METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblRole
     */
    public function getRoleById($Id)
    {

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $Entity = $Cache->getValue(__METHOD__.'::'.$Id) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById('TblRole', $Id);
            $Cache->setValue(__METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 300);
        }
        return ( null === $Entity ? false : $Entity );
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

        /** @var IApiInterface $Cache */
        $Cache = (new Cache(new Memcached()))->getCache();
        if (!( $EntityList = $Cache->getValue(__METHOD__) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity('TblRole')->findAll();
            $Cache->setValue(__METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300);
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     *
     * @param TblRole $tblRole
     *
     * @return bool|TblRoleLevel[]
     */
    public function getLevelAllByRole(TblRole $tblRole)
    {

        /** @var TblRoleLevel[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity('TblRoleLevel')->findBy(array(
            TblRoleLevel::ATTR_TBL_ROLE => $tblRole->getId()
        ));
        array_walk($EntityList, function (TblRoleLevel &$V) {

            $V = $V->getTblLevel();
        });
        return ( null === $EntityList ? false : $EntityList );
    }
}
