<?php
namespace SPHERE\Application\Contact\Address\Service;

use SPHERE\Application\Contact\Address\Service\Entity\TblAddress;
use SPHERE\Application\Contact\Address\Service\Entity\TblCity;
use SPHERE\Application\Contact\Address\Service\Entity\TblState;
use SPHERE\Application\Contact\Address\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Address\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Address\Service\Entity\TblType;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\IApiInterface;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Cache\Type\Memory;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\Contact\Address\Service
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

        $this->createType( 'Hauptadresse' );
        $this->createType( 'Rechnungsadresse' );
        $this->createType( 'Lieferadresse' );

        $this->createState( 'Baden-Württemberg' );
        $this->createState( 'Bremen' );
        $this->createState( 'Niedersachsen' );
        $this->createState( 'Sachsen' );
        $this->createState( 'Bayern' );
        $this->createState( 'Hamburg' );
        $this->createState( 'Nordrhein-Westfalen' );
        $this->createState( 'Sachsen-Anhalt' );
        $this->createState( 'Berlin' );
        $this->createState( 'Hessen' );
        $this->createState( 'Rheinland-Pfalz' );
        $this->createState( 'Schleswig-Holstein' );
        $this->createState( 'Brandenburg' );
        $this->createState( 'Mecklenburg-Vorpommern' );
        $this->createState( 'Saarland' );
        $this->createState( 'Thüringen' );
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblType
     */
    public function createType( $Name, $Description = '' )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblType' )->findOneBy( array(
            TblType::ATTR_NAME        => $Name,
            TblType::ATTR_DESCRIPTION => $Description
        ) );
        if (null === $Entity) {
            $Entity = new TblType();
            $Entity->setName( $Name );
            $Entity->setDescription( $Description );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param $Name
     *
     * @return TblState
     */
    public function createState( $Name )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblState' )->findOneBy( array(
            TblState::ATTR_NAME => $Name,
        ) );
        if (null === $Entity) {
            $Entity = new TblState( $Name );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblState
     */
    public function getStateById( $Id )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__.'::'.$Id ) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblState', $Id );
            $Cache->setValue( __METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500 );
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCity
     */
    public function getCityById( $Id )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__.'::'.$Id ) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblCity', $Id );
            $Cache->setValue( __METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500 );
        }
        return ( null === $Entity ? false : $Entity );
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
     * @param integer $Id
     *
     * @return bool|TblAddress
     */
    public function getAddressById( $Id )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__.'::'.$Id ) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblAddress', $Id );
            $Cache->setValue( __METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500 );
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblCity[]
     */
    public function getCityAll()
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $EntityList = $Cache->getValue( __METHOD__ ) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblCity' )->findAll();
            $Cache->setValue( __METHOD__, ( null === $EntityList ? false : $EntityList ), 500 );
        }
        return ( empty ( $EntityList ) ? false : $EntityList );
    }

    /**
     * @return bool|TblState[]
     */
    public function getStateAll()
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $EntityList = $Cache->getValue( __METHOD__ ) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblState' )->findAll();
            $Cache->setValue( __METHOD__, ( null === $EntityList ? false : $EntityList ), 500 );
        }
        return ( empty ( $EntityList ) ? false : $EntityList );
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
     * @return bool|TblAddress[]
     */
    public function getAddressAll()
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $EntityList = $Cache->getValue( __METHOD__ ) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblAddress' )->findAll();
            $Cache->setValue( __METHOD__, ( null === $EntityList ? false : $EntityList ), 500 );
        }
        return ( empty ( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string $Code
     * @param string $Name
     * @param string $District
     *
     * @return TblCity
     */
    public function createCity( $Code, $Name, $District )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblCity' )->findOneBy( array(
            TblCity::ATTR_CODE => $Code,
            TblCity::ATTR_NAME => $Name
        ) );
        if (null === $Entity) {
            $Entity = new TblCity();
            $Entity->setCode( $Code );
            $Entity->setName( $Name );
            $Entity->setDistrict( $District );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblState $tblState
     * @param TblCity  $tblCity
     * @param string   $StreetName
     * @param string   $StreetNumber
     * @param string   $PostOfficeBox
     *
     * @return TblAddress
     */
    public function createAddress( TblState $tblState, TblCity $tblCity, $StreetName, $StreetNumber, $PostOfficeBox )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblAddress' )
            ->findOneBy( array(
                TblAddress::ATTR_TBL_STATE       => $tblState->getId(),
                TblAddress::ATTR_TBL_CITY        => $tblCity->getId(),
                TblAddress::ATTR_STREET_NAME     => $StreetName,
                TblAddress::ATTR_STREET_NUMBER   => $StreetNumber,
                TblAddress::ATTR_POST_OFFICE_BOX => $PostOfficeBox
            ) );
        if (null === $Entity) {
            $Entity = new TblAddress();
            $Entity->setStreetName( $StreetName );
            $Entity->setStreetNumber( $StreetNumber );
            $Entity->setPostOfficeBox( $PostOfficeBox );
            $Entity->setTblState( $tblState );
            $Entity->setTblCity( $tblCity );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblPerson  $tblPerson
     * @param TblAddress $tblAddress
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToPerson
     */
    public function addAddressToPerson( TblPerson $tblPerson, TblAddress $tblAddress, TblType $tblType, $Remark )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblToPerson' )
            ->findOneBy( array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId(),
                TblToPerson::ATT_TBL_ADDRESS    => $tblAddress->getId(),
                TblToPerson::ATT_TBL_TYPE       => $tblType->getId(),
            ) );
        if (null === $Entity) {
            $Entity = new TblToPerson();
            $Entity->setServiceTblPerson( $tblPerson );
            $Entity->setTblAddress( $tblAddress );
            $Entity->setTblType( $tblType );
            $Entity->setRemark( $Remark );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblCompany $tblCompany
     * @param TblAddress $tblAddress
     * @param TblType    $tblType
     * @param string     $Remark
     *
     * @return TblToCompany
     */
    public function addAddressToCompany( TblCompany $tblCompany, TblAddress $tblAddress, TblType $tblType, $Remark )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblToCompany' )
            ->findOneBy( array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId(),
                TblToCompany::ATT_TBL_ADDRESS     => $tblAddress->getId(),
                TblToCompany::ATT_TBL_TYPE        => $tblType->getId(),
            ) );
        if (null === $Entity) {
            $Entity = new TblToCompany();
            $Entity->setServiceTblCompany( $tblCompany );
            $Entity->setTblAddress( $tblAddress );
            $Entity->setTblType( $tblType );
            $Entity->setRemark( $Remark );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblToPerson[]
     */
    public function getAddressAllByPerson( TblPerson $tblPerson )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memory() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__ ) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblToPerson' )->findBy( array(
                TblToPerson::SERVICE_TBL_PERSON => $tblPerson->getId()
            ) );
            $Cache->setValue( __METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300 );
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblCompany $tblCompany
     *
     * @return bool|TblToCompany[]
     */
    public function getAddressAllByCompany( TblCompany $tblCompany )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memory() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__ ) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblToCompany' )->findBy( array(
                TblToCompany::SERVICE_TBL_COMPANY => $tblCompany->getId()
            ) );
            $Cache->setValue( __METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300 );
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
