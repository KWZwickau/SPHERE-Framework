<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 01.12.2015
 * Time: 10:39
 */

namespace SPHERE\Application\Reporting\CheckList\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * @package SPHERE\Application\Reporting\CheckList\Service
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
        $tblListType = $this->setTableListType($Schema);
        $tblElementType = $this->setTableElementType($Schema);
        $tblList = $this->setTableList($Schema, $tblListType);
        $this->setTableListObjectList($Schema, $tblList);
        $tblListElementList = $this->setTableListElementList($Schema, $tblList, $tblElementType);
        $this->setTableListObjectElementList($Schema, $tblList, $tblListElementList);

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
    private function setTableListType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblListType');
        if (!$this->getConnection()->hasColumn('tblListType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblListType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableElementType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblElementType');
        if (!$this->getConnection()->hasColumn('tblElementType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblElementType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblListType
     * @return Table
     */
    private function setTableList(Schema &$Schema, Table $tblListType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblList');
        if (!$this->getConnection()->hasColumn('tblList', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblList', 'Description')) {
            $Table->addColumn('Description', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblListType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblList
     * @return Table
     */
    private function setTableListObjectList(Schema &$Schema, Table $tblList)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblListObjectList');
        if (!$this->getConnection()->hasColumn('tblListObjectList', 'serviceTblGroup')) {
            $Table->addColumn('serviceTblGroup', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblListObjectList', 'serviceTblObject')) {
            $Table->addColumn('serviceTblObject', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblList, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblList
     * @param Table $tblElementType
     * @return Table
     */
    private function setTableListElementList(Schema &$Schema, Table $tblList, Table $tblElementType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblListElementList');
        if (!$this->getConnection()->hasColumn('tblListElementList', 'Name')) {
            $Table->addColumn('Name', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblList, true);
        $this->getConnection()->addForeignKey($Table, $tblElementType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblList
     * @param Table $tblListElementList
     * @return Table
     */
    private function setTableListObjectElementList(Schema &$Schema, Table $tblList, Table $tblListElementList)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblListObjectElementList');
        if (!$this->getConnection()->hasColumn('tblListObjectElementList', 'serviceTblObject')) {
            $Table->addColumn('serviceTblObject', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblList, true);
        $this->getConnection()->addForeignKey($Table, $tblListElementList, true);

        return $Table;
    }
}