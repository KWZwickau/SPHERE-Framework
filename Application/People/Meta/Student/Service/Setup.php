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
        $this->setTableStudent($Schema, $tblStudentMedicalRecord, $tblStudentTransport);
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
     * @param Table  $tblStudentMedicalRecord
     * @param Table  $tblStudentTransport
     *
     * @return Table
     */
    private function setTableStudent(Schema &$Schema, Table $tblStudentMedicalRecord, Table $tblStudentTransport)
    {

        $Table = $this->Connection->createTable($Schema, 'tblStudent');
        if (!$this->Connection->hasColumn('tblStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->Connection->addForeignKey($Table, $tblStudentMedicalRecord);
        $this->Connection->addForeignKey($Table, $tblStudentTransport);
        return $Table;
    }
}
