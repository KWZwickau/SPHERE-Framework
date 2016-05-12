<?php
namespace SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\Gatekeeper\Authorization\Access\Service
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
        $tblRight = $this->setTableRight($Schema);
        $tblPrivilege = $this->setTablePrivilege($Schema);
        $tblLevel = $this->setTableLevel($Schema);
        $tblRole = $this->setTableRole($Schema);

        $this->setTablePrivilegeRight($Schema, $tblPrivilege, $tblRight);
        $this->setTableLevelPrivilege($Schema, $tblLevel, $tblPrivilege);
        $this->setTableRoleLevel($Schema, $tblRole, $tblLevel);
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
    private function setTableRight(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblRight');
        if (!$this->getConnection()->hasColumn('tblRight', 'Route')) {
            $Table->addColumn('Route', 'string');
        }
        $this->getConnection()->removeIndex($Table, array('Route'));
        if (!$this->getConnection()->hasIndex($Table, array('Route', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Route', Element::ENTITY_REMOVE));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTablePrivilege(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPrivilege');
        if (!$this->getConnection()->hasColumn('tblPrivilege', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        $this->getConnection()->removeIndex($Table, array('Name'));
        if (!$this->getConnection()->hasIndex($Table, array('Name', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Name', Element::ENTITY_REMOVE));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableLevel(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblLevel');
        if (!$this->getConnection()->hasColumn('tblLevel', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        $this->getConnection()->removeIndex($Table, array('Name'));
        if (!$this->getConnection()->hasIndex($Table, array('Name', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Name', Element::ENTITY_REMOVE));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableRole(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblRole');
        if (!$this->getConnection()->hasColumn('tblRole', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        $this->getConnection()->removeIndex($Table, array('Name'));
        if (!$this->getConnection()->hasIndex($Table, array('Name', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Name', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblRole', 'IsInternal')) {
            $Table->addColumn('IsInternal', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblRole', 'IsSecure')) {
            $Table->addColumn('IsSecure', 'boolean');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPrivilege
     * @param Table  $tblRight
     *
     * @return Table
     */
    private function setTablePrivilegeRight(
        Schema &$Schema,
        Table $tblPrivilege,
        Table $tblRight
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblPrivilegeRight');
        $this->getConnection()->addForeignKey($Table, $tblPrivilege);
        $this->getConnection()->addForeignKey($Table, $tblRight);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblLevel
     * @param Table  $tblPrivilege
     *
     * @return Table
     */
    private function setTableLevelPrivilege(
        Schema &$Schema,
        Table $tblLevel,
        Table $tblPrivilege
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblLevelPrivilege');
        $this->getConnection()->addForeignKey($Table, $tblLevel);
        $this->getConnection()->addForeignKey($Table, $tblPrivilege);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblRole
     * @param Table  $tblLevel
     *
     * @return Table
     */
    private function setTableRoleLevel(
        Schema &$Schema,
        Table $tblRole,
        Table $tblLevel
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblRoleLevel');
        $this->getConnection()->addForeignKey($Table, $tblRole);
        $this->getConnection()->addForeignKey($Table, $tblLevel);
        return $Table;
    }
}
