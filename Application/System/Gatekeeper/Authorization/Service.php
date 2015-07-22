<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Data;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblAccess;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblPrivilege;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblRight;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Entity\TblRole;
use SPHERE\Application\System\Gatekeeper\Authorization\Service\Setup;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization
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
     * @return bool|TblRight
     */
    public function getRightById( $Id )
    {

        return ( new Data( $this->Binding ) )->getRightById( $Id );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblRight
     */
    public function getRightByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getRightByName( $Name );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblPrivilege
     */
    public function getPrivilegeById( $Id )
    {

        return ( new Data( $this->Binding ) )->getPrivilegeById( $Id );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblPrivilege
     */
    public function getPrivilegeByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getPrivilegeByName( $Name );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAccess
     */
    public function getAccessById( $Id )
    {

        return ( new Data( $this->Binding ) )->getAccessById( $Id );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblAccess
     */
    public function getAccessByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getAccessByName( $Name );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblRole
     */
    public function getRoleById( $Id )
    {

        return ( new Data( $this->Binding ) )->getRoleById( $Id );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblRole
     */
    public function getRoleByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getRoleByName( $Name );
    }
}
