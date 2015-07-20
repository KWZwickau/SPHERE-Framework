<?php
namespace SPHERE\Application\System\Gatekeeper\Consumer\Service;

use SPHERE\Application\System\Gatekeeper\Consumer\Service\Entity\TblConsumer;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class DataBinding
 *
 * @package SPHERE\Application\System\Gatekeeper\Consumer\Service
 */
class DataBinding
{

    /** @var null|\SPHERE\System\Database\Fitting\Binding $Binding */
    private $Binding = null;

    /**
     *
     */
    function __construct()
    {

        $this->Binding = new Binding(
            new Identifier( 'System', 'Gatekeeper', 'Consumer' ), __DIR__.'/Entity', __NAMESPACE__.'\Entity'
        );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblConsumer
     */
    protected function getConsumerByName( $Name )
    {

        $Entity = $this->Binding->getEntityManager()->getEntity( 'TblConsumer' )
            ->findOneBy( array( TblConsumer::ATTR_NAME => $Name ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Acronym
     *
     * @return bool|TblConsumer
     */
    protected function getConsumerByAcronym( $Acronym )
    {

        $Entity = $this->Binding->getEntityManager()->getEntity( 'TblConsumer' )
            ->findOneBy( array( TblConsumer::ATTR_ACRONYM => $Acronym ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param string $Suffix
     * @param string $Name
     *
     * @return TblConsumer
     */
    protected function createConsumer( $Suffix, $Name )
    {

        $Manager = $this->Binding->getEntityManager();
        $Entity = $Manager->getEntity( 'TblConsumer' )
            ->findOneBy( array( TblConsumer::ATTR_ACRONYM => $Suffix ) );
        if (null === $Entity) {
            $Entity = new TblConsumer( $Suffix );
            $Entity->setName( $Name );
            $Manager->saveEntity( $Entity );
            System::serviceProtocol()->executeCreateInsertEntry( $this->Binding->getDatabase(),
                $Entity );
        }
        return $Entity;
    }

    /**
     * @return tblConsumer[]|bool
     */
    protected function getConsumerAll()
    {

        $EntityList = $this->Binding->getEntityManager()->getEntity( 'TblConsumer' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param null|string $Session
     *
     * @return bool|TblConsumer
     */
    protected function getConsumerBySession( $Session = null )
    {

        if (false !== ( $tblAccount = Gatekeeper::serviceAccount()->entityAccountBySession( $Session ) )) {
            return $this->getConsumerById( $tblAccount->getConsumer() );
        } else {
            return false;
        }
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblConsumer
     */
    protected function getConsumerById( $Id )
    {

        $Entity = $this->Binding->getEntityManager()->getEntityById( 'TblConsumer', $Id );
        return ( null === $Entity ? false : $Entity );
    }
}
