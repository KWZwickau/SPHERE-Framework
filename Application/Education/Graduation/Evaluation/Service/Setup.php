<?php
namespace SPHERE\Application\Education\Graduation\Evaluation\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\Graduation\Evaluation\Service\Entity\TblTest;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Graduation\Evaluation\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $tblTestType = $this->setTableTestType($Schema);
        $tblTask = $this->setTableTask($Schema, $tblTestType);
        $tblTest = $this->setTableTest($Schema, $tblTestType, $tblTask);
        $this->setTableTestLink($Schema, $tblTest);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
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
     * @param Table  $tblTestType
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
        if (!$this->getConnection()->hasColumn('tblTask', 'serviceTblPeriod')) {
            $Table->addColumn('serviceTblPeriod', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTask', 'serviceTblScoreType')) {
            $Table->addColumn('serviceTblScoreType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTask', 'serviceTblYear')) {
            $Table->addColumn('serviceTblYear', 'bigint', array('notnull' => false));
        }
        if (!$Table->hasColumn('IsLocked')){
            $Table->addColumn('IsLocked', 'boolean', array('default' => true));
        }

        $this->getConnection()->addForeignKey($Table, $tblTestType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblTestType
     * @param Table  $tblTask
     *
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
        if (!$Table->hasColumn('IsContinues')){
            $Table->addColumn('IsContinues', 'boolean');
        }
        $this->createColumn($Table, 'FinishDate', self::FIELD_TYPE_DATETIME, true);

        $this->getConnection()->addForeignKey($Table, $tblTestType, true);
        $this->getConnection()->addForeignKey($Table, $tblTask, true);

        $this->createIndex($Table, array('serviceTblGradeType'), false);
        $this->createIndex($Table, array(TblTest::ATTR_SERVICE_TBL_DIVISION,TblTest::ATTR_SERVICE_TBL_SUBJECT,TblTest::ENTITY_REMOVE), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblTest
     *
     * @return Table
     */
    private function setTableTestLink(Schema &$Schema, Table $tblTest)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblTestLink');
        if (!$Table->hasColumn('LinkId')){
            $Table->addColumn('LinkId', 'bigint');
        }

        $this->getConnection()->addForeignKey($Table, $tblTest, true);

        return $Table;
    }
}
