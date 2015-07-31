<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilegeRight;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRight;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Setup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Extension\Extension;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Access
 */
class Service extends Extension implements IServiceInterface
{

    /** @var array $AuthorizationRequest */
    private static $AuthorizationRequest = array();
    /** @var array $AuthorizationCache */
    private static $AuthorizationCache = array();
    /** @var TblRole[] $RoleByIdCache */
    private static $RoleByIdCache = array();
    /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel[] $LevelByIdCache */
    private static $LevelByIdCache = array();
    /** @var TblPrivilege[] $PrivilegeByIdCache */
    private static $PrivilegeByIdCache = array();
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
     * @param string $Route
     *
     * @return bool
     */
    public function hasAuthorization( $Route )
    {

        // Sanitize Route
        $Route = '/'.trim( $Route, '/' );

        // Cache
        $this->hydrateAuthorization();
        if (in_array( $Route, self::$AuthorizationCache ) || in_array( $Route, self::$AuthorizationRequest )) {
            return true;
        }
        if (false === ( $tblRight = $this->getRightByName( $Route ) )) {
            // Access valid PUBLIC -> Access granted
            self::$AuthorizationRequest[] = $Route;
            return true;
        } else {
            // MUST BE protected -> Access denied
            return false;
        }
    }

    private function hydrateAuthorization()
    {

        if (empty( self::$AuthorizationCache )) {
            if (false !== ( $tblAccount = Account::useService()->getAccountById( 2 ) )) {
                if (false !== ( $tblAuthorizationAll = Account::useService()->getAuthorizationAllByAccount( $tblAccount ) )) {
                    /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization $tblAuthorization */
                    foreach ($tblAuthorizationAll as $tblAuthorization) {
                        $tblRole = $tblAuthorization->getServiceTblRole();
                        $tblLevelAll = $tblRole->getTblLevelAll();
                        /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel */
                        foreach ($tblLevelAll as $tblLevel) {
                            $tblPrivilegeAll = $tblLevel->getTblPrivilegeAll();
                            /** @var TblPrivilege $tblPrivilege */
                            foreach ($tblPrivilegeAll as $tblPrivilege) {
                                $tblRightAll = $tblPrivilege->getTblRightAll();
                                /** @var TblRight $tblRight */
                                foreach ($tblRightAll as $tblRight) {
                                    if (!in_array( $tblRight->getRoute(), self::$AuthorizationCache )) {
                                        array_push( self::$AuthorizationCache, $tblRight->getRoute() );
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
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
            return new Redirect( '/Platform/Gatekeeper/Authorization/Access/Right', 0 );
        }
        return $Form;
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
            return new Redirect( '/Platform/Gatekeeper/Authorization/Access/Privilege', 0 );
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

        if (array_key_exists( $Id, self::$PrivilegeByIdCache )) {
            return self::$PrivilegeByIdCache[$Id];
        }
        self::$PrivilegeByIdCache[$Id] = ( new Data( $this->Binding ) )->getPrivilegeById( $Id );
        return self::$PrivilegeByIdCache[$Id];
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
            return new Redirect( '/Platform/Gatekeeper/Authorization/Access/Level', 0 );
        }
        return $Form;
    }

    /**
     * @param integer $Id
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel
     */
    public function getLevelById( $Id )
    {

        if (array_key_exists( $Id, self::$LevelByIdCache )) {
            return self::$LevelByIdCache[$Id];
        }
        self::$LevelByIdCache[$Id] = ( new Data( $this->Binding ) )->getLevelById( $Id );
        return self::$LevelByIdCache[$Id];
    }

    /**
     * @param string $Name
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel
     */
    public function getLevelByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getLevelByName( $Name );
    }

    /**
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel[]
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
            return new Redirect( '/Platform/Gatekeeper/Authorization/Access/Role', 0 );
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

        if (array_key_exists( $Id, self::$RoleByIdCache )) {
            return self::$RoleByIdCache[$Id];
        }
        self::$RoleByIdCache[$Id] = ( new Data( $this->Binding ) )->getRoleById( $Id );
        return self::$RoleByIdCache[$Id];
    }

    /**
     * @param string $Name
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole
     */
    public function getRoleByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getRoleByName( $Name );
    }

    /**
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole[]
     */
    public function getRoleAll()
    {

        return ( new Data( $this->Binding ) )->getRoleAll();
    }

    /**
     *
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole $tblRole
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel[]
     */
    public function getLevelAllByRole( TblRole $tblRole )
    {

        return ( new Data( $this->Binding ) )->getLevelAllByRole( $tblRole );
    }

    /**
     *
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege $tblPrivilege
     *
     * @return bool|TblRight[]
     */
    public function getRightAllByPrivilege( TblPrivilege $tblPrivilege )
    {

        return ( new Data( $this->Binding ) )->getRightAllByPrivilege( $tblPrivilege );
    }

    /**
     *
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege[]
     */
    public function getPrivilegeAllByLevel( TblLevel $tblLevel )
    {

        return ( new Data( $this->Binding ) )->getPrivilegeAllByLevel( $tblLevel );
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole  $tblRole
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRoleLevel
     */
    public function addRoleLevel( TblRole $tblRole, TblLevel $tblLevel )
    {

        return ( new Data( $this->Binding ) )->addRoleLevel( $tblRole, $tblLevel );
    }

    /**
     * @param TblRole                                                                              $tblRole
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevel $tblLevel
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRoleLevel
     */
    public function removeRoleLevel( TblRole $tblRole, TblLevel $tblLevel )
    {

        return ( new Data( $this->Binding ) )->removeRoleLevel( $tblRole, $tblLevel );
    }

    /**
     * @param TblLevel     $tblLevel
     * @param TblPrivilege $tblPrivilege
     *
     * @return bool
     */
    public function removeLevelPrivilege( TblLevel $tblLevel, TblPrivilege $tblPrivilege )
    {

        return ( new Data( $this->Binding ) )->removeLevelPrivilege( $tblLevel, $tblPrivilege );
    }

    /**
     * @param TblPrivilege $tblPrivilege
     * @param TblRight     $tblRight
     *
     * @return bool
     */
    public function removePrivilegeRight( TblPrivilege $tblPrivilege, TblRight $tblRight )
    {

        return ( new Data( $this->Binding ) )->removePrivilegeRight( $tblPrivilege, $tblRight );
    }

    /**
     * @param TblPrivilege $tblPrivilege
     * @param TblRight     $tblRight
     *
     * @return TblPrivilegeRight
     */
    public function addPrivilegeRight( TblPrivilege $tblPrivilege, TblRight $tblRight )
    {

        return ( new Data( $this->Binding ) )->addPrivilegeRight( $tblPrivilege, $tblRight );
    }

    /**
     * @param TblLevel     $tblLevel
     * @param TblPrivilege $tblPrivilege
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblLevelPrivilege
     */
    public function addLevelPrivilege( TblLevel $tblLevel, TblPrivilege $tblPrivilege )
    {

        return ( new Data( $this->Binding ) )->addLevelPrivilege( $tblLevel, $tblPrivilege );
    }
}
