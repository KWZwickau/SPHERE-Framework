<?php

namespace SPHERE\Application\Education\Competence\SkillGrid\Service;

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
        $tblSkillGrid = $this->setTableSkillGrid($schema);
        $tblSkillArea = $this->setTableSkillArea($schema, $tblSkillGrid);
        $this->setTableSkill($schema, $tblSkillArea);

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
    private function setTableSkillGrid(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblCompetenceSkillGrid');
        $this->createColumn($table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Level', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'serviceTblCourse', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'serviceTblSupportFocusType', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'IsAverage', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'serviceTblScoreType', self::FIELD_TYPE_INTEGER, true);

        // Todo Indexe

        return $table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblSkillGrid
     *
     * @return Table
     */
    private function setTableSkillArea(Schema &$schema, Table $tblSkillGrid): Table
    {
        $table = $this->createTable($schema, 'tblCompetenceSkillArea');
        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'SortOrder', self::FIELD_TYPE_INTEGER);

        $this->createForeignKey($table, $tblSkillGrid);

        return $table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblSkillArea
     *
     * @return void
     */
    private function setTableSkill(Schema &$schema, Table $tblSkillArea): void
    {
        $table = $this->createTable($schema, 'tblCompetenceSkill');
        $this->createColumn($table, 'Level', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'Skill');
        $this->createColumn($table, 'SortOrder', self::FIELD_TYPE_INTEGER);

        $this->createForeignKey($table, $tblSkillArea);
    }
}