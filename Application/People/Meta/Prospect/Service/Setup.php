<?php
namespace SPHERE\Application\People\Meta\Prospect\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Prospect\Service
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
        $tblProspectAppointment = $this->setTableProspectAppointment($Schema);
        $tblProspectReservation = $this->setTableProspectReservation($Schema);
        $this->setTableProspect($Schema, $tblProspectAppointment, $tblProspectReservation);
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
    private function setTableProspectAppointment(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblProspectAppointment');
        if (!$this->Connection->hasColumn('tblProspectAppointment', 'ReservationDate')) {
            $Table->addColumn('ReservationDate', 'datetime', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblProspectAppointment', 'InterviewDate')) {
            $Table->addColumn('InterviewDate', 'datetime', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblProspectAppointment', 'TrialDate')) {
            $Table->addColumn('TrialDate', 'datetime', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableProspectReservation(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblProspectReservation');
        if (!$this->Connection->hasColumn('tblProspectReservation', 'ReservationYear')) {
            $Table->addColumn('ReservationYear', 'string');
        }
        if (!$this->Connection->hasColumn('tblProspectReservation', 'ReservationDivision')) {
            $Table->addColumn('ReservationDivision', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblProspectAppointment
     * @param Table  $tblProspectReservation
     *
     * @return Table
     */
    private function setTableProspect(Schema &$Schema, Table $tblProspectAppointment, Table $tblProspectReservation)
    {

        $Table = $this->Connection->createTable($Schema, 'tblProspect');
        if (!$this->Connection->hasColumn('tblProspect', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblProspect', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        $this->Connection->addForeignKey($Table, $tblProspectAppointment);
        $this->Connection->addForeignKey($Table, $tblProspectReservation);
        return $Table;
    }
}
