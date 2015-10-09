<?php
namespace SPHERE\Application\Setting\Consumer\School\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Setting\Consumer\School\Service
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
        $this->setTableSchool($Schema);
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
    private function setTableSchool(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblSchool');
        if (!$this->getConnection()->hasColumn('tblSchool', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblSchool', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint');
        }

        return $Table;
    }
}
