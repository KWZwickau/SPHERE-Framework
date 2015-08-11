<?php
namespace SPHERE\Application\People\Person\Service;

use SPHERE\Application\People\Person\Service\Entity\TblSalutation;
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
