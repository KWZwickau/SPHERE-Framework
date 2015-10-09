<?php
namespace SPHERE\Application\People\Meta\Common\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Common\Service
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
        $tblCommonBirthDates = $this->setTableCommonBirthDates($Schema);
        $tblCommonInformation = $this->setTableCommonInformation($Schema);
        $this->setTableCommon($Schema, $tblCommonBirthDates, $tblCommonInformation);
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
    private function setTableCommonBirthDates(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommonBirthDates');
        if (!$this->getConnection()->hasColumn('tblCommonBirthDates', 'Birthday')) {
            $Table->addColumn('Birthday', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblCommonBirthDates', 'Birthplace')) {
            $Table->addColumn('Birthplace', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCommonBirthDates', 'Gender')) {
            $Table->addColumn('Gender', 'smallint');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCommonInformation(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommonInformation');
        if (!$this->getConnection()->hasColumn('tblCommonInformation', 'Nationality')) {
            $Table->addColumn('Nationality', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCommonInformation', 'Denomination')) {
            $Table->addColumn('Denomination', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCommonInformation', 'AssistanceActivity')) {
            $Table->addColumn('AssistanceActivity', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblCommonInformation', 'IsAssistance')) {
            $Table->addColumn('IsAssistance', 'smallint');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblCommonBirthDates
     * @param Table  $tblCommonInformation
     *
     * @return Table
     */
    private function setTableCommon(Schema &$Schema, Table $tblCommonBirthDates, Table $tblCommonInformation)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCommon');
        if (!$this->getConnection()->hasColumn('tblCommon', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblCommon', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        $this->getConnection()->addForeignKey($Table, $tblCommonBirthDates);
        $this->getConnection()->addForeignKey($Table, $tblCommonInformation);
        return $Table;
    }
}
