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
        $tblPrepare = $this->setTableCertificatePrepare($Schema);
        $tblPrepareAdditionalGradeType = $this->setTablePrepareAdditionalGradeType($Schema);
        $this->setTablePrepareGrade($Schema, $tblPrepare);
        $tblPrepareStudent = $this->setTablePrepareStudent($Schema, $tblPrepare);
        $this->setTablePrepareInformation($Schema, $tblPrepare);
        $this->setTablePrepareAdditionalGrade($Schema, $tblPrepare, $tblPrepareAdditionalGradeType);
        $tblLeaveStudent = $this->setTableLeaveStudent($Schema);
        $this->setTableLeaveGrade($Schema,$tblLeaveStudent);
        $this->setTableLeaveInformation($Schema,$tblLeaveStudent);
        $this->setTableLeaveAdditionalGrade($Schema, $tblLeaveStudent, $tblPrepareAdditionalGradeType);
        $this->setTableLeaveComplexExam($Schema, $tblLeaveStudent);
        $this->setTablePrepareComplexExam($Schema, $tblPrepareStudent);

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
    private function setTableCertificatePrepare(Schema &$Schema): Table
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblPrepareCertificate');
        $this->createColumn($Table, 'serviceTblGenerateCertificate', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPersonSigner', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'IsPrepared', self::FIELD_TYPE_BOOLEAN, false, false);

        // werden jetzt direkt über den Notenauftrag gezogen
        if ($this->getConnection()->hasColumn('tblPrepareCertificate', 'serviceTblBehaviorTask')) {
            $Table->dropColumn('serviceTblBehaviorTask');
        }
        if ($this->getConnection()->hasColumn('tblPrepareCertificate', 'serviceTblAppointedDateTask')) {
            $Table->dropColumn('serviceTblAppointedDateTask');
        }
        // wird auch nicht mehr benötigt
        if ($this->getConnection()->hasColumn('tblPrepareCertificate', 'serviceTblAppointedDateTask')) {
            $Table->dropColumn('IsGradeInformation');
        }
        if ($this->getConnection()->hasColumn('tblPrepareCertificate', 'Date')) {
            $Table->dropColumn('Date');
        }
        if ($this->getConnection()->hasColumn('tblPrepareCertificate', 'Name')) {
            $Table->dropColumn('Name');
        }

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
        $this->createColumn($Table, 'ExcusedDaysFromLessons', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($Table, 'UnexcusedDaysFromLessons', self::FIELD_TYPE_INTEGER, true);

        // alte Spalte löschen
        if ($this->getConnection()->hasColumn('tblPrepareStudent', 'IsPrepared')) {
            $Table->dropColumn('IsPrepared');
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
        $this->createColumn($Table, 'IsSelected', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'IsLocked', self::FIELD_TYPE_BOOLEAN);

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
    private function setTableLeaveStudent(Schema &$Schema): Table
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblLeaveStudent');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        // todo drop später
        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT);

        $this->createColumn($Table, 'serviceTblYear', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblCertificate', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'IsApproved', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'IsPrinted', self::FIELD_TYPE_BOOLEAN);

        // todo drop später
        $this->createIndex($Table, array('serviceTblPerson' , 'serviceTblDivision'));

        $this->createIndex($Table, array('serviceTblPerson' , 'serviceTblYear'));

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

    /**
     * @param Schema $Schema
     * @param Table $tblLeaveStudent
     * @param Table $tblPrepareAdditionalGradeType
     *
     * @return Table
     */
    private function setTableLeaveAdditionalGrade(Schema &$Schema, Table $tblLeaveStudent, Table $tblPrepareAdditionalGradeType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblLeaveAdditionalGrade');

        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Grade', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IsLocked', self::FIELD_TYPE_BOOLEAN);

        $this->getConnection()->addForeignKey($Table, $tblLeaveStudent, true);
        $this->getConnection()->addForeignKey($Table, $tblPrepareAdditionalGradeType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblLeaveStudent
     *
     * @return Table
     */
    private function setTableLeaveComplexExam(Schema &$Schema, Table $tblLeaveStudent)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblLeaveComplexExam');

        $this->createColumn($Table, 'Identifier', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Ranking', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'serviceTblFirstSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSecondSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Grade', self::FIELD_TYPE_STRING);

        $this->getConnection()->addForeignKey($Table, $tblLeaveStudent);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblPrepareStudent
     *
     * @return Table
     */
    private function setTablePrepareComplexExam(Schema &$Schema, Table $tblPrepareStudent)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblPrepareComplexExam');

        $this->createColumn($Table, 'Identifier', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Ranking', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'serviceTblFirstSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSecondSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Grade', self::FIELD_TYPE_STRING);

        $this->getConnection()->addForeignKey($Table, $tblPrepareStudent);

        return $Table;
    }
}
