<?php
namespace SPHERE\Application\People\Meta\Student\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Student\Service
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

        /**
         * Table
         */
        $Schema = clone $this->Connection->getSchema();
        $tblStudentMedicalRecord = $this->setTableStudentMedicalRecord($Schema);
        $tblStudentTransport = $this->setTableStudentTransport($Schema);

        $tblStudentTransferEnrollment = $this->setTableStudentTransferEnrollment($Schema);
        $tblStudentTransferArrive = $this->setTableStudentTransferArrive($Schema);
        $tblStudentTransferProcess = $this->setTableStudentTransferProcess($Schema);
        $tblStudentTransferLeave = $this->setTableStudentTransferLeave($Schema);
        $tblStudentTransfer = $this->setTableStudentTransfer($Schema,
            $tblStudentTransferEnrollment, $tblStudentTransferArrive, $tblStudentTransferProcess,
            $tblStudentTransferLeave
        );

        $this->setTableStudent($Schema, $tblStudentMedicalRecord, $tblStudentTransport, $tblStudentTransfer);
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
    private function setTableStudentMedicalRecord(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblStudentMedicalRecord');
        if (!$this->Connection->hasColumn('tblStudentMedicalRecord', 'Disease')) {
            $Table->addColumn('Disease', 'text');
        }
        if (!$this->Connection->hasColumn('tblStudentMedicalRecord', 'Medication')) {
            $Table->addColumn('Medication', 'text');
        }
        if (!$this->Connection->hasColumn('tblStudentMedicalRecord', 'serviceTblPersonAttendingDoctor')) {
            $Table->addColumn('serviceTblPersonAttendingDoctor', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentMedicalRecord', 'InsuranceState')) {
            $Table->addColumn('InsuranceState', 'bigint');
        }
        if (!$this->Connection->hasColumn('tblStudentMedicalRecord', 'Insurance')) {
            $Table->addColumn('Insurance', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransport(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblStudentTransport');
        if (!$this->Connection->hasColumn('tblStudentTransport', 'Route')) {
            $Table->addColumn('Route', 'string');
        }
        if (!$this->Connection->hasColumn('tblStudentTransport', 'StationEntrance')) {
            $Table->addColumn('StationEntrance', 'string');
        }
        if (!$this->Connection->hasColumn('tblStudentTransport', 'StationExit')) {
            $Table->addColumn('StationExit', 'string');
        }
        if (!$this->Connection->hasColumn('tblStudentTransport', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransferEnrollment(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblStudentTransferEnrollment');
        if (!$this->Connection->hasColumn('tblStudentTransferEnrollment', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentTransferEnrollment', 'EnrollmentDate')) {
            $Table->addColumn('EnrollmentDate', 'datetime', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentTransferEnrollment', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransferArrive(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblStudentTransferArrive');
        if (!$this->Connection->hasColumn('tblStudentTransferArrive', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentTransferArrive', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentTransferArrive', 'ArriveDate')) {
            $Table->addColumn('ArriveDate', 'datetime', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentTransferArrive', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransferProcess(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblStudentTransferProcess');
        if (!$this->Connection->hasColumn('tblStudentTransferProcess', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentTransferProcess', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransferLeave(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblStudentTransferLeave');
        if (!$this->Connection->hasColumn('tblStudentTransferLeave', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentTransferLeave', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentTransferLeave', 'LeaveDate')) {
            $Table->addColumn('LeaveDate', 'datetime', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblStudentTransferLeave', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentTransferEnrollment
     * @param Table  $tblStudentTransferArrive
     * @param Table  $tblStudentTransferProcess
     * @param Table  $tblStudentTransferLeave
     *
     * @return Table
     */
    private function setTableStudentTransfer(
        Schema &$Schema,
        Table $tblStudentTransferEnrollment,
        Table $tblStudentTransferArrive,
        Table $tblStudentTransferProcess,
        Table $tblStudentTransferLeave
    ) {

        $Table = $this->Connection->createTable($Schema, 'tblStudentTransfer');
        $this->Connection->addForeignKey($Table, $tblStudentTransferEnrollment);
        $this->Connection->addForeignKey($Table, $tblStudentTransferArrive);
        $this->Connection->addForeignKey($Table, $tblStudentTransferProcess);
        $this->Connection->addForeignKey($Table, $tblStudentTransferLeave);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentMedicalRecord
     * @param Table  $tblStudentTransport
     * @param Table  $tblStudentTransfer
     *
     * @return Table
     */
    private function setTableStudent(
        Schema &$Schema,
        Table $tblStudentMedicalRecord,
        Table $tblStudentTransport,
        Table $tblStudentTransfer
    ) {

        $Table = $this->Connection->createTable($Schema, 'tblStudent');
        if (!$this->Connection->hasColumn('tblStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->Connection->addForeignKey($Table, $tblStudentMedicalRecord);
        $this->Connection->addForeignKey($Table, $tblStudentTransport);
        $this->Connection->addForeignKey($Table, $tblStudentTransfer);
        return $Table;
    }
}
