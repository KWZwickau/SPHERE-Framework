<?php
namespace SPHERE\Application\People\Group\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Group\Service
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
        $this->setTableGroup( $Schema );
        /**
         * Migration & Protocol
         */
        $this->Connection->addProtocol( __CLASS__ );
        $this->Connection->setMigration( $Schema, $Simulate );
        return $this->Connection->getProtocol( $Simulate );
    }

    /**
     * @param Schema $Schema
     * @return Table
     */
    private function setTableGroup( Schema &$Schema )
    {
        $Table = $this->Connection->createTable( $Schema, 'tblGroup' );
        if ( !$this->Connection->hasColumn( 'tblGroup', 'Name' ) ) {
            $Table->addColumn( 'Name', 'string' );
        }
        if ( !$this->Connection->hasColumn( 'tblGroup', 'Description' ) ) {
            $Table->addColumn( 'Description', 'string' );
        }
        if ( !$this->Connection->hasColumn( 'tblGroup', 'Remark' ) ) {
            $Table->addColumn( 'Remark', 'text' );
        }
        if ( !$this->Connection->hasColumn( 'tblGroup', 'IsLocked' ) ) {
            $Table->addColumn( 'IsLocked', 'boolean' );
        }
        if ( !$this->Connection->hasColumn( 'tblGroup', 'MetaTable' ) ) {
            $Table->addColumn( 'MetaTable', 'string' );
        }
        return $Table;
    }
}
