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

//        $this->setTable($Schema);

        return $this->saveSchema($Schema, $Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTable(Schema $Schema)
    {

//        $Table = $this->createTable($Schema, 'tblDynamicFilter');
//        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT, true);
//        $this->createColumn($Table, 'FilterName');
//        $this->createIndex($Table, array('serviceTblAccount','FilterName'));
//        $this->createColumn($Table, 'IsPublic', self::FIELD_TYPE_BOOLEAN);
//
//        return $Table;
    }
}
