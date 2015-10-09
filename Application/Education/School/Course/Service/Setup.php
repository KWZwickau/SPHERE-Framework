<?php

namespace SPHERE\Application\Education\School\Course\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\School\Course\Service
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
        $this->setTableCourse($Schema);
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
    private function setTableCourse(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblCourse');
        if (!$this->getConnection()->hasColumn('tblCourse', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblCourse', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }
}
