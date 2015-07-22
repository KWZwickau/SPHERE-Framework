<?php
namespace SPHERE\Application\System\Gatekeeper\Account;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Gatekeeper\Account\Service\Data;
use SPHERE\Application\System\Gatekeeper\Account\Service\Entity\TblAccount;
use SPHERE\Application\System\Gatekeeper\Account\Service\Entity\TblIdentification;
use SPHERE\Application\System\Gatekeeper\Account\Service\Entity\TblSession;
use SPHERE\Application\System\Gatekeeper\Account\Service\Setup;
use SPHERE\Application\System\Gatekeeper\Token\Token;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Account
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

        return ( new Data( $this->Binding ) )->getAccountById( $Id );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblIdentification
     */
    public function getIdentificationById( $Id )
    {

        return ( new Data( $this->Binding ) )->getIdentificationById( $Id );
    }

    /**
     * @param string $Name
     *
     * @return bool|TblIdentification
     */
    public function getIdentificationByName( $Name )
    {

        return ( new Data( $this->Binding ) )->getIdentificationByName( $Name );
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
     * @param IFormInterface    $Form
     * @param string            $CredentialName
     * @param string            $CredentialLock
     * @param TblIdentification $tblIdentification
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
     * @param string            $Username
     * @param string            $Password
     * @param bool              $TokenString
     * @param TblIdentification $tblIdentification
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
     * @param string            $Username
     * @param string            $Password
     * @param TblIdentification $tblIdentification
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
     * @return TblSession
     */
    private function createSession( TblAccount $tblAccount, $Session = null, $Timeout = 1800 )
    {

        return ( new Data( $this->Binding ) )->createSession( $tblAccount, $Session, $Timeout );
    }

    /**
     * @param IFormInterface    $Form
     * @param string            $CredentialName
     * @param string            $CredentialLock
     * @param string            $CredentialKey
     * @param TblIdentification $tblIdentification
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
}
