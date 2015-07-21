<?php
namespace SPHERE\Application\System\Gatekeeper\Token\Service;

use SPHERE\Application\System\Gatekeeper\Token\Service\Entity\TblToken;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Extension\Extension;

/**
 * Class Data
 *
 * @package SPHERE\Application\System\Gatekeeper\Token\Service
 */
class Data extends Extension
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct( Binding $Connection )
    {

        $this->Connection = $Connection;
    }

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
     * @param string      $Identifier
     * @param TblConsumer $tblConsumer
     *
     * @return TblToken
     */
    protected function actionCreateToken( $Identifier, TblConsumer $tblConsumer = null )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntity( 'TblToken' )
            ->findOneBy( array( TblToken::ATTR_IDENTIFIER => $Identifier ) );
        if (null === $Entity) {
            $Entity = new TblToken( $Identifier );
            $Entity->setSerial( $this->getModHex( $Identifier )->getSerialNumber() );
            $Entity->setServiceGatekeeperConsumer( $tblConsumer );
            $Manager->saveEntity( $Entity );
            System::serviceProtocol()->executeCreateInsertEntry( $this->Connection->getDatabase(),
                $Entity );
        }
        return $Entity;
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

    /**
     * @param string $Identifier
     *
     * @return bool|TblToken
     */
    protected function entityTokenByIdentifier( $Identifier )
    {

        $Entity = $this->Connection->getEntityManager()->getEntity( 'TblToken' )
            ->findOneBy( array( TblToken::ATTR_IDENTIFIER => $Identifier ) );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return TblToken[]|bool
     */
    protected function entityTokenAll()
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblToken' )->findAll();
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToken
     */
    protected function entityTokenById( $Id )
    {

        $Entity = $this->Connection->getEntityManager()->getEntityById( 'TblToken',
            $Id );
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @param TblConsumer $tblConsumer
     *
     * @return bool|Entity\TblToken[]
     */
    protected function entityTokenAllByConsumer( TblConsumer $tblConsumer )
    {

        $EntityList = $this->Connection->getEntityManager()->getEntity( 'TblToken' )->findBy( array(
            TblToken::ATTR_SERVICE_GATEKEEPER_CONSUMER => $tblConsumer->getId()
        ) );
        return ( empty( $EntityList ) ? false : $EntityList );
    }

    /**
     * @param TblToken $tblToken
     */
    protected function actionDestroyToken( TblToken $tblToken )
    {

        $Manager = $this->Connection->getEntityManager();
        $Entity = $Manager->getEntityById( 'TblToken', $tblToken->getId() );
        if (null !== $Entity) {
            $Manager->killEntity( $Entity );
            System::serviceProtocol()->executeCreateDeleteEntry( $this->Connection->getDatabase(),
                $Entity );
        }
    }
}
