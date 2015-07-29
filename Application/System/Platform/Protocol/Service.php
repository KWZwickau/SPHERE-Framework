<?php
namespace SPHERE\Application\System\Platform\Protocol;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\System\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\System\Platform\Protocol\Service\Data;
use SPHERE\Application\System\Platform\Protocol\Service\Entity\TblProtocol;
use SPHERE\Application\System\Platform\Protocol\Service\Setup;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\System\Platform\Protocol
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

        return ( new Setup( $this->Structure ) )->setupDatabaseSchema( $Simulate );
    }

    /**
     * @return bool|TblProtocol[]
     */
    public function getProtocolAll()
    {

        return ( new Data( $this->Binding ) )->getProtocolAll();
    }

    /**
     * @param string  $DatabaseName
     * @param Element $Entity
     *
     * @return false|TblProtocol
     */
    public function createInsertEntry(
        $DatabaseName,
        Element $Entity
    ) {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblConsumer = $tblAccount->getServiceTblConsumer();
        } else {
            $tblConsumer = null;
        }

        return ( new Data( $this->Binding ) )->createProtocolEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            null,
            $Entity
        );
    }

    /**
     * @param string  $DatabaseName
     * @param Element $From
     * @param Element $To
     *
     * @return false|TblProtocol
     */
    public function createUpdateEntry(
        $DatabaseName,
        Element $From,
        Element $To
    ) {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblConsumer = $tblAccount->getServiceTblConsumer();
        } else {
            $tblConsumer = null;
        }

        return ( new Data( $this->Binding ) )->createProtocolEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            $From,
            $To
        );
    }

    /**
     * @param string  $DatabaseName
     * @param Element $Entity
     *
     * @return false|TblProtocol
     */
    public function createDeleteEntry(
        $DatabaseName,
        Element $Entity = null
    ) {

        $tblAccount = Account::useService()->getAccountBySession();
        if ($tblAccount) {
            $tblConsumer = $tblAccount->getServiceTblConsumer();
        } else {
            $tblConsumer = null;
        }

        return ( new Data( $this->Binding ) )->createProtocolEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            $Entity,
            null
        );
    }
}
