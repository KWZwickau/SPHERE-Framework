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
        if (!$this->Connection->hasColumn('tblTestPicture', 'ImgData')) {
            $Table->addColumn('ImgData', 'blob');
        }
        if (!$this->Connection->hasColumn('tblTestPicture', 'ImgType')) {
            $Table->addColumn('ImgType', 'string');
        }

        return $Table;
    }
}
