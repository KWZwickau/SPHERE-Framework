<?php
namespace SPHERE\Application\Document\Explorer\Storage\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * @package SPHERE\Application\Document\Explorer\Storage\Service
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

        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableFile($Schema);
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
    private function setTableFile(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblFile');
        if (!$this->getConnection()->hasColumn('tblFile', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileName')) {
            $Table->addColumn('FileName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileExtension')) {
            $Table->addColumn('FileExtension', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileContent')) {
            $Table->addColumn('FileContent', 'blob');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileType')) {
            $Table->addColumn('FileType', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblFile', 'FileSize')) {
            $Table->addColumn('FileSize', 'integer');
        }

        return $Table;
    }
}
