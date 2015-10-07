<?php
namespace SPHERE\Application\Setting\Consumer\Responsibility\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Setting\Consumer\Responsibility\Service
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
        $this->setTableResponsibility( $Schema );
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
    private function setTableResponsibility( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblResponsibility' );
        if (!$this->Connection->hasColumn( 'tblResponsibility', 'serviceTblCompany' )) {
            $Table->addColumn( 'serviceTblCompany', 'bigint' );
        }

        return $Table;
    }
}