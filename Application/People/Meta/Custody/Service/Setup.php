<?php
namespace SPHERE\Application\People\Meta\Custody\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Custody\Service
 */
class Setup
{

    /** @var null|Structure $Connection */
    private $Connection = null;

    /**
     * @param Structure $Connection
     */
    function __construct(Structure $Connection)
    {

        $this->Connection = $Connection;
    }

    /**
     * @param bool $Simulate
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        $Schema = clone $this->Connection->getSchema();
        $this->setTableCustody($Schema);
        /**
         * Migration & Protocol
         */
        $this->Connection->addProtocol(__CLASS__);
        $this->Connection->setMigration($Schema, $Simulate);
        return $this->Connection->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCustody(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblCustody');
        if (!$this->Connection->hasColumn('tblCustody', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblCustody', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        if (!$this->Connection->hasColumn('tblCustody', 'Occupation')) {
            $Table->addColumn('Occupation', 'string');
        }
        if (!$this->Connection->hasColumn('tblCustody', 'Employment')) {
            $Table->addColumn('Employment', 'string');
        }
        return $Table;
    }
}
