<?php
namespace SPHERE\Application\Education\Lesson\Subject\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Lesson\Subject\Service
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
        $tblSubject = $this->setTableSubject($Schema);
        $tblCategory = $this->setTableCategory($Schema);
        $this->setTableMember($Schema, $tblSubject, $tblCategory);
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
    private function setTableSubject(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblSubject');
        if (!$this->Connection->hasColumn('tblSubject', 'Acronym')) {
            $Table->addColumn('Acronym', 'string');
        }
        if (!$this->Connection->hasIndex($Table, array('Acronym'))) {
            $Table->addUniqueIndex(array('Acronym'));
        }
        if (!$this->Connection->hasColumn('tblSubject', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblSubject', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCategory(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblCategory');
        if (!$this->Connection->hasColumn('tblCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblCategory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->Connection->hasColumn('tblCategory', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSubject
     * @param Table  $tblCategory
     *
     * @return Table
     */
    private function setTableMember(Schema &$Schema, Table $tblSubject, Table $tblCategory)
    {

        $Table = $this->Connection->createTable($Schema, 'tblMember');
        $this->Connection->addForeignKey($Table, $tblSubject);
        $this->Connection->addForeignKey($Table, $tblCategory);
        return $Table;
    }
}
