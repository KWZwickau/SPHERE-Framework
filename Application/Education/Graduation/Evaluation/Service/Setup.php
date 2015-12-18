<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 15.12.2015
 * Time: 09:39
 */

namespace SPHERE\Application\Education\Graduation\Evaluation\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * @package SPHERE\Application\Education\Graduation\Evaluation\Service
 */
class Setup extends AbstractSetup
{

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
        $Schema = clone $this->getConnection()->getSchema();
        $tblTestType = $this->setTableTestType($Schema);
        $tblTask = $this->setTableTask($Schema, $tblTestType);
        $this->setTableTest($Schema, $tblTestType, $tblTask);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTestType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblTestType');
        if (!$this->getConnection()->hasColumn('tblTestType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblTestType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblTestType
     * @param Table $tblTask
     * @return Table
     */
    private function setTableTest(Schema &$Schema, Table $tblTestType, Table $tblTask)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblTest');
        if (!$this->getConnection()->hasColumn('tblTest', 'Date')) {
            $Table->addColumn('Date', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'CorrectionDate')) {
            $Table->addColumn('CorrectionDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'ReturnDate')) {
            $Table->addColumn('ReturnDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblSubjectGroup')) {
            $Table->addColumn('serviceTblSubjectGroup', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblPeriod')) {
            $Table->addColumn('serviceTblPeriod', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblGradeType')) {
            $Table->addColumn('serviceTblGradeType', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblTestType, true);
        $this->getConnection()->addForeignKey($Table, $tblTask, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblTestType
     *
     * @return Table
     */
    private function setTableTask(Schema &$Schema, Table $tblTestType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblTask');
        if (!$this->getConnection()->hasColumn('tblTask', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblTask', 'Date')) {
            $Table->addColumn('Date', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTask', 'FromDate')) {
            $Table->addColumn('FromDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTask', 'ToDate')) {
            $Table->addColumn('ToDate', 'datetime', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblTestType, true);

        return $Table;
    }
}