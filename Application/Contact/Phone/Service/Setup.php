<?php
namespace SPHERE\Application\Contact\Phone\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Contact\Phone\Service
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
        $tblPhone = $this->setTablePhone( $Schema );
        $tblType = $this->setTableType( $Schema );
        $this->setTableToPerson( $Schema, $tblPhone, $tblType );
        $this->setTableToCompany( $Schema, $tblPhone, $tblType );
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
    private function setTablePhone( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblPhone' );
        if (!$this->Connection->hasColumn( 'tblPhone', 'Number' )) {
            $Table->addColumn( 'Number', 'string' );
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableType( Schema &$Schema )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblType' );
        if (!$this->Connection->hasColumn( 'tblType', 'Name' )) {
            $Table->addColumn( 'Name', 'string' );
        }
        if (!$this->Connection->hasColumn( 'tblType', 'Description' )) {
            $Table->addColumn( 'Description', 'string' );
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPhone
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToPerson( Schema &$Schema, Table $tblPhone, Table $tblType )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblToPerson' );
        if (!$this->Connection->hasColumn( 'tblToPerson', 'Remark' )) {
            $Table->addColumn( 'Remark', 'string' );
        }
        if (!$this->Connection->hasColumn( 'tblToPerson', 'serviceTblPerson' )) {
            $Table->addColumn( 'serviceTblPerson', 'bigint', array( 'notnull' => false ) );
        }
        $this->Connection->addForeignKey( $Table, $tblPhone );
        $this->Connection->addForeignKey( $Table, $tblType );
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPhone
     * @param Table  $tblType
     *
     * @return Table
     */
    private function setTableToCompany( Schema &$Schema, Table $tblPhone, Table $tblType )
    {

        $Table = $this->Connection->createTable( $Schema, 'tblToCompany' );
        if (!$this->Connection->hasColumn( 'tblToCompany', 'Remark' )) {
            $Table->addColumn( 'Remark', 'string' );
        }
        if (!$this->Connection->hasColumn( 'tblToCompany', 'serviceTblCompany' )) {
            $Table->addColumn( 'serviceTblCompany', 'bigint', array( 'notnull' => false ) );
        }
        $this->Connection->addForeignKey( $Table, $tblPhone );
        $this->Connection->addForeignKey( $Table, $tblType );
        return $Table;
    }
}
