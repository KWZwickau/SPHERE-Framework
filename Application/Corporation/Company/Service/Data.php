<?php
namespace SPHERE\Application\Corporation\Company\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\IApiInterface;
use SPHERE\System\Cache\Type\Memcached;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\Corporation\Company\Service
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
     *
     * @return TblCompany
     */
    public function createCompany( $Name )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = new TblCompany();
        $Entity->setName( $Name );
        $Manager->saveEntity( $Entity );
        Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        return $Entity;
    }

    /**
     * @param TblCompany $tblCompany
     * @param string     $Name
     *
     * @return TblCompany
     */
    public function updateCompany(
        TblCompany $tblCompany,
        $Name
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var TblCompany $Entity */
        $Entity = $Manager->getEntityById( 'TblCompany', $tblCompany->getId() );
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setName( $Name );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createUpdateEntry( $this->Connection->getDatabase(), $Protocol, $Entity );
            return true;
        }
        return false;
    }

    /**
     * @return bool|TblCompany[]
     */
    public function getCompanyAll()
    {

        /** @var IApiInterface $Cache */
        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblCompany' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblCompany
     */
    public function getCompanyById( $Id )
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memcached() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__.'::'.$Id ) )) {
            $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblCompany', $Id );
            $Cache->setValue( __METHOD__.'::'.$Id, ( null === $Entity ? false : $Entity ), 500 );
        }
        return ( null === $Entity ? false : $Entity );
    }
}
