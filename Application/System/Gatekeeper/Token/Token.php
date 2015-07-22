<?php
namespace SPHERE\Application\System\Gatekeeper\Token;

use SPHERE\Application\IModuleInterface;
use SPHERE\Application\System\Gatekeeper\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\System\Gatekeeper\Token\Service\Entity\TblToken;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Token\YubiKey\BadOTPException;
use SPHERE\System\Token\YubiKey\ComponentException;
use SPHERE\System\Token\YubiKey\ReplayedOTPException;

/**
 * Class Token
 *
 * @package SPHERE\Application\System\Gatekeeper\Token
 */
class Token implements IModuleInterface
{

    public static function registerModule()
    {
        // TODO: Implement registerModule() method.
    }

    public static function useFrontend()
    {
        // TODO: Implement useFrontend() method.
    }

    /**
     * @param IFormInterface $Form
     * @param string         $CredentialKey
     * @param TblConsumer    $tblConsumer
     *
     * @return bool|TblToken
     */
    public function createToken( IFormInterface $Form, $CredentialKey, TblConsumer $tblConsumer = null )
    {

        try {
            if (null !== $CredentialKey && !empty( $CredentialKey )) {
                $this->useService()->isTokenValid( $CredentialKey );
                if (false === $this->useService()->getTokenByIdentifier( substr( $CredentialKey, 0, 12 ) )) {
                    if ($this->useService()->createToken( substr( $CredentialKey, 0, 12 ), $tblConsumer )) {
                        $Form->setSuccess( 'CredentialKey',
                            'Der YubiKey wurde hinzugefügt'.new Redirect( '/Sphere/Management/Token', 5 )
                        );
                    }
                } else {
                    $Form->setError( 'CredentialKey', 'Der von Ihnen angegebene YubiKey wurde bereits registriert' );
                }
            } elseif (null !== $CredentialKey && empty( $CredentialKey )) {
                $Form->setError( 'CredentialKey', 'Bitte verwenden Sie Ihren YubiKey um dieses Feld zu befüllen' );
            }
            return $Form;
        } catch( BadOTPException $E ) {
            $Form->setError( 'CredentialKey',
                'Der von Ihnen angegebene YubiKey ist nicht gültig<br/>Bitte verwenden Sie einen YubiKey um dieses Feld zu befüllen'
            );
            return $Form;
        } catch( ReplayedOTPException $E ) {
            $Form->setError( 'CredentialKey',
                'Der von Ihnen angegebene YubiKey wurde bereits verwendet<br/>Bitte verwenden Sie einen YubiKey um dieses Feld neu zu befüllen'
            );
            return $Form;
        } catch( ComponentException $E ) {
            $Form->setError( 'CredentialKey',
                'Der YubiKey konnte nicht überprüft werden<br/>Bitte versuchen Sie es später noch einmal'
            );
            return $Form;
        }
    }

    /**
     * @return Service
     */
    public static function useService()
    {

        return new Service( new Identifier( 'System', 'Gatekeeper', 'Token' ),
            __DIR__.'/Service/Entity', __NAMESPACE__.'\Service\Entity'
        );
    }


}
