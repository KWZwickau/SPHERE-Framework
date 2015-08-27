<?php
namespace SPHERE\Application\People\Relationship\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblToCompany;
use SPHERE\Application\People\Relationship\Service\Entity\TblToPerson;
use SPHERE\Application\People\Relationship\Service\Entity\TblType;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\IApiInterface;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Cache\Type\Memory;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Relationship\Service
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

        $this->createType( 'Sorgeberechtigt' );
        $this->createType( 'Vormund' );
        $this->createType( 'Bevollmächtigt' );
        $this->createType( 'Geschwisterkind' );
        $this->createType( 'Arzt' );
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool   $IsLocked
     *
     * @return TblType
     */
    public function createType( $Name, $Description = '', $IsLocked = false )
    {

        $Manager = $this->Connection->getEntityManager();
        if ($IsLocked) {
            $Entity = $Manager->getEntity( 'TblType' )->findOneBy( array(
                TblType::ATTR_NAME      => $Name,
                TblType::ATTR_IS_LOCKED => $IsLocked
            ) );
        } else {
            $Entity = $Manager->getEntity( 'TblType' )->findOneBy( array(
                TblType::ATTR_NAME => $Name
            ) );
        }

        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName( $Name );
            $Entity->setDescription( $Description );
            $Entity->setIsLocked( $IsLocked );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblType
     */
    public function getTypeById( $Id )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__.'::'.$Id ) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblType', $Id );
            $Cache->setValue( __METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500 );
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblType[]
     */
    public function getTypeAll()
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $EntityList = $Cache->getValue( __METHOD__ ) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblType' )->findAll();
            $Cache->setValue( __METHOD__, ( null === $EntityList ? false : $EntityList ), 500 );
        }
        return ( empty ( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToPerson
     */
    public function getRelationshipToPersonById( $Id )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__.'::'.$Id ) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblToPerson', $Id );
            $Cache->setValue( __METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500 );
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToCompany
     */
    public function getRelationshipToCompanyById( $Id )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__.'::'.$Id ) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblToCompany', $Id );
            $Cache->setValue( __METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500 );
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getPersonRelationshipAllByPerson( TblPerson $tblPerson )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memory() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__ ) )) {
            $EntityList = array_merge(
                $this->Connection->getEntityManager()->getEntity( 'TblToPerson' )->findBy( array(
                    TblToPerson::SERVICE_TBL_PERSON_FROM => $tblPerson->getId()
                ) ),
                $this->Connection->getEntityManager()->getEntity( 'TblToPerson' )->findBy( array(
                    TblToPerson::SERVICE_TBL_PERSON_TO => $tblPerson->getId()
                ) )
            );
            $Cache->setValue( __METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300 );
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getCompanyRelationshipAllByPerson( TblPerson $tblPerson )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memory() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__ ) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblToCompany' )->findBy( array(
                TblToCompany::SERVICE_TBL_PERSON => $tblPerson->getId()
            ) );
            $Cache->setValue( __METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300 );
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblPerson $tblPersonFrom
     * @param TblPerson $tblPersonTo
     * @param TblType   $tblType
     * @param string    $Remark
     *
     * @return TblToPerson
     */
    public function addPersonRelationshipToPerson(
        TblPerson $tblPersonFrom,
        TblPerson $tblPersonTo,
        TblType $tblType,
        $Remark
    ) {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblToPerson' )
            ->findOneBy( array(
                TblToPerson::SERVICE_TBL_PERSON_FROM => $tblPersonFrom->getId(),
                TblToPerson::SERVICE_TBL_PERSON_TO   => $tblPersonTo->getId(),
                TblToPerson::ATT_TBL_TYPE            => $tblType->getId(),
            ) );
        if (null === $Entity) {
            $Entity = new TblToPerson();
            $Entity->setServiceTblPersonFrom( $tblPersonFrom );
            $Entity->setServiceTblPersonTo( $tblPersonTo );
            $Entity->setTblType( $tblType );
            $Entity->setRemark( $Remark );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblToPerson $tblToPerson
     *
     * @return bool
     */
    public function removePersonRelationshipToPerson( TblToPerson $tblToPerson )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblToPerson $Entity */
        $Entity = $Manager->getEntityById( 'TblToPerson', $tblToPerson->getId() );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param TblToCompany $tblToCompany
     *
     * @return bool
     */
    public function removeCompanyRelationshipToPerson( TblToCompany $tblToCompany )
    {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblToCompany $Entity */
        $Entity = $Manager->getEntityById( 'TblToCompany', $tblToCompany->getId() );
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
            $Manager->killEntity( $Entity );
            return true;
        }
        return false;
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblPerson  $tblPerson
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToCompany
     */
    public function addCompanyRelationshipToPerson(
        TblCompany $tblCompany,
        TblPerson $tblPerson,
        TblType $tblType,
        $Remark
    ) {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblToCompany' )
            ->findOneBy( array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblToCompany::SERVICE_TBL_PERSON  => $tblPerson->getId(),
                TblToCompany::ATT_TBL_TYPE        => $tblType->getId(),
            ) );
        if (null === $Entity) {
            $Entity = new TblToCompany();
            $Entity->setServiceTblCompany( $tblCompany );
            $Entity->setServiceTblPerson( $tblPerson );
            $Entity->setTblType( $tblType );
            $Entity->setRemark( $Remark );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }
}
