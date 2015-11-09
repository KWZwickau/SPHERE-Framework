<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 02.11.2015
 * Time: 10:32
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service;


use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * @package SPHERE\Application\Education\Graduation\Gradebook\Service
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
        $tblGradeType = $this->setTableGradeType($Schema);
        $tblTest = $this->setTableTest($Schema, $tblGradeType);
        $this->setTableGradeStudentSubjectLink($Schema, $tblGradeType, $tblTest);

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
    private function setTableGradeType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGradeType');
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Code')) {
            $Table->addColumn('Code', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'IsHighlighted')) {
            $Table->addColumn('IsHighlighted', 'boolean');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     *
     * @return Table
     */
    private function setTableTest(Schema &$Schema, Table $tblGradeType)
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
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblPeriod')) {
            $Table->addColumn('serviceTblPeriod', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     * @param Table $tblTest
     *
     * @return Table
     */
    private function setTableGradeStudentSubjectLink(Schema &$Schema, Table $tblGradeType, Table $tblTest)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGradeStudentSubjectLink');
        if (!$this->getConnection()->hasColumn('tblGradeStudentSubjectLink', 'Grade')) {
            $Table->addColumn('Grade', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGradeStudentSubjectLink', 'Comment')) {
            $Table->addColumn('Comment', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGradeStudentSubjectLink', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGradeStudentSubjectLink', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGradeStudentSubjectLink', 'serviceTblPeriod')) {
            $Table->addColumn('serviceTblPeriod', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGradeStudentSubjectLink', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);
        $this->getConnection()->addForeignKey($Table, $tblTest, true);

        return $Table;
    }
}