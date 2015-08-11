<?php
namespace SPHERE\Application\People\Person\Service;

use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Cache\Cache;
use SPHERE\System\Cache\IApiInterface;
use SPHERE\System\Cache\Type\Memory;
use SPHERE\System\Database\Fitting\Binding;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Person\Service
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

        $this->createSalutation( 'Herr', true );
        $this->createSalutation( 'Frau', true );
    }

    /**
     * @param string $Salutation
     * @param bool   $IsLocked
     *
     * @return TblSalutation
     */
    public function createSalutation( $Salutation, $IsLocked = false )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblSalutation' )->findOneBy( array( TblSalutation::ATTR_SALUTATION => $Salutation ) );
        if (null === $Entity) {
            $Entity = new TblSalutation( $Salutation );
            $Entity->setIsLocked( $IsLocked );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @return bool|TblSalutation[]
     */
    public function getSalutationAll()
    {

        /** @var IApiInterface $Cache */
        $Cache = ( new Cache( new Memory() ) )->getCache();
        if (!( $Entity = $Cache->getValue( __METHOD__ ) )) {
            $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblSalutation' )->findAll();
            $Cache->setValue( __METHOD__, ( empty( $EntityList ) ? false : $EntityList ), 300 );
        }
        return ( empty( $EntityList ) ? false : $EntityList );
    }
}
