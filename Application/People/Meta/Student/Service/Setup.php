<?php
namespace SPHERE\Application\People\Meta\Student\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Student\Service
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
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentMedicalRecord(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentMedicalRecord');
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Disease')) {
            $Table->addColumn('Disease', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Medication')) {
            $Table->addColumn('Medication', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'serviceTblPersonAttendingDoctor')) {
            $Table->addColumn('serviceTblPersonAttendingDoctor', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'InsuranceState')) {
            $Table->addColumn('InsuranceState', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Insurance')) {
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

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransport');
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'Route')) {
            $Table->addColumn('Route', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'StationEntrance')) {
            $Table->addColumn('StationEntrance', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'StationExit')) {
            $Table->addColumn('StationExit', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'Remark')) {
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

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferEnrollment');
        if (!$this->getConnection()->hasColumn('tblStudentTransferEnrollment', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferEnrollment', 'EnrollmentDate')) {
            $Table->addColumn('EnrollmentDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferEnrollment', 'Remark')) {
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

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferArrive');
        if (!$this->getConnection()->hasColumn('tblStudentTransferArrive', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferArrive', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferArrive', 'ArriveDate')) {
            $Table->addColumn('ArriveDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferArrive', 'Remark')) {
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

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferProcess');
        if (!$this->getConnection()->hasColumn('tblStudentTransferProcess', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferProcess', 'Remark')) {
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

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferLeave');
        if (!$this->getConnection()->hasColumn('tblStudentTransferLeave', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferLeave', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferLeave', 'LeaveDate')) {
            $Table->addColumn('LeaveDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferLeave', 'Remark')) {
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

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransfer');
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferEnrollment);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferArrive);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferProcess);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferLeave);
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

        $Table = $this->getConnection()->createTable($Schema, 'tblStudent');
        if (!$this->getConnection()->hasColumn('tblStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblStudentMedicalRecord);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransport);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransfer);
        return $Table;
    }
}
