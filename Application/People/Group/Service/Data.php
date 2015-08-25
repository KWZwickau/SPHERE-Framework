<?php
namespace SPHERE\Application\People\Group\Service;

use SPHERE\Application\People\Group\Service\Entity\TblGroup;
use SPHERE\Application\People\Group\Service\Entity\TblMember;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Group\Service
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

        $this->createGroup( 'Allgemein', 'Personendaten', '', true, 'Common' );
        $this->createGroup( 'Interessenten', 'Schüler die zur Aufnahme vorgemerkt sind', '', true, 'Prospect' );
        $this->createGroup( 'Schüler', 'Alle im System verfügbaren Schüler', '', true, 'Student' );
        $this->createGroup( 'Sorgeberechtigte', '', '', true, 'Custody' );
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
    public function countPersonAllByGroup( TblGroup $tblGroup )
    {

        return $this->Connection->getEntityManager()->getEntity( 'TblMember' )->countBy( array(
            TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
        ) );
    }

    /**
     *
     * @param TblGroup $tblGroup
     *
     * @return bool|TblPerson[]
     */
    public function getPersonAllByGroup( TblGroup $tblGroup )
    {

        /** @var TblMember[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblMember' )->findBy( array(
            TblMember::ATTR_TBL_GROUP => $tblGroup->getId()
        ) );
        array_walk( $EntityList, function ( TblMember &$V ) {

            $V = $V->getServiceTblPerson();
        } );
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblGroup[]
     */
    public function getGroupAllByPerson( TblPerson $tblPerson )
    {

        /** @var TblMember[] $EntityList */
        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblMember' )->findBy( array(
            TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
        ) );
        array_walk( $EntityList, function ( TblMember &$V ) {

            $V = $V->getTblGroup();
        } );
        return ( null === $EntityList ? false : $EntityList );
    }

    /**
     * @param TblGroup  $tblGroup
     * @param TblPerson $tblPerson
     *
     * @return TblMember
     */
    public function addGroupPerson( TblGroup $tblGroup, TblPerson $tblPerson )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblMember' )
            ->findOneBy( array(
                TblMember::ATTR_TBL_GROUP     => $tblGroup->getId(),
                TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
            ) );
        if (null === $Entity) {
            $Entity = new TblMember();
            $Entity->setTblGroup( $tblGroup );
            $Entity->setServiceTblPerson( $tblPerson );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblGroup  $tblGroup
     * @param TblPerson $tblPerson
     *
     * @return bool
     */
    public function removeGroupPerson( TblGroup $tblGroup, TblPerson $tblPerson )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblMember $Entity */
        $Entity = $Manager->getEntity( 'TblMember' )
            ->findOneBy( array(
                TblMember::ATTR_TBL_GROUP     => $tblGroup->getId(),
                TblMember::SERVICE_TBL_PERSON => $tblPerson->getId()
            ) );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }
}
