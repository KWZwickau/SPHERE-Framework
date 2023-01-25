<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

class Setup  extends AbstractSetup
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
        // todo indexe
        $tblGradeType = $this->setTableGradeType($schema);
        $tblTest = $this->setTableTest($schema, $tblGradeType);
        $this->setTableTestGrade($schema, $tblTest);
        $this->setTableTestCourseLink($schema, $tblTest);
        $this->setTableTestStudentLink($schema, $tblTest);

        $tblScoreType = $this->setTableScoreType($schema);
        $tblGradeText = $this->setTableGradeText($schema);
        $tblTask = $this->setTableTask($schema, $tblScoreType);
        $this->setTableTaskGrade($schema, $tblTask, $tblGradeType, $tblGradeText);
        $this->setTableTaskCourseLink($schema, $tblTask);
        $this->setTableTaskGradeTypeLink($schema, $tblTask, $tblGradeType);

        // Score
        $this->setTableScoreTypeSubject($schema, $tblScoreType);
        $tblScoreGroup = $this->setTableScoreGroup($schema);
        $this->setTableScoreGroupGradeTypeList($schema, $tblGradeType, $tblScoreGroup);
        $tblScoreCondition = $this->setTableScoreCondition($schema);
        $this->setTableScoreConditionGradeTypeList($schema, $tblGradeType, $tblScoreCondition);
        $this->setTableScoreConditionGroupList($schema, $tblScoreCondition, $tblScoreGroup);
        $this->setTableScoreConditionGroupRequirement($schema, $tblScoreCondition, $tblScoreGroup);
        $tblScoreRule = $this->setTableScoreRule($schema);
        $this->setTableScoreRuleConditionList($schema, $tblScoreRule, $tblScoreCondition);
        $this->setTableScoreRuleSubject($schema, $tblScoreRule);
        $this->setTableScoreRuleSubjectDivisionCourse($schema, $tblScoreRule);

        // MinimumGradeCount
        $tblMinimumGradeCount = $this->setTableMinimumGradeCount($schema, $tblGradeType);
        $this->setTableMinimumGradeCountLevelLink($schema, $tblMinimumGradeCount);
        $this->setTableMinimumGradeCountSubjectLink($schema, $tblMinimumGradeCount);

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

    private function setTableGradeType(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblGraduationGradeType');
        $this->createColumn($table, 'Code');
        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'Description');
        $this->createColumn($table, 'IsTypeBehavior', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'IsHighlighted', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'IsPartGrade', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'IsActive', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

    private function setTableTest(Schema &$schema, Table $tblGradeType): Table
    {
        $table = $this->createTable($schema, 'tblGraduationTest');

        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        // Halbjahr wir über das Datum ermittelt
//        $this->createColumn($table, 'Period', self::FIELD_TYPE_SMALLINT);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createForeignKey($table, $tblGradeType);

        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($table, 'FinishDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($table, 'CorrectionDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($table, 'ReturnDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($table, 'IsContinues', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'Description');

        $this->createColumn($table, 'serviceTblPersonTeacher', self::FIELD_TYPE_BIGINT, true);

        return $table;
    }

    private function setTableTestGrade(Schema &$schema, Table $tblTest)
    {
        $table = $this->createTable($schema, 'tblGraduationTestGrade');

        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createForeignKey($table, $tblTest);

        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME, true);

        $this->createColumn($table, 'Grade', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'Comment', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'PublicComment', self::FIELD_TYPE_STRING, true);

        $this->createColumn($table, 'serviceTblPersonTeacher', self::FIELD_TYPE_BIGINT, true);
    }

    private function setTableTestCourseLink(Schema &$schema, Table $tblTest)
    {
        $table = $this->createTable($schema, 'tblGraduationTestCourseLink');
        $this->createForeignKey($table, $tblTest);
        $this->createColumn($table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT);
    }

    private function setTableTestStudentLink(Schema &$schema, Table $tblTest)
    {
        $table = $this->createTable($schema, 'tblGraduationTestStudentLink');
        $this->createForeignKey($table, $tblTest);
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
    }

    private function setTableTask(Schema &$schema, Table $tblScoreType): Table
    {
        $table = $this->createTable($schema, 'tblGraduationTask');

        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'IsTypeBehavior', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'Name');

        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($table, 'FromDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($table, 'ToDate', self::FIELD_TYPE_DATETIME, true);

        // Halbjahr wir über das Datum ermittelt, und ob es Kurssystem ist
//        $this->createColumn($table, 'Period', self::FIELD_TYPE_SMALLINT);
        $this->createColumn($table, 'IsAllYears', self::FIELD_TYPE_BOOLEAN);

        $this->createForeignKey($table, $tblScoreType, true);

        return $table;
    }

    private function setTableTaskGrade(Schema &$schema, Table $tblTask, Table $tblGradeType, Table $tblGradeText)
    {
        $table = $this->createTable($schema, 'tblGraduationTaskGrade');

        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createForeignKey($table, $tblTask);
        $this->createForeignKey($table, $tblGradeType, true);

        $this->createColumn($table, 'Grade', self::FIELD_TYPE_STRING, true);
        $this->createForeignKey($table, $tblGradeText, true);
        $this->createColumn($table, 'Comment', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'serviceTblPersonTeacher', self::FIELD_TYPE_BIGINT, true);

        $this->createIndex($table, array('tblGraduationTask', 'serviceTblPerson'), false);
//        $this->createIndex($table, array('tblGraduationTask', 'serviceTblPerson', 'serviceTblSubject'), false);
//        $this->createIndex($table, array('tblGraduationTask', 'serviceTblPerson', 'serviceTblSubject', 'tblGraduationGradeType'), false);
    }

    private function setTableTaskCourseLink(Schema &$schema, Table $tblTask)
    {
        $table = $this->createTable($schema, 'tblGraduationTaskCourseLink');
        $this->createForeignKey($table, $tblTask);
        $this->createColumn($table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT);
        // todo Stichtagsnotenauftrag für HS in Klasse 9, 2 Aufträge für Klasse 9 OS wären schlecht für Zeugnisauftrag
    }

    private function setTableTaskGradeTypeLink(Schema &$schema, Table $tblTask, Table $tblGradeType)
    {
        $table = $this->createTable($schema, 'tblGraduationTaskGradeTypeLink');
        $this->createForeignKey($table, $tblTask);
        $this->createForeignKey($table, $tblGradeType);
    }

    private function setTableScoreType(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblGraduationScoreType');

        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'Identifier');
        $this->createColumn($table, 'Pattern');

        $this->createIndex($table, array('Identifier'));

        return $table;
    }

    private function setTableGradeText(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblGraduationGradeText');

        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'ShortName');
        $this->createColumn($table, 'Identifier');

        $this->createIndex($table, array('Identifier'));

        return $table;
    }

    private function setTableScoreTypeSubject(Schema &$schema, Table $tblScoreType)
    {
        $table = $this->createTable($schema, 'tblGraduationScoreTypeSubject');
        $this->createColumn($table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Level', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createForeignKey($table, $tblScoreType);
    }

    private function setTableScoreGroup(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblGraduationScoreGroup');
        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'Multiplier');
        $this->createColumn($table, 'IsEveryGradeASingleGroup', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'IsActive', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

    private function setTableScoreGroupGradeTypeList(Schema &$schema, Table $tblGradeType, Table $tblScoreGroup)
    {
        $table = $this->createTable($schema, 'tblGraduationScoreGroupGradeTypeList');
        $this->createColumn($table, 'Multiplier');
        $this->createForeignKey($table, $tblGradeType);
        $this->createForeignKey($table, $tblScoreGroup);
    }

    private function setTableScoreCondition(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblGraduationScoreCondition');
        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'Priority', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'Period', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'IsActive', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

    private function setTableScoreConditionGradeTypeList(Schema &$schema, Table $tblGradeType, Table $tblScoreCondition)
    {
        $table = $this->createTable($schema, 'tblGraduationScoreConditionGradeTypeList');
        $this->createColumn($table, 'Count', self::FIELD_TYPE_INTEGER);
        $this->createForeignKey($table, $tblGradeType);
        $this->createForeignKey($table, $tblScoreCondition);
    }

    private function setTableScoreConditionGroupList(Schema &$schema, Table $tblScoreCondition, Table $tblScoreGroup)
    {
        $table = $this->createTable($schema, 'tblGraduationScoreConditionGroupList');
        $this->createForeignKey($table, $tblScoreCondition);
        $this->createForeignKey($table, $tblScoreGroup);
    }

    private function setTableScoreConditionGroupRequirement(Schema &$schema, Table $tblScoreCondition, Table $tblScoreGroup)
    {
        $table = $this->createTable($schema, 'tblGraduationScoreConditionGroupRequirement');
        $this->createColumn($table, 'Count', self::FIELD_TYPE_INTEGER);
        $this->createForeignKey($table, $tblScoreCondition);
        $this->createForeignKey($table, $tblScoreGroup);
    }

    private function setTableScoreRule(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblGraduationScoreRule');
        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'Description');
        $this->createColumn($table, 'DescriptionForExtern', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'IsActive', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

    private function setTableScoreRuleConditionList(Schema &$schema, Table $tblScoreRule, Table $tblScoreCondition)
    {
        $table = $this->createTable($schema, 'tblGraduationScoreRuleConditionList');
        $this->createForeignKey($table, $tblScoreRule);
        $this->createForeignKey($table, $tblScoreCondition);
    }

    private function setTableScoreRuleSubject(Schema &$schema, Table $tblScoreRule)
    {
        $table = $this->createTable($schema, 'tblGraduationScoreRuleSubject');
        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Level', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createForeignKey($table, $tblScoreRule);
    }

    private function setTableScoreRuleSubjectDivisionCourse(Schema &$schema, Table $tblScoreRule)
    {
        $table = $this->createTable($schema, 'tblGraduationScoreRuleSubjectDivisionCourse');
        $this->createColumn($table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createForeignKey($table, $tblScoreRule);
    }

    private function setTableMinimumGradeCount(Schema $schema, Table $tblGradeType): Table
    {
        $table = $this->createTable($schema, 'tblGraduationMinimumGradeCount');
        $this->createColumn($table, 'Count', self::FIELD_TYPE_INTEGER);
        $this->createForeignKey($table, $tblGradeType, true);
        $this->createColumn($table, 'Period', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'Highlighted', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'Course', self::FIELD_TYPE_INTEGER);

        return $table;
    }

    private function setTableMinimumGradeCountLevelLink(Schema $schema, Table $tblMinimumGradeCount)
    {
        $table = $this->createTable($schema, 'tblGraduationMinimumGradeCountLevelLink');
        $this->createForeignKey($table, $tblMinimumGradeCount);
        $this->createColumn($table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Level', self::FIELD_TYPE_INTEGER);
    }

    private function setTableMinimumGradeCountSubjectLink(Schema $schema, Table $tblMinimumGradeCount)
    {
        $table = $this->createTable($schema, 'tblGraduationMinimumGradeCountSubjectLink');
        $this->createForeignKey($table, $tblMinimumGradeCount);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
    }
}