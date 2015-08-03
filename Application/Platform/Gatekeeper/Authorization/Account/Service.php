<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Account;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service\Entity\TblRole;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Data;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Setup;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Service\Entity\TblToken;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Token\Token;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Authorization\Account
 */
class Service implements IServiceInterface
{

    /** @var TblAccount[] $AccountByIdCache */
    private static $AccountByIdCache = array();
    /** @var \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification[] $IdentificationByIdCache */
    private static $IdentificationByIdCache = array();
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
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService( $doSimulation, $withData )
    {

        $Protocol = ( new Setup( $this->Structure ) )->setupDatabaseSchema( $doSimulation );
        if (!$doSimulation && $withData ) {
            ( new Data( $this->Binding ) )->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param null|string $Session
     *
     * @return bool|TblAccount
     */
    public function getAccountBySession( $Session = null )
    {

        return ( new Data( $this->Binding ) )->getAccountBySession( $Session );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblAccount
     */
    public function getAccountById( $Id )
    {

        if (array_key_exists( $Id, self::$AccountByIdCache )) {
            return self::$AccountByIdCache[$Id];
        }
        self::$AccountByIdCache[$Id] = ( new Data( $this->Binding ) )->getAccountById( $Id );
        return self::$AccountByIdCache[$Id];
    }

    /**
     * @param integer $Id
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification
     */
    public function getIdentificationById( $Id )
    {

        if (array_key_exists( $Id, self::$IdentificationByIdCache )) {
            return self::$IdentificationByIdCache[$Id];
        }
        self::$IdentificationByIdCache[$Id] = ( new Data( $this->Binding ) )->getIdentificationById( $Id );
        return self::$IdentificationByIdCache[$Id];
    }

    /**
     * @param string $Name
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification
     */
    public function getIdentificationByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getIdentificationByName( $Name );
    }

    /**
     * @return TblIdentification[]|bool
     */
    public function getIdentificationAll()
    {

        return ( new Data( $this->Binding ) )->getIdentificationAll();
    }

    /**
     * @param Redirect    $Redirect
     * @param null|string $Session
     *
     * @return bool
     */
    public function destroySession( Redirect $Redirect, $Session = null )
    {

        ( new Data( $this->Binding ) )->destroySession( $Session );
        if (!headers_sent()) {
            // Destroy Cookie
            $params = session_get_cookie_params();
            setcookie( session_name(), '', 0, $params['path'], $params['domain'], $params['secure'],
                isset( $params['httponly'] ) );
            session_start();
            // Generate New Id
            session_regenerate_id( true );
        }
        return $Redirect;
    }

    /**
     * @param IFormInterface                                                                                 $Form
     * @param string                                                                                         $CredentialName
     * @param string                                                                                         $CredentialLock
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification $tblIdentification
     *
     * @return IFormInterface|Redirect
     */
    public function createSessionCredential(
        IFormInterface &$Form,
        $CredentialName,
        $CredentialLock,
        TblIdentification $tblIdentification
    ) {

        switch ($this->isCredentialValid( $CredentialName, $CredentialLock, false, $tblIdentification )) {
            case false: {
                if (null !== $CredentialName && empty( $CredentialName )) {
                    $Form->setError( 'CredentialName', 'Bitte geben Sie einen gültigen Benutzernamen ein' );
                }
                if (null !== $CredentialName && !empty( $CredentialName )) {
                    $Form->setError( 'CredentialName', 'Bitte geben Sie einen gültigen Benutzernamen ein' );
                }
                if (null !== $CredentialLock && empty( $CredentialLock )) {
                    $Form->setError( 'CredentialLock', 'Bitte geben Sie ein gültiges Passwort ein' );
                }
                if (null !== $CredentialLock && !empty( $CredentialLock )) {
                    $Form->setError( 'CredentialLock', 'Bitte geben Sie ein gültiges Passwort ein' );
                }
                break;
            }
            case true: {
                return new Redirect( '/', 0 );
                break;
            }
        }
        return $Form;
    }

    /**
     * @param string                                                                                         $Username
     * @param string                                                                                         $Password
     * @param bool                                                                                           $TokenString
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification $tblIdentification
     *
     * @return null|bool
     */
    private function isCredentialValid( $Username, $Password, $TokenString, TblIdentification $tblIdentification )
    {

        if (false === ( $tblAccount = $this->getAccountByCredential( $Username, $Password, $tblIdentification ) )) {
            return false;
        } else {
            if (false === $TokenString) {
                session_regenerate_id();
                $this->createSession( $tblAccount, session_id() );
                return true;
            } else {
                try {
                    if (Token::useService()->isTokenValid( $TokenString )) {
                        if (false === ( $Token = $tblAccount->getServiceTblToken() )) {
                            return null;
                        } else {
                            if ($Token->getIdentifier() == substr( $TokenString, 0, 12 )) {
                                session_regenerate_id();
                                $this->createSession( $tblAccount, session_id() );
                                return true;
                            } else {
                                return null;
                            }
                        }
                    } else {
                        return null;
                    }
                } catch( \Exception $E ) {
                    return null;
                }
            }
        }
    }

    /**
     * @param string                                                                                         $Username
     * @param string                                                                                         $Password
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification $tblIdentification
     *
     * @return bool|TblAccount
     */
    public function getAccountByCredential( $Username, $Password, TblIdentification $tblIdentification = null )
    {

        return ( new Data( $this->Binding ) )->getAccountByCredential( $Username, $Password, $tblIdentification );
    }

    /**
     * @param TblAccount  $tblAccount
     * @param null|string $Session
     * @param integer     $Timeout
     *
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblSession
     */
    private function createSession( TblAccount $tblAccount, $Session = null, $Timeout = 1800 )
    {

        return ( new Data( $this->Binding ) )->createSession( $tblAccount, $Session, $Timeout );
    }

    /**
     * @param IFormInterface                                                                                 $Form
     * @param string                                                                                         $CredentialName
     * @param string                                                                                         $CredentialLock
     * @param string                                                                                         $CredentialKey
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblIdentification $tblIdentification
     *
     * @return IFormInterface|Redirect
     */
    public function createSessionCredentialToken(
        IFormInterface &$Form,
        $CredentialName,
        $CredentialLock,
        $CredentialKey,
        TblIdentification $tblIdentification
    ) {

        switch ($this->isCredentialValid( $CredentialName, $CredentialLock, $CredentialKey, $tblIdentification )) {
            case false: {
                if (null !== $CredentialName && empty( $CredentialName )) {
                    $Form->setError( 'CredentialName', 'Bitte geben Sie einen gültigen Benutzernamen ein' );
                }
                if (null !== $CredentialName && !empty( $CredentialName )) {
                    $Form->setError( 'CredentialName', 'Bitte geben Sie einen gültigen Benutzernamen ein' );
                }
                if (null !== $CredentialLock && empty( $CredentialLock )) {
                    $Form->setError( 'CredentialLock', 'Bitte geben Sie ein gültiges Passwort ein' );
                }
                if (null !== $CredentialLock && !empty( $CredentialLock )) {
                    $Form->setError( 'CredentialLock', 'Bitte geben Sie ein gültiges Passwort ein' );
                }
                break;
            }
            case null: {
                $Form->setSuccess( 'CredentialName', '' );
                $Form->setSuccess( 'CredentialLock', '' );
                $Form->setError( 'CredentialKey', 'Der von Ihnen angegebene YubiKey ist nicht gültig.'
                    .'<br/>Bitte verwenden Sie Ihren YubiKey um dieses Feld zu befüllen' );
                break;
            }
            case true: {
                return new Redirect( '/', 0 );
                break;
            }
        }
        return $Form;
    }

    /**
     * @return \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount[]|bool
     */
    public function getAccountAll()
    {

        return ( new Data( $this->Binding ) )->getAccountAll();
    }

    /**
     * @param TblToken $tblToken
     *
     * @return bool|TblAccount[]
     */
    public function getAccountAllByToken( TblToken $tblToken )
    {

        return ( new Data( $this->Binding ) )->getAccountAllByToken( $tblToken );
    }

    /**
     * @param \SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount $tblAccount
     * @param TblRole                                                                                 $tblRole
     *
     * @return bool
     */
    public function hasAuthorization( TblAccount $tblAccount, TblRole $tblRole )
    {

        $tblAuthorization = $this->getAuthorizationAllByAccount( $tblAccount );
        /** @noinspection PhpUnusedParameterInspection */
        array_walk( $tblAuthorization, function ( TblAuthorization &$tblAuthorization, $Index, TblRole $tblRole ) {

            if ($tblAuthorization->getServiceTblRole()->getId() != $tblRole->getId()) {
                $tblAuthorization = false;
            }
        }, $tblRole );
        $tblAuthorization = array_filter( $tblAuthorization );
        if (!empty( $tblAuthorization )) {
            return true;
        }
        return false;
    }

    /**
     * @param TblAccount $tblAccount
     *
     * @return bool|\SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAuthorization[]
     */
    public function getAuthorizationAllByAccount( TblAccount $tblAccount )
    {

        return ( new Data( $this->Binding ) )->getAuthorizationAllByAccount( $tblAccount );
    }
}
