<?php
namespace SPHERE\Application\System\Gatekeeper\Token\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class DataStructure
 *
 * @package SPHERE\Application\System\Gatekeeper\Token\Service
 */
class DataStructure
{

    /** @var null|Structure $Structure */
    private $Structure = null;

    /**
     *
     */
    function __construct()
    {

        $this->Structure = new Structure(
            new Identifier( 'System', 'Gatekeeper', 'Token' )
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
        $this->setTableToken( $Schema );
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
     * @throws SchemaException
     */
    private function setTableToken( Schema &$Schema )
    {

        /**
         * Install
         */
        $Table = $this->Structure->createTable( $Schema, 'tblToken' );
        /**
         * Upgrade
         */
        if (!$this->Structure->hasColumn( 'tblToken', 'Identifier' )) {
            $Table->addColumn( 'Identifier', 'string' );
        }
        if (!$this->Structure->hasIndex( $Table, array( 'Identifier' ) )) {
            $Table->addUniqueIndex( array( 'Identifier' ) );
        }
        if (!$this->Structure->hasColumn( 'tblToken', 'Serial' )) {
            $Table->addColumn( 'Serial', 'string', array( 'notnull' => false ) );
        }
        if (!$this->Structure->hasIndex( $Table, array( 'Serial' ) )) {
            $Table->addUniqueIndex( array( 'Serial' ) );
        }
        if (!$this->Structure->hasColumn( 'tblToken', 'serviceGatekeeper_Consumer' )) {
            $Table->addColumn( 'serviceGatekeeper_Consumer', 'bigint', array( 'notnull' => false ) );
        }

        return $Table;
    }

    /**
     * @return Table
     * @throws SchemaException
     */
    protected function getTableToken()
    {

        return $this->Structure->getSchema()->getTable( 'tblToken' );
    }
}
