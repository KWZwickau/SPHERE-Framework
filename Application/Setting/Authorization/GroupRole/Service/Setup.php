<?php

namespace SPHERE\Application\Setting\Authorization\GroupRole\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * 
 * @package SPHERE\Application\Setting\Authorization\GroupRole\Service
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
        $tblGroupRole = $this->setTableGroupRole($Schema);
        $this->setTableGroupRoleLink($Schema, $tblGroupRole);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        return $this->getConnection()->getProtocol($Simulate);
    }


    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableGroupRole(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblGroupRole');

        $this->createColumn($Table, 'Name', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGroupRole
     *
     * @return Table
     */
    private function setTableGroupRoleLink(Schema &$Schema, Table $tblGroupRole)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblGroupRoleLink');

        $this->createColumn($Table, 'serviceTblRole', self::FIELD_TYPE_BIGINT);

        $this->createForeignKey($Table, $tblGroupRole);

        return $Table;
    }
}