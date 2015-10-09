<?php

namespace SPHERE\Application\Education\Graduation\ScoreType\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\Graduation\ScoreType\Service
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
        $this->setTableScoreTypes($Schema);
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
    private function setTableScoreTypes(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreType');
        if (!$this->getConnection()->hasColumn('tblScoreType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreType', 'Short')) {
            $Table->addColumn('Short', 'string');
        }
        return $Table;
    }
}
