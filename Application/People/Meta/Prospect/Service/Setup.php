<?php
namespace SPHERE\Application\People\Meta\Prospect\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Prospect\Service
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
        $tblProspectAppointment = $this->setTableProspectAppointment($Schema);
        $tblProspectReservation = $this->setTableProspectReservation($Schema);
        $this->setTableProspect($Schema, $tblProspectAppointment, $tblProspectReservation);
        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewPeopleMetaProspect') )
                ->addLink(new TblProspect(), 'tblProspectAppointment', new TblProspectAppointment(), 'Id')
                ->addLink(new TblProspect(), 'tblProspectReservation', new TblProspectReservation(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableProspectAppointment(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblProspectAppointment');
        if (!$this->getConnection()->hasColumn('tblProspectAppointment', 'ReservationDate')) {
            $Table->addColumn('ReservationDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblProspectAppointment', 'InterviewDate')) {
            $Table->addColumn('InterviewDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblProspectAppointment', 'TrialDate')) {
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

        $Table = $this->createTable($Schema, 'tblProspectReservation');
        $this->createColumn($Table, 'ReservationYear', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'ReservationDivision', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblTypeOptionA', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblTypeOptionB', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblCompany', self::FIELD_TYPE_BIGINT, true);
        // e.g. ForeignKey
//        $this->createForeignKey($Table, $WertAusFunktionsaufruf, true);

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

        $Table = $this->getConnection()->createTable($Schema, 'tblProspect');
        if (!$this->getConnection()->hasColumn('tblProspect', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblProspect', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        $this->getConnection()->addForeignKey($Table, $tblProspectAppointment);
        $this->getConnection()->addForeignKey($Table, $tblProspectReservation);
        return $Table;
    }
}
