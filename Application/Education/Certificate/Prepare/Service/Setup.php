<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 11:18
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Certificate\Prepare\Service
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
        $tblPrepare = $this->setTableCertificatePrepare($Schema);
        $tblPrepareAdditionalGradeType = $this->setTablePrepareAdditionalGradeType($Schema);
        $this->setTablePrepareGrade($Schema, $tblPrepare);
        $this->setTablePrepareStudent($Schema, $tblPrepare);
        $this->setTablePrepareInformation($Schema, $tblPrepare);
        $this->setTablePrepareAdditionalGrade($Schema, $tblPrepare, $tblPrepareAdditionalGradeType);
        $tblLeaveStudent = $this->setTableLeaveStudent($Schema);
        $this->setTableLeaveGrade($Schema,$tblLeaveStudent);
        $this->setTableLeaveInformation($Schema,$tblLeaveStudent);

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
    private function setTableCertificatePrepare(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPrepareCertificate');
        if (!$this->getConnection()->hasColumn('tblPrepareCertificate', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareCertificate', 'Date')) {
            $Table->addColumn('Date', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareCertificate', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPrepareCertificate', 'serviceTblBehaviorTask')) {
            $Table->addColumn('serviceTblBehaviorTask', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareCertificate', 'serviceTblAppointedDateTask')) {
            $Table->addColumn('serviceTblAppointedDateTask', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareCertificate', 'serviceTblPersonSigner')) {
            $Table->addColumn('serviceTblPersonSigner', 'bigint', array('notnull' => false));
        }
        if (!$Table->hasColumn('IsGradeInformation')){
            $Table->addColumn('IsGradeInformation', 'boolean');
        }

        $this->createColumn($Table, 'serviceTblGenerateCertificate', self::FIELD_TYPE_BIGINT, true);

        $this->createIndex($Table, array('serviceTblGenerateCertificate', 'serviceTblDivision'));

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblPrepare
     *
     * @return Table
     */
    private function setTablePrepareGrade(Schema &$Schema, Table $tblPrepare)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPrepareGrade');
        if (!$this->getConnection()->hasColumn('tblPrepareGrade', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareGrade', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareGrade', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareGrade', 'serviceTblTestType')) {
            $Table->addColumn('serviceTblTestType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareGrade', 'serviceTblGradeType')) {
            $Table->addColumn('serviceTblGradeType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareGrade', 'Grade')) {
            $Table->addColumn('Grade', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblPrepare, true);

        $this->createIndex($Table, array('serviceTblGradeType'), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblPrepare
     *
     * @return Table
     */
    private function setTablePrepareStudent(Schema &$Schema, Table $tblPrepare)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPrepareStudent');
        if (!$this->getConnection()->hasColumn('tblPrepareStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareStudent', 'serviceTblCertificate')) {
            $Table->addColumn('serviceTblCertificate', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareStudent', 'IsApproved')) {
            $Table->addColumn('IsApproved', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblPrepareStudent', 'IsPrinted')) {
            $Table->addColumn('IsPrinted', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblPrepareStudent', 'ExcusedDays')) {
            $Table->addColumn('ExcusedDays', 'integer', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareStudent', 'UnexcusedDays')) {
            $Table->addColumn('UnexcusedDays', 'integer', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareStudent', 'serviceTblPersonSigner')) {
            $Table->addColumn('serviceTblPersonSigner', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblPrepare, true);
        $this->createIndex($Table, array('serviceTblPerson' , 'tblPrepareCertificate'));

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblPrepare
     *
     * @return Table
     */
    private function setTablePrepareInformation(Schema &$Schema, Table $tblPrepare)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPrepareInformation');
        if (!$this->getConnection()->hasColumn('tblPrepareInformation', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblPrepareInformation', 'Value')) {
            $Table->addColumn('Value', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblPrepareInformation', 'Field')) {
            $Table->addColumn('Field', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblPrepare, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblPrepare
     * @param Table $tblPrepareAdditionalGradeType
     *
     * @return Table
     */
    private function setTablePrepareAdditionalGrade(Schema &$Schema, Table $tblPrepare, Table $tblPrepareAdditionalGradeType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPrepareAdditionalGrade');

        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Grade', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Ranking', self::FIELD_TYPE_INTEGER);

        $this->getConnection()->addForeignKey($Table, $tblPrepare, true);
        $this->getConnection()->addForeignKey($Table, $tblPrepareAdditionalGradeType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTablePrepareAdditionalGradeType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblPrepareAdditionalGradeType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Identifier', self::FIELD_TYPE_STRING);
        $this->createIndex($Table, array('Identifier'));

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableLeaveStudent(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblLeaveStudent');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblCertificate', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'IsApproved', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'IsPrinted', self::FIELD_TYPE_BOOLEAN);

        $this->createIndex($Table, array('serviceTblPerson' , 'serviceTblDivision'));

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblLeaveStudent
     *
     * @return Table
     */
    private function setTableLeaveGrade(Schema &$Schema, Table $tblLeaveStudent)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblLeaveGrade');
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Grade', self::FIELD_TYPE_STRING);

        $this->getConnection()->addForeignKey($Table, $tblLeaveStudent);
        $this->createIndex($Table, array('serviceTblSubject' , 'tblLeaveStudent'));

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblLeaveStudent
     *
     * @return Table
     */
    private function setTableLeaveInformation(Schema &$Schema, Table $tblLeaveStudent)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblLeaveInformation');
        $this->createColumn($Table, 'Field', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Value', self::FIELD_TYPE_TEXT);

        $this->getConnection()->addForeignKey($Table, $tblLeaveStudent);
        $this->createIndex($Table, array('Field' , 'tblLeaveStudent'));

        return $Table;
    }
}
