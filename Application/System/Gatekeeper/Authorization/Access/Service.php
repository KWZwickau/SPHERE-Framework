<?php
namespace SPHERE\Application\System\Gatekeeper\Authorization\Access;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Data;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblLevel;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblLevelPrivilege;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilege;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblPrivilegeRight;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblRight;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Entity\TblRoleLevel;
use SPHERE\Application\System\Gatekeeper\Authorization\Access\Service\Setup;
use SPHERE\Application\System\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\System\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
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

    /** @var array $Authorization */
    private static $Authorization = array();
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

        // Sanatize Route
        $Route = '/'.trim( $Route, '/' );

        // Cache
        if (in_array( $Route, self::$Authorization )) {
            return true;
        }

        if (false !== ( $tblRight = $this->getRightByName( $Route ) )) {
            if (false !== ( $tblAccount = Account::useService()->getAccountById( 1 ) )) {
                if (false !== ( $tblAuthorizationAll = Account::useService()->getAuthorizationAllByAccount( $tblAccount ) )) {
                    /** @var TblAuthorization $tblAuthorization */
                    foreach ($tblAuthorizationAll as $tblAuthorization) {
                        $tblRole = $tblAuthorization->getServiceTblRole();
                        $tblLevelAll = $tblRole->getTblLevelAll();
                        /** @var TblLevel $tblLevel */
                        foreach ($tblLevelAll as $tblLevel) {
                            $tblPrivilegeAll = $tblLevel->getTblPrivilegeAll();
                            /** @var TblPrivilege $tblPrivilege */
                            foreach ($tblPrivilegeAll as $tblPrivilege) {
                                $tblRightAll = $tblPrivilege->getTblRightAll();
                                /** @noinspection PhpUnusedParameterInspection */
                                array_walk( $tblRightAll,
                                    function ( TblRight &$tblRight, $Index, TblRight $Right ) {

                                        if ($tblRight->getId() == $Right->getId()) {
                                            // Access valid -> Access granted
                                            $tblRight = true;
                                        } else {
                                            // Access not valid -> Access denied
                                            $tblRight = false;
                                        }
                                    }, $tblRight );
                                $tblRightAll = array_filter( $tblRightAll );
                                if (!empty( $tblRightAll )) {
                                    // Access valid -> Access granted
                                    self::$Authorization[] = $Route;
                                    return true;
                                }
                            }
                        }
                    }
                    // Access not valid -> Access denied
                    return false;
                } else {
                    // Authorization invalid -> Access denied
                    return false;
                }
            } else {
                // Session invalid -> Access denied
                return false;
            }
        } else {
            if (!array_key_exists( 'REST', $this->getRequest()->getParameterArray() )) {
                // Resource is not protected -> Access granted
                self::$Authorization[] = $Route;
                return true;
            } else {
                // REST MUST BE protected -> Access denied
                return false;
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
            return new Redirect( '/System/Gatekeeper/Authorization/Access/Right', 0 );
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

    /**
     *
     * @param TblRole $tblRole
     *
     * @return bool|TblLevel[]
     */
    public function getLevelAllByRole( TblRole $tblRole )
    {

        return ( new Data( $this->Binding ) )->getLevelAllByRole( $tblRole );
    }

    /**
     *
     * @param TblPrivilege $tblPrivilege
     *
     * @return bool|TblRight[]
     */
    public function getRightAllByPrivilege( TblPrivilege $tblPrivilege )
    {

        return ( new Data( $this->Binding ) )->getRightAllByPrivilege( $tblPrivilege );
    }

    /**
     *
     * @param TblLevel $tblLevel
     *
     * @return bool|TblPrivilege[]
     */
    public function getPrivilegeAllByLevel( TblLevel $tblLevel )
    {

        return ( new Data( $this->Binding ) )->getPrivilegeAllByLevel( $tblLevel );
    }

    /**
     * @param TblRole  $tblRole
     * @param TblLevel $tblLevel
     *
     * @return TblRoleLevel
     */
    public function addRoleLevel( TblRole $tblRole, TblLevel $tblLevel )
    {

        return ( new Data( $this->Binding ) )->addRoleLevel( $tblRole, $tblLevel );
    }

    /**
     * @param TblRole  $tblRole
     * @param TblLevel $tblLevel
     *
     * @return TblRoleLevel
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
     * @return TblLevelPrivilege
     */
    public function addLevelPrivilege( TblLevel $tblLevel, TblPrivilege $tblPrivilege )
    {

        return ( new Data( $this->Binding ) )->addLevelPrivilege( $tblLevel, $tblPrivilege );
    }
}
