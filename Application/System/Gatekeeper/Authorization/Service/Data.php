<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Service;

use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblAccess;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblAccessPrivilege;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblPrivilege;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblPrivilegeRight;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblRight;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblRole;
use SPHERE\Application\System\Information\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Service
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
     * @param string $Name
     *
     * @return TblPrivilege
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
     * @param string $Name
     *
     * @return TblAccess
     */
    public function createAccess( $Name )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblAccess' )->findOneBy( array( TblAccess::ATTR_NAME => $Name ) );
        if (null === $Entity) {
            $Entity = new TblAccess( $Name );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
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
     * @param TblPrivilege $TblPrivilege
     * @param TblRight     $TblRight
     *
     * @return TblPrivilegeRight
     */
    public function addPrivilegeRight( TblPrivilege $TblPrivilege, TblRight $TblRight )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblPrivilegeRight' )
            ->findOneBy( array(
                TblPrivilegeRight::ATTR_TBL_PRIVILEGE => $TblPrivilege->getId(),
                TblPrivilegeRight::ATTR_TBL_RIGHT     => $TblRight->getId()
            ) );
        if (null === $Entity) {
            $Entity = new TblPrivilegeRight();
            $Entity->setTblPrivilege( $TblPrivilege );
            $Entity->setTblRight( $TblRight );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblPrivilege $TblPrivilege
     * @param TblRight     $TblRight
     *
     * @return bool
     */
    public function removePrivilegeRight( TblPrivilege $TblPrivilege, TblRight $TblRight )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblPrivilegeRight $Entity */
        $Entity = $Manager->getEntity( 'TblPrivilegeRight' )
            ->findOneBy( array(
                TblPrivilegeRight::ATTR_TBL_PRIVILEGE => $TblPrivilege->getId(),
                TblPrivilegeRight::ATTR_TBL_RIGHT     => $TblRight->getId()
            ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param TblAccess    $tblAccess
     * @param TblPrivilege $tblPrivilege
     *
     * @return TblAccessPrivilege
     */
    public function addAccessPrivilege( TblAccess $tblAccess, TblPrivilege $tblPrivilege )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblAccessPrivilege' )
            ->findOneBy( array(
                TblAccessPrivilege::ATTR_TBL_ACCESS    => $tblAccess->getId(),
                TblAccessPrivilege::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId()
            ) );
        if (null === $Entity) {
            $Entity = new TblAccessPrivilege();
            $Entity->setTblAccess( $tblAccess );
            $Entity->setTblPrivilege( $tblPrivilege );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblAccess    $tblAccess
     * @param TblPrivilege $tblPrivilege
     *
     * @return bool
     */
    public function removeAccessPrivilege( TblAccess $tblAccess, TblPrivilege $tblPrivilege )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblAccessPrivilege $Entity */
        $Entity = $Manager->getEntity( 'TblAccessPrivilege' )
            ->findOneBy( array(
                TblAccessPrivilege::ATTR_TBL_ACCESS    => $tblAccess->getId(),
                TblAccessPrivilege::ATTR_TBL_PRIVILEGE => $tblPrivilege->getId()
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
     * @return bool|TblAccess
     */
    public function getAccessById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblAccess', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblAccess
     */
    public function getAccessByName( $Name )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblAccess' )
            ->findOneBy( array( TblAccess::ATTR_NAME => $Name ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPrivilege
     */
    public function getPrivilegeById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblPrivilege', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPrivilege
     */
    public function getPrivilegeByName( $Name )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblPrivilege' )
            ->findOneBy( array( TblPrivilege::ATTR_NAME => $Name ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     *
     * @param TblAccess $tblAccess
     *
     * @return bool|TblAccessPrivilege[]
     */
    public function getPrivilegeAllByAccess( TblAccess $tblAccess )
    {

        /** @var TblAccessPrivilege[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblAccessPrivilege' )->findBy( array(
            TblAccessPrivilege::ATTR_TBL_ACCESS => $tblAccess->getId()
        ) );
        array_walk( $EntityList, function ( TblAccessPrivilege &$V ) {

            $V = $V->getTblPrivilege();
        } );
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     *
     * @param TblPrivilege $tblPrivilege
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
     * @return bool|TblPrivilege[]
     */
    public function getPrivilegeAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblPrivilege' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @return bool|TblAccess[]
     */
    public function getAccessAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblAccess' )->findAll();
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
}
