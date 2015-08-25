<?php
namespace SPHERE\Application\Contact\Phone\Service;

use SPHERE\Application\Contact\Phone\Service\Entity\TblPhone;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToCompany;
use SPHERE\Application\Contact\Phone\Service\Entity\TblToPerson;
use SPHERE\Application\Contact\Phone\Service\Entity\TblType;
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
 * @package SPHERE\Application\Contact\Phone\Service
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

        $this->createType( 'Privat', 'Festnetz' );
        $this->createType( 'Privat', 'Mobil' );
        $this->createType( 'Geschäftlich', 'Festnetz' );
        $this->createType( 'Geschäftlich', 'Mobil' );
        $this->createType( 'Notfall', 'Festnetz' );
        $this->createType( 'Notfall', 'Mobil' );
        $this->createType( 'Fax', 'Privat' );
        $this->createType( 'Fax', 'Geschäftlich' );
    }

    /**
     * @param $Name
     * @param $Description
     *
     * @return TblType
     */
    public function createType(
        $Name,
        $Description = ''
    ) {

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
     * @return bool|TblPhone
     */
    public function getPhoneById( $Id )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__.'::'.$Id ) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblPhone', $Id );
            $Cache->setValue( __METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500 );
        }
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param $Number
     *
     * @return TblPhone
     */
    public function createPhone( $Number )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblPhone' )->findOneBy( array(
            TblPhone::ATTR_NUMBER => $Number
        ) );
        if (null === $Entity) {
            $Entity = new TblPhone();
            $Entity->setNumber( $Number );
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
    public function getPhoneAllByPerson( TblPerson $tblPerson )
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
    public function getPhoneAllByCompany( TblCompany $tblCompany )
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
