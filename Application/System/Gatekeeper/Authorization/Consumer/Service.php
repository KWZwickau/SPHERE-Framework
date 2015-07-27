<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Consumer;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Gatekeeper\Authorization\Consumer\Service\Data;
use SPHERE\Application\System\Gatekeeper\Authorization\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\System\Gatekeeper\Authorization\Consumer\Service\Setup;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Consumer
 */
class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct( Identifier $Identifier, $EntityPath, $EntityNamespace )
    {

        $this->Binding = new Binding( $Identifier, $EntityPath, $EntityNamespace );
        $this->Structure = new Structure( $Identifier );
    }

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupService( $Simulate )
    {

        $Protocol = ( new Setup( $this->Structure ) )->setupDatabaseSchema( $Simulate );
        if (!$Simulate) {
            ( new Data( $this->Binding ) )->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblConsumer
     */
    public function getConsumerById( $Id )
    {

        return ( new Data( $this->Binding ) )->getConsumerById( $Id );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getConsumerByName( $Name );
    }

    /**
     * @param null|string $Session
     *
     * @return bool|TblConsumer
     */
    public function getConsumerBySession( $Session = null )
    {

        return ( new Data( $this->Binding ) )->getConsumerBySession( $Session );
    }

    /**
     * @return bool|TblConsumer[]
     */
    public function getConsumerAll()
    {

        return ( new Data( $this->Binding ) )->getConsumerAll();
    }

    /**
     * @param string $Acronym
     *
     * @return bool|TblConsumer
     */
    public function getConsumerByAcronym( $Acronym )
    {

        return ( new Data( $this->Binding ) )->getConsumerByAcronym( $Acronym );
    }

    /**
     * @param string $Acronym
     * @param string $Name
     *
     * @return TblConsumer
     */
    public function createConsumer( $Acronym, $Name )
    {

        return ( new Data( $this->Binding ) )->createConsumer( $Acronym, $Name );
    }
}
