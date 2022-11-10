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
        $this->setTableTaskGrade($schema, $tblTask, $tblGradeText);
        $this->setTableTaskCourseLink($schema, $tblTask);

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

    /**
     * @param Schema $schema
     * @param Table $tblGradeType
     *
     * @return Table
     */
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

        return $table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblTest
     */
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

    /**
     * @param Schema $schema
     * @param Table $tblTest
     */
    private function setTableTestCourseLink(Schema &$schema, Table $tblTest)
    {
        $table = $this->createTable($schema, 'tblGraduationTestCourseLink');
        $this->createForeignKey($table, $tblTest);
        $this->createColumn($table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT);
    }

    /**
     * @param Schema $schema
     * @param Table $tblTest
     */
    private function setTableTestStudentLink(Schema &$schema, Table $tblTest)
    {
        $table = $this->createTable($schema, 'tblGraduationTestStudentLink');
        $this->createForeignKey($table, $tblTest);
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
    }

    /**
     * @param Schema $schema
     * @param Table $tblScoreType
     *
     * @return Table
     */
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

    /**
     * @param Schema $schema
     * @param Table $tblTask
     * @param Table $tblGradeText
     */
    private function setTableTaskGrade(Schema &$schema, Table $tblTask, Table $tblGradeText)
    {
        $table = $this->createTable($schema, 'tblGraduationTaskGrade');

        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createForeignKey($table, $tblTask);

        $this->createColumn($table, 'Grade', self::FIELD_TYPE_STRING, true);
        $this->createForeignKey($table, $tblGradeText, true);
        $this->createColumn($table, 'Comment', self::FIELD_TYPE_STRING, true);
        $this->createColumn($table, 'serviceTblPersonTeacher', self::FIELD_TYPE_BIGINT, true);
    }

    /**
     * @param Schema $schema
     * @param Table $tblTask
     */
    private function setTableTaskCourseLink(Schema &$schema, Table $tblTask)
    {
        $table = $this->createTable($schema, 'tblGraduationTaskCourseLink');
        $this->createForeignKey($table, $tblTask);
        $this->createColumn($table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT);
        // todo Stichtagsnotenauftrag für HS in Klasse 9, 2 Aufträge für Klasse 9 OS wären schlecht für Zeugnisauftrag
    }

    /**
     * @param Schema $schema
     *
     * @return Table
     */
    private function setTableScoreType(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblGraduationScoreType');

        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'Identifier');
        $this->createColumn($table, 'Pattern');

        return $table;
    }

    /**
     * @param Schema $schema
     *
     * @return Table
     */
    private function setTableGradeText(Schema &$schema): Table
    {
        $table = $this->createTable($schema, 'tblGraduationGradeText');

        $this->createColumn($table, 'Name');
        $this->createColumn($table, 'Identifier');

        $this->createIndex($table, array('Identifier'));

        return $table;
    }
}