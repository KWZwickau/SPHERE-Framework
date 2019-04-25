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
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        $Schema = $this->loadSchema();


        $tblPreset = $this->setTablePreset($Schema);
        $this->setTableWorkSpace($Schema, $tblPreset);
        $this->setTablePresetSetting($Schema, $tblPreset);

        return $this->saveSchema($Schema, $Simulate);
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
        $this->createColumn($Table, 'IsPublic', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->createColumn($Table, 'PersonCreator', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'PostValue', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPreset
     *
     * @return Table
     */
    private function setTableWorkSpace(Schema $Schema, Table $tblPreset)
    {

        $Table = $this->createTable($Schema, 'tblWorkSpace');
        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Field', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'View', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'ViewType', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Position', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'FieldCount', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'IsExpanded', self::FIELD_TYPE_BOOLEAN);
        $this->getConnection()->addForeignKey($Table, $tblPreset, true);

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
        $this->createColumn($Table, 'ViewType', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Position', self::FIELD_TYPE_INTEGER);
        $this->getConnection()->addForeignKey($Table, $tblPreset);

        return $Table;
    }
}
