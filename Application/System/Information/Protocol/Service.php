<?php
namespace SPHERE\Application\System\Information\Protocol;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Information\Protocol\Service\Data;
use SPHERE\Application\System\Information\Protocol\Service\Entity\TblProtocol;
use SPHERE\Application\System\Information\Protocol\Service\Setup;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Information\Protocol
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

        return ( new Setup( $this->Structure ) )->setupDatabaseSchema( $Simulate );
    }

    /**
     * @return bool|TblProtocol[]
     */
    public function getProtocolAll()
    {

        return ( new Data( $this->Binding ) )->getProtocolAll();
    }
}
