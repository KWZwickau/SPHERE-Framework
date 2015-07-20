<?php
namespace SPHERE\Application\System\Gatekeeper\Token\Service;

use SPHERE\Application\System\Gatekeeper\Token\Service\Entity\TblToken;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class DataBinding
 *
 * @package SPHERE\Application\System\Gatekeeper\Token\Service
 */
class DataBinding extends Extension
{

    /** @var null|Binding $Binding */
    private $Binding = null;

    /**
     *
     */
    function __construct()
    {

        $this->Binding = new Binding(
            new Identifier( 'System', 'Gatekeeper', 'Token' ), __DIR__.'/Entity', __NAMESPACE__.'\Entity'
        );
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblToken
     */
    protected function entityTokenByIdentifier( $Identifier )
    {

        $Entity = $this->Binding->getEntityManager()->getEntity( 'TblToken' )
            ->findOneBy( array( TblToken::ATTR_IDENTIFIER => $Identifier ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return TblToken[]|bool
     */
    protected function entityTokenAll()
    {

        $EntityList = $this->Binding->getEntityManager()->getEntity( 'TblToken' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToken
     */
    protected function entityTokenById( $Id )
    {

        $Entity = $this->Binding->getEntityManager()->getEntityById( 'TblToken',
            $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|Entity\TblToken[]
     */
    protected function entityTokenAllByConsumer( TblConsumer $tblConsumer )
    {

        $EntityList = $this->Binding->getEntityManager()->getEntity( 'TblToken' )->findBy( array(
            TblToken::ATTR_SERVICE_GATEKEEPER_CONSUMER => $tblConsumer->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param string      $Identifier
     * @param TblConsumer $tblConsumer
     *
     * @return TblToken
     */
    protected function actionCreateToken( $Identifier, TblConsumer $tblConsumer = null )
    {

        $Manager = $this->Binding->getEntityManager();
        $Entity = $Manager->getEntity( 'TblToken' )
            ->findOneBy( array( TblToken::ATTR_IDENTIFIER => $Identifier ) );
        if (null === $Entity) {
            $Entity = new TblToken( $Identifier );
            $Entity->setSerial( $this->getModHex( $Identifier )->getSerialNumber() );
            $Entity->setServiceGatekeeperConsumer( $tblConsumer );
            $Manager->saveEntity( $Entity );
            System::serviceProtocol()->executeCreateInsertEntry( $this->Binding->getDatabase(),
                $Entity );
        }
        return $Entity;
    }

    /**
     * @param TblToken $tblToken
     */
    protected function actionDestroyToken( TblToken $tblToken )
    {

        $Manager = $this->Binding->getEntityManager();
        $Entity = $Manager->getEntityById( 'TblToken', $tblToken->getId() );
        if (null !== $Entity) {
            $Manager->killEntity( $Entity );
            System::serviceProtocol()->executeCreateDeleteEntry( $this->Binding->getDatabase(),
                $Entity );
        }
    }
}
