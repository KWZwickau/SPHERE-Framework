<?php
namespace SPHERE\Application\Platform\System\Protocol;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\System\Archive\Archive;
use SPHERE\Application\Platform\System\Archive\Service\Entity\TblArchive;
use SPHERE\Application\Platform\System\Protocol\Service\Data;
use SPHERE\Application\Platform\System\Protocol\Service\Entity\TblProtocol;
use SPHERE\Application\Platform\System\Protocol\Service\Setup;
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
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        return (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
    }

    /**
     * @return bool|TblProtocol[]
     */
    public function getProtocolAll()
    {

        return (new Data($this->Binding))->getProtocolAll();
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

        Archive::useService()->createArchiveEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            $Entity, TblArchive::ARCHIVE_TYPE_CREATE
        );

        return (new Data($this->Binding))->createProtocolEntry(
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

        if (( $Protocol = (new Data($this->Binding))->createProtocolEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            $From,
            $To
        ) )
        ) {
            Archive::useService()->createArchiveEntry(
                $DatabaseName,
                ( $tblAccount ? $tblAccount : null ),
                ( $tblConsumer ? $tblConsumer : null ),
                $To, TblArchive::ARCHIVE_TYPE_UPDATE
            );
        };
        return $Protocol;
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

        return (new Data($this->Binding))->createProtocolEntry(
            $DatabaseName,
            ( $tblAccount ? $tblAccount : null ),
            ( $tblConsumer ? $tblConsumer : null ),
            $Entity,
            null
        );
    }
}
