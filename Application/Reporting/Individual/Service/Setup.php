<?php

namespace SPHERE\Application\Reporting\Individual\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Reporting\Individual\Service
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

        $Schema = $this->loadSchema();

        $this->setTableWorkSpace($Schema);
        $tblPreset = $this->setTablePreset($Schema);
        $this->setTablePresetSetting($Schema, $tblPreset);

        return $this->saveSchema($Schema, $Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableWorkSpace(Schema $Schema)
    {

        $Table = $this->createTable($Schema, 'tblWorkSpace');
        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Field', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'View', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Position', self::FIELD_TYPE_INTEGER);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTablePreset(Schema $Schema)
    {

        $Table = $this->createTable($Schema, 'tblPreset');
        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPreset
     *
     * @return Table
     */
    private function setTablePresetSetting(Schema $Schema, Table $tblPreset)
    {

        $Table = $this->createTable($Schema, 'tblPresetSetting');
        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Field', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'View', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Position', self::FIELD_TYPE_INTEGER);
        $this->getConnection()->addForeignKey($Table, $tblPreset);

        return $Table;
    }
}
