<?php
namespace SPHERE\Application\System\Gatekeeper\Token;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Gatekeeper\Consumer\Service\Entity\TblConsumer;
use SPHERE\Application\System\Gatekeeper\Token\Service\Data;
use SPHERE\Application\System\Gatekeeper\Token\Service\Entity\TblToken;
use SPHERE\Application\System\Gatekeeper\Token\Service\Setup;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;
use SPHERE\System\Token\Token;
use SPHERE\System\Token\Type\YubiKey;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Gatekeeper\Token
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
     * @param TblConsumer $tblConsumer
     *
     * @return bool|TblToken[]
     */
    public function getTokenAllByConsumer( TblConsumer $tblConsumer )
    {

        return ( new Data( $this->Binding ) )->getTokenAllByConsumer( $tblConsumer );
    }

    /**
     * @param integer $Id
     *
     * @return bool|TblToken
     */
    public function getTokenById( $Id )
    {

        return ( new Data( $this->Binding ) )->getTokenById( $Id );
    }

    /**
     * @return TblToken[]|bool
     */
    public function getTokenAll()
    {

        return ( new Data( $this->Binding ) )->getTokenAll();
    }

    /**
     * @param string $Identifier
     *
     * @return bool|TblToken
     */
    public function getTokenByIdentifier( $Identifier )
    {

        return ( new Data( $this->Binding ) )->getTokenByIdentifier( $Identifier );
    }

    /**
     * @param string      $Identifier
     * @param TblConsumer $tblConsumer
     *
     * @return TblToken
     */
    public function createToken( $Identifier, TblConsumer $tblConsumer = null )
    {

        return ( new Data( $this->Binding ) )->createToken( $Identifier, $tblConsumer );
    }

    /**
     * @param TblToken $tblToken
     */
    public function destroyToken( TblToken $tblToken )
    {

        ( new Data( $this->Binding ) )->destroyToken( $tblToken );
    }

    /**
     * @param string $Value
     *
     * @return bool
     * @throws \Exception
     */
    public function isTokenValid( $Value )
    {

        /** @var YubiKey $YubiKey */
        $YubiKey = ( new Token( new YubiKey() ) )->getToken();
        $Key = $YubiKey->parseKey( $Value );
        return $YubiKey->verifyKey( $Key );
    }
}
