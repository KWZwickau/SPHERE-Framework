<?php
namespace SPHERE\Application\Platform\System\Test\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Fitting\Structure;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\System\Test\Service
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
        $this->setTestPicture($Schema);
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
    private function setTestPicture(Schema &$Schema)
    {

        $Table = $this->Connection->createTable($Schema, 'tblTestPicture');
        if (!$this->Connection->hasColumn('tblTestPicture', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->Connection->hasColumn('tblTestPicture', 'FileName')) {
            $Table->addColumn('FileName', 'string');
        }
        if (!$this->Connection->hasColumn('tblTestPicture', 'Extension')) {
            $Table->addColumn('Extension', 'string');
        }
        if (!$this->Connection->hasColumn('tblTestPicture', 'ImgData')) {
            $Table->addColumn('ImgData', 'blob');
        }
        if (!$this->Connection->hasColumn('tblTestPicture', 'ImgType')) {
            $Table->addColumn('ImgType', 'string');
        }
        if (!$this->Connection->hasColumn('tblTestPicture', 'Size')) {
            $Table->addColumn('Size', 'integer');
        }
        if (!$this->Connection->hasColumn('tblTestPicture', 'Width')) {
            $Table->addColumn('Width', 'integer');
        }
        if (!$this->Connection->hasColumn('tblTestPicture', 'Height')) {
            $Table->addColumn('Height', 'integer');
        }

        return $Table;
    }
}
