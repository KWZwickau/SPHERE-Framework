<?php
namespace SPHERE\Application\Platform\System\Test\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Platform\System\Test\Service
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
        $this->setTestPicture($Schema);
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
    private function setTestPicture(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblTestPicture');
        if (!$this->getConnection()->hasColumn('tblTestPicture', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblTestPicture', 'FileName')) {
            $Table->addColumn('FileName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblTestPicture', 'Extension')) {
            $Table->addColumn('Extension', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblTestPicture', 'ImgData')) {
            $Table->addColumn('ImgData', 'blob');
        }
        if (!$this->getConnection()->hasColumn('tblTestPicture', 'ImgType')) {
            $Table->addColumn('ImgType', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblTestPicture', 'Size')) {
            $Table->addColumn('Size', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblTestPicture', 'Width')) {
            $Table->addColumn('Width', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblTestPicture', 'Height')) {
            $Table->addColumn('Height', 'integer');
        }

        return $Table;
    }
}
