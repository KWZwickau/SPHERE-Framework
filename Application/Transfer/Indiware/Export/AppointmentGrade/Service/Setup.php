<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service
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
        $this->setTableTblIndiwareStudentSubjectOrder($Schema);

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
     * @return Table $tblTblIndiwareStudentSubjectOrder
     *
     * @return Table
     */
    private function setTableTblIndiwareStudentSubjectOrder(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblIndiwareStudentSubjectOrder');
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'serviceTblTask')) {
            $Table->addColumn('serviceTblTask', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Period')) {
            $Table->addColumn('Period', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'FirstName')) {
            $Table->addColumn('FirstName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'LastName')) {
            $Table->addColumn('LastName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'BirthDay')) {
            $Table->addColumn('BirthDay', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject1')) {
            $Table->addColumn('Subject1', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject2')) {
            $Table->addColumn('Subject2', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject3')) {
            $Table->addColumn('Subject3', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject4')) {
            $Table->addColumn('Subject4', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject5')) {
            $Table->addColumn('Subject5', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject6')) {
            $Table->addColumn('Subject6', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject7')) {
            $Table->addColumn('Subject7', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject8')) {
            $Table->addColumn('Subject8', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject9')) {
            $Table->addColumn('Subject9', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject10')) {
            $Table->addColumn('Subject10', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject11')) {
            $Table->addColumn('Subject11', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject12')) {
            $Table->addColumn('Subject12', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject13')) {
            $Table->addColumn('Subject13', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject14')) {
            $Table->addColumn('Subject14', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject15')) {
            $Table->addColumn('Subject15', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject16')) {
            $Table->addColumn('Subject16', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareStudentSubjectOrder', 'Subject17')) {
            $Table->addColumn('Subject17', 'string');
        }

        return $Table;
    }
}