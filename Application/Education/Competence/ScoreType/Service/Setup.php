<?php

namespace SPHERE\Application\Education\Competence\ScoreType\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

class Setup extends AbstractSetup
{
    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false): string
    {
        /**
         * Table
         */
        $schema = clone $this->getConnection()->getSchema();
        $tblScoreType = $this->setTableScoreType($schema);
        $this->setTableScoreTypeItem($schema, $tblScoreType);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }
        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $schema
     *
     * @return Table
     */
    private function setTableScoreType(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblCompetenceScoreType');
        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'Description');

        return $table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblScoreType
     *
     * @return void
     */
    private function setTableScoreTypeItem(Schema &$schema, Table $tblScoreType): void
    {
        $table = $this->createTable($schema, 'tblCompetenceScoreTypeItem');
        $this->createColumn($table, 'Value');
        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'Description', self::FIELD_TYPE_TEXT, true);

        $this->createForeignKey($table, $tblScoreType);
    }
}