<?php

namespace SPHERE\Application\Education\Competence\SkillRate\Service;

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
        $tblStudentSkill = $this->setTableStudentSkill($schema);
        $this->setTableStudentSkillRate($schema, $tblStudentSkill);

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
    private function setTableStudentSkill(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblCompetenceStudentSkill');
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblPersonTeacher', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'serviceTblSkill', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'SkillArea', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'SkillLevel', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'Skill');

        // Todo Indexe

        return $table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblStudentSkill
     *
     * @return void
     */
    private function setTableStudentSkillRate(Schema &$schema, Table $tblStudentSkill): void
    {
        $table = $this->createTable($schema, 'tblCompetenceStudentSkillRate');
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'serviceTblPersonTeacher', self::FIELD_TYPE_BIGINT, true);
        $this->createForeignKey($table, $tblStudentSkill);
        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($table, 'Comment', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'Rate', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'serviceTblScoreTypeItemId', self::FIELD_TYPE_BIGINT, true);

        // Todo Indexe
    }
}