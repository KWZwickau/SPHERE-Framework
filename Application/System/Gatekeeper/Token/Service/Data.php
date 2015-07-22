<?php
namespace SPHERE\Application\System\Gatekeeper\Token\Service;

use SPHERE\Application\System\Gatekeeper\Consumer\Consumer;
use SPHERE\Application\System\Gatekeeper\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\System\Gatekeeper\Token\Service\Entity\TblToken;
use SPHERE\Application\System\Information\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Extension\Extension;

/**
 * Class Data
 *
 * @package SPHERE\Application\System\Gatekeeper\Token\Service
 */
class Data extends Extension
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

        $this->createToken( 'ccccccdilkui', Consumer::useService()->getConsumerByAcronym( 'DEMO' ) );
    }

    /**
     * @param string      $Identifier
     * @param TblConsumer $tblConsumer
     *
     * @return TblToken
     */
    public function createToken( $Identifier, TblConsumer $tblConsumer = null )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblToken' )->findOneBy( array( TblToken::ATTR_IDENTIFIER => $Identifier ) );
        if (null === $Entity) {
            $Entity = new TblToken( $Identifier );
            $Entity->setSerial( $this->getModHex( $Identifier )->getSerialNumber() );
            $Entity->setServiceTblConsumer( $tblConsumer );
            $Manager->saveEntity( $Entity );
            Protocol::useService()->createInsertEntry( $this->Connection->getDatabase(), $Entity );
        }
        return $Entity;
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblToken
     */
    public function getTokenByIdentifier( $Identifier )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblToken' )
            ->findOneBy( array( TblToken::ATTR_IDENTIFIER => $Identifier ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return TblToken[]|bool
     */
    public function getTokenAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblToken' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToken
     */
    public function getTokenById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblToken', $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|TblToken[]
     */
    public function getTokenAllByConsumer( TblConsumer $tblConsumer )
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblToken' )->findBy( array(
            TblToken::SERVICE_TBL_CONSUMER => $tblConsumer->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblToken $tblToken
     */
    public function destroyToken( TblToken $tblToken )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntityById( 'TblToken', $tblToken->getId() );
        if (null !== $Entity) {
            $Manager->killEntity( $Entity );
            Protocol::useService()->createDeleteEntry( $this->Connection->getDatabase(), $Entity );
        }
    }
}
