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
        $tblGroup = $this->setTableGroup($Schema);
        $tblCategory = $this->setTableCategory($Schema);
        $this->setTableGroupCategory($Schema, $tblGroup, $tblCategory);
        $tblSubject = $this->setTableSubject($Schema);
        $this->setTableCategorySubject($Schema, $tblCategory, $tblSubject);
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
    private function setTableGroup(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblGroup');
        if (!$this->Connection->hasColumn('tblGroup', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->Connection->hasColumn('tblGroup', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        if (!$this->Connection->hasColumn('tblGroup', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblGroup', 'Description')) {
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
        if (!$this->Connection->hasColumn('tblCategory', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->Connection->hasColumn('tblCategory', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        if (!$this->Connection->hasColumn('tblCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblCategory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblGroup
     * @param Table  $tblCategory
     *
     * @return Table
     */
    private function setTableGroupCategory(Schema &$Schema, Table $tblGroup, Table $tblCategory)
    {

        $Table = $this->Connection->createTable($Schema, 'tblGroupCategory');
        $this->Connection->addForeignKey($Table, $tblGroup);
        $this->Connection->addForeignKey($Table, $tblCategory);
        return $Table;
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
     * @param Table  $tblCategory
     * @param Table  $tblSubject
     *
     * @return Table
     */
    private function setTableCategorySubject(Schema &$Schema, Table $tblCategory, Table $tblSubject)
    {

        $Table = $this->Connection->createTable($Schema, 'tblCategorySubject');
        $this->Connection->addForeignKey($Table, $tblCategory);
        $this->Connection->addForeignKey($Table, $tblSubject);
        return $Table;
    }


}
