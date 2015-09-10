<?php
namespace SPHERE\Application\People\Meta\Common\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Common\Service
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
        $tblCommonBirthDates = $this->setTableCommonBirthDates($Schema);
        $tblCommonInformation = $this->setTableCommonInformation($Schema);
        $this->setTableCommon($Schema, $tblCommonBirthDates, $tblCommonInformation);
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
    private function setTableCommonBirthDates(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblCommonBirthDates');
        if (!$this->Connection->hasColumn('tblCommonBirthDates', 'Birthday')) {
            $Table->addColumn('Birthday', 'datetime');
        }
        if (!$this->Connection->hasColumn('tblCommonBirthDates', 'Birthplace')) {
            $Table->addColumn('Birthplace', 'string');
        }
        if (!$this->Connection->hasColumn('tblCommonBirthDates', 'Gender')) {
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

        $Table = $this->Connection->createTable($Schema, 'tblCommonInformation');
        if (!$this->Connection->hasColumn('tblCommonInformation', 'Nationality')) {
            $Table->addColumn('Nationality', 'string');
        }
        if (!$this->Connection->hasColumn('tblCommonInformation', 'Denomination')) {
            $Table->addColumn('Denomination', 'string');
        }
        if (!$this->Connection->hasColumn('tblCommonInformation', 'AssistanceActivity')) {
            $Table->addColumn('AssistanceActivity', 'text');
        }
        if (!$this->Connection->hasColumn('tblCommonInformation', 'IsAssistance')) {
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

        $Table = $this->Connection->createTable($Schema, 'tblCommon');
        if (!$this->Connection->hasColumn('tblCommon', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->Connection->hasColumn('tblCommon', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        $this->Connection->addForeignKey($Table, $tblCommonBirthDates);
        $this->Connection->addForeignKey($Table, $tblCommonInformation);
        return $Table;
    }
}
