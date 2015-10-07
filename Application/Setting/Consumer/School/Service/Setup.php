<?php
namespace SPHERE\Application\Setting\Consumer\School\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Setting\Consumer\School\Service
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
        $this->setTableSchool( $Schema );
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
    private function setTableSchool( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblSchool' );
        if (!$this->Connection->hasColumn( 'tblSchool', 'serviceTblCompany' )) {
            $Table->addColumn( 'serviceTblCompany', 'bigint' );
        }
        if (!$this->Connection->hasColumn( 'tblSchool', 'serviceTblType' )) {
            $Table->addColumn( 'serviceTblType', 'bigint' );
        }

        return $Table;
    }
}