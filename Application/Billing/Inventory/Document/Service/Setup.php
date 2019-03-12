<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.03.2019
 * Time: 09:38
 */

namespace SPHERE\Application\Billing\Inventory\Document\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Billing\Inventory\Document\Service
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
        $tblDocument = $this->setTableDocument($Schema);
        $this->setTableDocumentItem($Schema, $tblDocument);
        $this->setTableDocumentInformation($Schema, $tblDocument);

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
    private function setTableDocument(Schema &$Schema)
    {
        $Table = $this->createTable($Schema, 'tblDocument');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDocument
     *
     * @return Table
     */
    private function setTableDocumentItem(Schema &$Schema, Table $tblDocument)
    {
        $Table = $this->createTable($Schema, 'tblDocumentItem');
        $this->createColumn($Table, 'serviceTblItem', self::FIELD_TYPE_BIGINT, true);

        $this->createForeignKey($Table, $tblDocument);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDocument
     *
     * @return Table
     */
    private function setTableDocumentInformation(Schema &$Schema, Table $tblDocument)
    {
        $Table = $this->createTable($Schema, 'tblDocumentInformation');
        $this->createColumn($Table, 'Field', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Value', self::FIELD_TYPE_TEXT);

        $this->createForeignKey($Table, $tblDocument);

        return $Table;
    }
}