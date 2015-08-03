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
    function __construct( Binding $Connection )
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

        /**
         * Administrator
         */
        $tblRole = $this->createRole( 'Administrator' );

        // Mandanten
        $tblLevel = $this->createLevel( 'Mandanten' );
        $this->addRoleLevel( $tblRole, $tblLevel );

        $tblPrivilege = $this->createPrivilege( 'Mandanten' );
        $this->addLevelPrivilege( $tblLevel, $tblPrivilege );
        $tblRight = $this->createRight( '/Platform' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization/Consumer' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );

        // Hardware-Token
        $tblLevel = $this->createLevel( 'Hardware-Token' );
        $this->addRoleLevel( $tblRole, $tblLevel );

        $tblPrivilege = $this->createPrivilege( 'Hardware-Token' );
        $this->addLevelPrivilege( $tblLevel, $tblPrivilege );
        $tblRight = $this->createRight( '/Platform' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization/Token' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );

        // Benutzerkonten
        $tblLevel = $this->createLevel( 'Benutzerkonten' );
        $this->addRoleLevel( $tblRole, $tblLevel );

        $tblPrivilege = $this->createPrivilege( 'Benutzerkonten' );
        $this->addLevelPrivilege( $tblLevel, $tblPrivilege );
        $tblRight = $this->createRight( '/Platform' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization/Account' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );

        // Rechteverwaltung
        $tblLevel = $this->createLevel( 'Rechteverwaltung' );
        $this->addRoleLevel( $tblRole, $tblLevel );

        $tblPrivilege = $this->createPrivilege( 'Rechteverwaltung' );
        $this->addLevelPrivilege( $tblLevel, $tblPrivilege );
        $tblRight = $this->createRight( '/Platform' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization/Access' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization/Access/Role' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization/Access/Level' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization/Access/Privilege' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/Gatekeeper/Authorization/Access/Right' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );

        // Plattform
        $tblLevel = $this->createLevel( 'Plattform' );
        $this->addRoleLevel( $tblRole, $tblLevel );

        $tblPrivilege = $this->createPrivilege( 'System' );
        $this->addLevelPrivilege( $tblLevel, $tblPrivilege );
        $tblRight = $this->createRight( '/Platform' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/System' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/System/Cache' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/System/Database' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/System/Database/Setup/Simulation' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/System/Database/Setup/Execution' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );
        $tblRight = $this->createRight( '/Platform/System/Protocol' );
        $this->addPrivilegeRight( $tblPrivilege, $tblRight );

        /**
         * Schüler / Eltern
         */
        $tblRole = $this->createRole( 'Schüler / Eltern' );

        $tblLevel = $this->createLevel( 'Zensuren' );
        $this->addRoleLevel( $tblRole, $tblLevel );
    }

    /**
     * @param string $Name
     *
     * @return TblRole
     */
    public function createRole( $Name )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblRole' )->findOneBy( array( TblRole::ATTR_NAME => $Name ) );
        if (null === $Entity) {
            $Entity = new TblRole( $Name );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel
     */
    public function createLevel( $Name )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblLevel' )->findOneBy( array( TblLevel::ATTR_NAME => $Name ) );
        if (null === $Entity) {
            $Entity = new TblLevel( $Name );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblRole                                                                              $tblRole
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel
     *
     * @return TblRoleLevel
     */
    public function addRoleLevel( TblRole $tblRole, TblLevel $tblLevel )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblRoleLevel' )
            ->findOneBy( array(
                TblRoleLevel::ATTR_TBL_ROLE  => $tblRole->getId(),
                TblRoleLevel::ATTR_TBL_LEVEL => $tblLevel->getId()
            ) );
        if (null === $Entity) {
            $Entity = new TblRoleLevel();
            $Entity->setTblRole( $tblRole );
            $Entity->setTblLevel( $tblLevel );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param string $Name
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege
     */
    public function createPrivilege( $Name )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblPrivilege' )->findOneBy( array( TblPrivilege::ATTR_NAME => $Name ) );
        if (null === $Entity) {
            $Entity = new TblPrivilege( $Name );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel     $tblLevel
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege $tblPrivilege
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevelPrivilege
     */
    public function addLevelPrivilege( TblLevel $tblLevel, TblPrivilege $tblPrivilege )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblLevelPrivilege' )
            ->findOneBy( array(
                TblLevelPrivilege::ATTR_TBL_LEVEL     => $tblLevel->getId(),
                TblLevelPrivilege::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId()
            ) );
        if (null === $Entity) {
            $Entity = new TblLevelPrivilege();
            $Entity->setTblLevel( $tblLevel );
            $Entity->setTblPrivilege( $tblPrivilege );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param string $Route
     *
     * @return TblRight
     */
    public function createRight( $Route )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblRight' )->findOneBy( array( TblRight::ATTR_ROUTE => $Route ) );
        if (null === $Entity) {
            $Entity = new TblRight( $Route );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege $tblPrivilege
     * @param TblRight                                                                                 $tblRight
     *
     * @return TblPrivilegeRight
     */
    public function addPrivilegeRight( TblPrivilege $tblPrivilege, TblRight $tblRight )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblPrivilegeRight' )
            ->findOneBy( array(
                TblPrivilegeRight::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId(),
                TblPrivilegeRight::ATTR_TBL_RIGHT     => $tblRight->getId()
            ) );
        if (null === $Entity) {
            $Entity = new TblPrivilegeRight();
            $Entity->setTblPrivilege( $tblPrivilege );
            $Entity->setTblRight( $tblRight );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblRole                                                                              $tblRole
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel
     *
     * @return bool
     */
    public function removeRoleLevel( TblRole $tblRole, TblLevel $tblLevel )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblRoleLevel $Entity */
        $Entity = $Manager->getEntity( 'TblRoleLevel' )
            ->findOneBy( array(
                TblRoleLevel::ATTR_TBL_ROLE  => $tblRole->getId(),
                TblRoleLevel::ATTR_TBL_LEVEL => $tblLevel->getId()
            ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege $tblPrivilege
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRight     $tblRight
     *
     * @return bool
     */
    public function removePrivilegeRight( TblPrivilege $tblPrivilege, TblRight $tblRight )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilegeRight $Entity */
        $Entity = $Manager->getEntity( 'TblPrivilegeRight' )
            ->findOneBy( array(
                TblPrivilegeRight::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId(),
                TblPrivilegeRight::ATTR_TBL_RIGHT     => $tblRight->getId()
            ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel     $tblLevel
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege $tblPrivilege
     *
     * @return bool
     */
    public function removeLevelPrivilege( TblLevel $tblLevel, TblPrivilege $tblPrivilege )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblLevelPrivilege $Entity */
        $Entity = $Manager->getEntity( 'TblLevelPrivilege' )
            ->findOneBy( array(
                TblLevelPrivilege::ATTR_TBL_LEVEL     => $tblLevel->getId(),
                TblLevelPrivilege::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId()
            ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblRight
     */
    public function getRightById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblRight', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblRight
     */
    public function getRightByName( $Name )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblRight' )
            ->findOneBy( array( TblRight::ATTR_ROUTE => $Name ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblRight[]
     */
    public function getRightAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblRight' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel
     */
    public function getLevelById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblLevel', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel
     */
    public function getLevelByName( $Name )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblLevel' )
            ->findOneBy( array( TblLevel::ATTR_NAME => $Name ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege
     */
    public function getPrivilegeById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblPrivilege', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege
     */
    public function getPrivilegeByName( $Name )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblPrivilege' )
            ->findOneBy( array( TblPrivilege::ATTR_NAME => $Name ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     *
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel
     *
     * @return bool|TblLevelPrivilege[]
     */
    public function getPrivilegeAllByLevel( TblLevel $tblLevel )
    {

        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevelPrivilege[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblLevelPrivilege' )->findBy( array(
            TblLevelPrivilege::ATTR_TBL_LEVEL => $tblLevel->getId()
        ) );
        array_walk( $EntityList, function ( TblLevelPrivilege &$V ) {

            $V = $V->getTblPrivilege();
        } );
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     *
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege $tblPrivilege
     *
     * @return bool|TblRight[]
     */
    public function getRightAllByPrivilege( TblPrivilege $tblPrivilege )
    {

        /** @var TblPrivilegeRight[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblPrivilegeRight' )->findBy( array(
            TblPrivilegeRight::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId()
        ) );
        array_walk( $EntityList, function ( TblPrivilegeRight &$V ) {

            $V = $V->getTblRight();
        } );
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege[]
     */
    public function getPrivilegeAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblPrivilege' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel[]
     */
    public function getLevelAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblLevel' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblRole
     */
    public function getRoleById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblRole', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblRole
     */
    public function getRoleByName( $Name )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblRole' )
            ->findOneBy( array( TblRole::ATTR_NAME => $Name ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblRole[]
     */
    public function getRoleAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblRole' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     *
     * @param TblRole $tblRole
     *
     * @return bool|TblRoleLevel[]
     */
    public function getLevelAllByRole( TblRole $tblRole )
    {

        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRoleLevel[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblRoleLevel' )->findBy( array(
            TblRoleLevel::ATTR_TBL_ROLE => $tblRole->getId()
        ) );
        array_walk( $EntityList, function ( TblRoleLevel &$V ) {

            $V = $V->getTblLevel();
        } );
        return ( null === $EntityList ? false : $EntityList );
    }
}
