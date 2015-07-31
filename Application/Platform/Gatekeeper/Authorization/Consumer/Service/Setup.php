<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Consumer\Service
 */
class Setup
{

    /** @var null|Structure $Connection */
    private $Connection = null;

    /**
     * @param Structure $Connection
     */
    function __construct( Structure $Connection )
    {

        $this->Connection = $Connection;
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
        $Schema = clone $this->Connection->getSchema();
        $this->setTableConsumer( $Schema );
        /**
         * Migration & Protocol
         */
        $this->Connection->addProtocol( __CLASS__ );
        $this->Connection->setMigration( $Schema, $Simulate );
        return $this->Connection->getProtocol( $Simulate );
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableConsumer( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblConsumer' );
        if (!$this->Connection->hasColumn( 'tblConsumer', 'Acronym' )) {
            $Table->addColumn( 'Acronym', 'string' );
        }
        if (!$this->Connection->hasIndex( $Table, array( 'Acronym' ) )) {
            $Table->addUniqueIndex( array( 'Acronym' ) );
        }
        if (!$this->Connection->hasColumn( 'tblConsumer', 'Name' )) {
            $Table->addColumn( 'Name', 'string' );
        }

        return $Table;
    }
}
