<?php
namespace SPHERE\Application\Document\Storage\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Service
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

        $Schema = clone $this->getConnection()->getSchema();
        $tblPartition = $this->setTablePartition($Schema);
        $tblDirectory = $this->setTableDirectory($Schema, $tblPartition);
        $tblBinary = $this->setTableBinary($Schema);
        $tblFileCategory = $this->setTableFileCategory($Schema);
        $tblFileType = $this->setTableFileType($Schema, $tblFileCategory);
        $tblFile = $this->setTableFile($Schema, $tblDirectory, $tblBinary, $tblFileType);
        $tblReferenceType = $this->setTableReferenceType($Schema);
        $this->setTableReference($Schema, $tblFile, $tblReferenceType);
        $this->setTablePersonPicture($Schema);
        $this->setTableBinaryRevision($Schema, $tblFile, $tblBinary);

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
    private function setTablePartition(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPartition');
        if (!$this->getConnection()->hasColumn('tblPartition', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPartition', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblPartition', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblPartition', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblPartition
     *
     * @return Table
     */
    private function setTableDirectory(Schema &$Schema, Table $tblPartition)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblDirectory');
        if (!$this->getConnection()->hasColumn('tblDirectory', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        $this->createIndex($Table, array('Identifier'), false);
        if (!$this->getConnection()->hasColumn('tblDirectory', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblDirectory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblDirectory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblPartition, true);
        $this->getConnection()->addForeignKey($Table, $Table, true);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableBinary(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblBinary');
        if (!$this->getConnection()->hasColumn('tblBinary', 'BinaryBlob')) {
            $Table->addColumn('BinaryBlob', 'blob');
        }
        if (!$this->getConnection()->hasColumn('tblBinary', 'Hash')) {
            $Table->addColumn('Hash', 'string');
        }
        $this->createColumn($Table, 'FileSizeKiloByte', self::FIELD_TYPE_INTEGER, false, 0);
        $this->createColumn($Table, 'serviceTblPersonPrinter', self::FIELD_TYPE_BIGINT, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableFileCategory(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblFileCategory');
        if (!$this->getConnection()->hasColumn('tblFileCategory', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFileCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblFileCategory
     *
     * @return Table
     */
    private function setTableFileType(Schema &$Schema, Table $tblFileCategory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblFileType');
        if (!$this->getConnection()->hasColumn('tblFileType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFileType', 'Extension')) {
            $Table->addColumn('Extension', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFileType', 'MimeType')) {
            $Table->addColumn('MimeType', 'string');
        }
        if (!$this->getConnection()->hasIndex($Table, array('Extension', 'MimeType'))) {
            $Table->addUniqueIndex(array('Extension', 'MimeType'));
        }
        $this->getConnection()->addForeignKey($Table, $tblFileCategory, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblDirectory
     * @param Table  $tblBinary
     * @param Table  $tblFileType
     *
     * @return Table
     */
    private function setTableFile(Schema &$Schema, Table $tblDirectory, Table $tblBinary, Table $tblFileType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblFile');
        if (!$this->getConnection()->hasColumn('tblFile', 'IsLocked')) {
            $Table->addColumn('IsLocked', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'Description')) {
            $Table->addColumn('Description', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblDirectory, true);
        $this->getConnection()->addForeignKey($Table, $tblFileType, true);
        $this->getConnection()->addForeignKey($Table, $tblBinary, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableReferenceType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblReferenceType');
        if (!$this->getConnection()->hasColumn('tblReferenceType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblReferenceType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblReferenceType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblFile
     * @param Table  $tblReferenceType
     *
     * @return Table
     */
    private function setTableReference(Schema &$Schema, Table $tblFile, Table $tblReferenceType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblReference');
        if (!$this->getConnection()->hasColumn('tblReference', 'foreignTblEntity')) {
            $Table->addColumn('foreignTblEntity', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblFile, true);
        $this->getConnection()->addForeignKey($Table, $tblReferenceType, true);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTablePersonPicture(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblPersonPicture');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Picture', self::FIELD_TYPE_BINARY); // Type::BLOB

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblFile
     * @param Table $tblBinary
     */
    private function setTableBinaryRevision(Schema &$Schema, Table $tblFile, Table $tblBinary): void
    {
        $table = $this->createTable($Schema, 'tblBinaryRevision');
        $this->createColumn($table, 'Version', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'Description');

        $this->createForeignKey($table, $tblFile);
        $this->createForeignKey($table, $tblBinary);
    }
}
