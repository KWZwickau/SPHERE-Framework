<?php
namespace SPHERE\Application\System\Gatekeeper\Token;

use SPHERE\Application\System\Gatekeeper\Token\Service\DataBinding;
use SPHERE\Application\System\Gatekeeper\Token\Service\Entity\TblToken;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Token\Type\YubiKey;
use SPHERE\System\Token\YubiKey\BadOTPException;
use SPHERE\System\Token\YubiKey\ComponentException;
use SPHERE\System\Token\YubiKey\ReplayedOTPException;

/**
 * Class Token
 *
 * @package SPHERE\Application\System\Gatekeeper\Token
 */
class Token extends DataBinding
{

    public function setupDatabaseContent()
    {

        /**
         * Create SystemAdmin (Token)
         */
        $tblToken = $this->actionCreateToken( 'ccccccdilkui' );
        Gatekeeper::serviceAccount()->executeChangeToken( $tblToken,
            Gatekeeper::serviceAccount()->entityAccountByUsername( 'System' )
        );
    }

    /**
     * @param IFormInterface $View
     * @param string         $CredentialKey
     * @param TblConsumer    $tblConsumer
     *
     * @return bool|TblToken
     */
    public function executeCreateToken( IFormInterface $View, $CredentialKey, TblConsumer $tblConsumer = null )
    {

        try {
            if (null !== $CredentialKey && !empty( $CredentialKey )) {
                $this->checkIsValidToken( $CredentialKey );
                if (false === $this->entityTokenByIdentifier( substr( $CredentialKey, 0, 12 ) )) {
                    if (parent::actionCreateToken( substr( $CredentialKey, 0, 12 ), $tblConsumer )) {
                        $View->setSuccess( 'CredentialKey',
                            'Der YubiKey wurde hinzugefügt'.new Redirect( '/Sphere/Management/Token', 5 )
                        );
                    }
                } else {
                    $View->setError( 'CredentialKey', 'Der von Ihnen angegebene YubiKey wurde bereits registriert' );
                }
            } elseif (null !== $CredentialKey && empty( $CredentialKey )) {
                $View->setError( 'CredentialKey', 'Bitte verwenden Sie Ihren YubiKey um dieses Feld zu befüllen' );
            }
            return $View;
        } catch( BadOTPException $E ) {
            $View->setError( 'CredentialKey',
                'Der von Ihnen angegebene YubiKey ist nicht gültig<br/>Bitte verwenden Sie einen YubiKey um dieses Feld zu befüllen'
            );
            return $View;
        } catch( ReplayedOTPException $E ) {
            $View->setError( 'CredentialKey',
                'Der von Ihnen angegebene YubiKey wurde bereits verwendet<br/>Bitte verwenden Sie einen YubiKey um dieses Feld neu zu befüllen'
            );
            return $View;
        } catch( ComponentException $E ) {
            $View->setError( 'CredentialKey',
                'Der YubiKey konnte nicht überprüft werden<br/>Bitte versuchen Sie es später noch einmal'
            );
            return $View;
        }
    }

    /**
     * @param string $Value
     *
     * @return bool
     * @throws \Exception
     */
    public function checkIsValidToken( $Value )
    {

        /** @var YubiKey $YubiKey */
        $YubiKey = ( new \SPHERE\System\Token\Token( new YubiKey() ) )->getToken();
        $Key = $YubiKey->parseKey( $Value );
        return $YubiKey->verifyKey( $Key );
    }

}
