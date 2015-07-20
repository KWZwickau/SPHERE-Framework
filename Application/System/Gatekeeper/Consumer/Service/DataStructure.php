<?php
namespace SPHERE\Application\System\Gatekeeper\Consumer\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class DataStructure
 *
 * @package SPHERE\Application\System\Gatekeeper\Consumer\Service
 */
class DataStructure
{

    /** @var null|\SPHERE\System\Database\Fitting\Structure $Structure */
    private $Structure = null;

    /**
     *
     */
    function __construct()
    {

        $this->Structure = new Structure(
            new Identifier( 'System', 'Gatekeeper', 'Consumer' )
        );
    }

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupDatabaseSchema( $Simulate = true )
    {

        /**
         * Table
         */
        $Schema = clone $this->Structure->getSchema();
        $this->setTableConsumer( $Schema );
        /**
         * Migration & Protocol
         */
        $this->Structure->addProtocol( __CLASS__ );
        $this->Structure->setMigration( $Schema, $Simulate );
        return $this->Structure->getProtocol( $Simulate );
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableConsumer( Schema &$Schema )
    {

        $Table = $this->Structure->createTable( $Schema, 'tblConsumer' );
        if (!$this->Structure->hasColumn( 'tblConsumer', 'Name' )) {
            $Table->addColumn( 'Name', 'string' );
        }
        if (!$this->Structure->hasColumn( 'tblConsumer', 'Acronym' )) {
            $Table->addColumn( 'Acronym', 'string', array( 'notnull' => false ) );
        }
        if (!$this->Structure->hasIndex( $Table, array( 'Acronym' ) )) {
            $Table->addUniqueIndex( array( 'Acronym' ) );
        }

        return $Table;
    }

    /**
     * @return Table
     */
    protected function getTableConsumer()
    {

        return $this->Structure->getSchema()->getTable( 'tblConsumer' );
    }

}
