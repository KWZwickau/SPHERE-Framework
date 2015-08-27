<?php
namespace SPHERE\Application\Corporation\Group\Service;

use SPHERE\Application\Corporation\Group\Service\Entity\TblGroup;
use SPHERE\Application\Corporation\Group\Service\Entity\TblMember;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\Corporation\Group\Service
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
     * @param string $Name
     * @param string $Description
     * @param string $Remark
     * @param bool   $IsLocked
     * @param string $MetaTable
     *
     * @return TblGroup
     */
    public function createGroup( $Name, $Description, $Remark, $IsLocked = false, $MetaTable = '' )
    {

        $Manager = $this->Connection->getEntityManager();

        if ($IsLocked) {
            $Entity = $Manager->getEntity( 'TblGroup' )->findOneBy( array(
                TblGroup::ATTR_IS_LOCKED  => $IsLocked,
                TblGroup::ATTR_META_TABLE => $MetaTable
            ) );
        } else {
            $Entity = $Manager->getEntity( 'TblGroup' )->findOneBy( array(
                TblGroup::ATTR_NAME => $Name
            ) );
        }

        if (null === $Entity) {
            $Entity = new TblGroup( $Name );
            $Entity->setDescription( $Description );
            $Entity->setRemark( $Remark );
            $Entity->setIsLocked( $IsLocked );
            $Entity->setMetaTable( $MetaTable );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }

        return $Entity;
    }

    /**
     * @param TblGroup $tblGroup
     * @param string   $Name
     * @param string   $Description
     * @param string   $Remark
     *
     * @return bool
     */
    public function updateGroup( TblGroup $tblGroup, $Name, $Description, $Remark )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var null|TblGroup $Entity */
        $Entity = $Manager->getEntityById( 'TblGroup', $tblGroup->getId() );
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName( $Name );
            $Entity->setDescription( $Description );
            $Entity->setRemark( $Remark );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createUpdateEntry( $this->Connection->getDatabase(), $Protocol, $Entity );
            return true;
        }
        return false;
    }

    /**
     * @return TblGroup[]|bool
     */
    public function getGroupAll()
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblGroup' )->findAll();
        return ( empty( $Entity ) ? false : $Entity );
    }

    /**
     * @param int $Id
     *
     * @return bool|TblGroup
     */
    public function getGroupById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblGroup', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblGroup
     */
    public function getGroupByName( $Name )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblGroup' )
            ->findOneBy( array( TblGroup::ATTR_NAME => $Name ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return int
     */
    public function countCompanyAllByGroup( TblGroup $tblGroup )
    {

        return $this->Connection->getEntityManager()->getEntity( 'TblMember' )->countBy( array(
            TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
        ) );
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return bool|TblCompany[]
     */
    public function getCompanyAllByGroup( TblGroup $tblGroup )
    {

        /** @var TblMember[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblMember' )->findBy( array(
            TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
        ) );
        array_walk( $EntityList, function ( TblMember &$V ) {

            $V = $V->getServiceTblCompany();
        } );
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     *
     * @param TblCompany $tblCompany
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllByCompany( TblCompany $tblCompany )
    {

        /** @var TblMember[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblMember' )->findBy( array(
            TblMember::SERVICE_TBL_COMPANY => $tblCompany->getId()
        ) );
        array_walk( $EntityList, function ( TblMember &$V ) {

            $V = $V->getTblGroup();
        } );
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblGroup   $tblGroup
     * @param TblCompany $tblCompany
     *
     * @return TblMember
     */
    public function addGroupCompany( TblGroup $tblGroup, TblCompany $tblCompany )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblMember' )
            ->findOneBy( array(
                TblMember::ATTR_TBL_GROUP     => $tblGroup->getId(),
                TblMember::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ) );
        if (null === $Entity) {
            $Entity = new TblMember();
            $Entity->setTblGroup( $tblGroup );
            $Entity->setServiceTblCompany( $tblCompany );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblGroup   $tblGroup
     * @param TblCompany $tblCompany
     *
     * @return bool
     */
    public function removeGroupCompany( TblGroup $tblGroup, TblCompany $tblCompany )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblMember $Entity */
        $Entity = $Manager->getEntity( 'TblMember' )
            ->findOneBy( array(
                TblMember::ATTR_TBL_GROUP     => $tblGroup->getId(),
                TblMember::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param TblGroup $tblGroup
     *
     * @return bool
     */
    public function destroyGroup( TblGroup $tblGroup )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblMember $Entity */
        $Entity = $Manager->getEntityById( 'TblGroup', $tblGroup->getId() );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }
}
