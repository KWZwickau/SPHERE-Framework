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
        $this->setTablePrepareGrade($Schema, $tblPrepare);

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

        $Table = $this->getConnection()->createTable($Schema, 'tblCertificatePrepare');
        if (!$this->getConnection()->hasColumn('tblCertificatePrepare', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblCertificatePrepare', 'Date')) {
            $Table->addColumn('Date', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblCertificatePrepare', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCertificatePrepare', 'IsApproved')) {
            $Table->addColumn('IsApproved', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblCertificatePrepare', 'IsPrinted')) {
            $Table->addColumn('IsPrinted', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblCertificatePrepare', 'serviceTblBehaviorTask')) {
            $Table->addColumn('serviceTblBehaviorTask', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblCertificatePrepare', 'serviceTblAppointedDateTask')) {
            $Table->addColumn('serviceTblAppointedDateTask', 'bigint', array('notnull' => false));
        }

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

        return $Table;
    }
}
