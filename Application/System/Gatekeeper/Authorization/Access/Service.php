<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Access;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Data;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblLevel;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblRight;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Setup;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access
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
     * @return bool|TblRight[]
     */
    public function getRightAll()
    {

        return ( new Data( $this->Binding ) )->getRightAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|string    $Name
     *
     * @return IFormInterface|Redirect
     */
    public function createRight( IFormInterface $Form, $Name )
    {

        if (null !== $Name && empty( $Name )) {
            $Form->setError( 'Name', 'Bitte geben Sie einen Namen ein' );
        }
        if (!empty( $Name )) {
            $Form->setSuccess( 'Name', 'Das Recht wurde hinzugefügt' );
            ( new Data( $this->Binding ) )->createRight( $Name );
            return new Redirect( '/System/Gatekeeper/Authorization/Access/Right', 0 );
        }
        return $Form;
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
     * @param IFormInterface $Form
     * @param null|string    $Name
     *
     * @return IFormInterface|Redirect
     */
    public function createPrivilege( IFormInterface $Form, $Name )
    {

        if (null !== $Name && empty( $Name )) {
            $Form->setError( 'Name', 'Bitte geben Sie einen Namen ein' );
        }
        if (!empty( $Name )) {
            $Form->setSuccess( 'Name', 'Das Privileg wurde hinzugefügt' );
            ( new Data( $this->Binding ) )->createPrivilege( $Name );
            return new Redirect( '/System/Gatekeeper/Authorization/Access/Privilege', 0 );
        }
        return $Form;
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
     * @return bool|TblPrivilege[]
     */
    public function getPrivilegeAll()
    {

        return ( new Data( $this->Binding ) )->getPrivilegeAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|string    $Name
     *
     * @return IFormInterface|Redirect
     */
    public function createLevel( IFormInterface $Form, $Name )
    {

        if (null !== $Name && empty( $Name )) {
            $Form->setError( 'Name', 'Bitte geben Sie einen Namen ein' );
        }
        if (!empty( $Name )) {
            $Form->setSuccess( 'Name', 'Das Zugriffslevel wurde hinzugefügt' );
            ( new Data( $this->Binding ) )->createLevel( $Name );
            return new Redirect( '/System/Gatekeeper/Authorization/Access/Level', 0 );
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblLevel
     */
    public function getLevelById( $Id )
    {

        return ( new Data( $this->Binding ) )->getLevelById( $Id );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblLevel
     */
    public function getLevelByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getLevelByName( $Name );
    }

    /**
     * @return bool|TblLevel[]
     */
    public function getLevelAll()
    {

        return ( new Data( $this->Binding ) )->getLevelAll();
    }

    /**
     * @param IFormInterface $Form
     * @param null|string    $Name
     *
     * @return IFormInterface|Redirect
     */
    public function createRole( IFormInterface $Form, $Name )
    {

        if (null !== $Name && empty( $Name )) {
            $Form->setError( 'Name', 'Bitte geben Sie einen Namen ein' );
        }
        if (!empty( $Name )) {
            $Form->setSuccess( 'Name', 'Die Rolle wurde hinzugefügt' );
            ( new Data( $this->Binding ) )->createRole( $Name );
            return new Redirect( '/System/Gatekeeper/Authorization/Access/Role', 0 );
        }
        return $Form;
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

    /**
     * @return bool|TblRole[]
     */
    public function getRoleAll()
    {

        return ( new Data( $this->Binding ) )->getRoleAll();
    }
}
