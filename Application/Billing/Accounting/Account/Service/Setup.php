<?php

namespace SPHERE\Application\Billing\Accounting\Account\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

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

        /**
         * Table
         */
        $Schema = clone $this->Connection->getSchema();
        $tblAccountKeyType = $this->setTableAccountKeyType($Schema);
        $tblAccountType = $this->setTableAccountType($Schema);
        $tblAccountKey = $this->setTableAccountKey($Schema, $tblAccountKeyType);
        $this->setTableAccount($Schema, $tblAccountType, $tblAccountKey);

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
    private function setTableAccountType(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblAccountType');
        if (!$this->Connection->hasColumn('tblAccountType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblAccountType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccountType
     * @param Table  $tblAccountKey
     *
     * @return Table
     */
    private function setTableAccount(Schema &$Schema, Table $tblAccountType, Table $tblAccountKey)
    {

        $Table = $this->Connection->createTable($Schema, 'tblAccount');
        if (!$this->Connection->hasColumn('tblAccount', 'Number')) {
            $Table->addColumn('Number', 'string');
        }
        if (!$this->Connection->hasColumn('tblAccount', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->Connection->hasColumn('tblAccount', 'IsActive')) {
            $Table->addColumn('IsActive', 'boolean');
        }
        $this->Connection->addForeignKey($Table, $tblAccountType);
        $this->Connection->addForeignKey($Table, $tblAccountKey);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table $tblAccountKeyType
     *
     * @return Table
     */
    private function setTableAccountKeyType(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblAccountKeyType');
        if (!$this->Connection->hasColumn('tblAccountKeyType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblAccountKeyType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblAccountKeyType
     *
     * @return Table
     */
    private function setTableAccountKey(Schema &$Schema, Table $tblAccountKeyType)
    {

        $Table = $this->Connection->createTable($Schema, 'tblAccountKey');
        if (!$this->Connection->hasColumn('tblAccountKey', 'ValidFrom')) {
            $Table->addColumn('ValidFrom', 'date');
        }
        if (!$this->Connection->hasColumn('tblAccountKey', 'Value')) {
            $Table->addColumn('Value', 'string');
        }
        if (!$this->Connection->hasColumn('tblAccountKey', 'ValidTo')) {
            $Table->addColumn('ValidTo', 'date');
        }
        if (!$this->Connection->hasColumn('tblAccountKey', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->Connection->hasColumn('tblAccountKey', 'Code')) {
            $Table->addColumn('Code', 'integer');
        }
        $this->Connection->addForeignKey($Table, $tblAccountKeyType);
        return $Table;
    }
}
