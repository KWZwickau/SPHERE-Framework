<?php

namespace SPHERE\Application\Transfer\Education\Service;

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
        $tblImport = $this->setTableImport($schema);
        $this->setTableImportLectureship($schema, $tblImport);
        $this->setTableImportMapping($schema);
        $tblImportStudent = $this->setTableImportStudent($schema, $tblImport);
        $this->setTableImportStudentCourse($schema, $tblImportStudent);

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
    public function setTableImport(Schema &$schema): Table
    {
        $table = $this->getConnection()->createTable($schema, 'tblImport');

        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'ExternSoftwareName');
        $this->createColumn($table, 'TypeIdentifier');
        $this->createColumn($table, 'FileName');

        return $table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblImport
     */
    public function setTableImportLectureship(Schema &$schema, Table $tblImport)
    {
        $table = $this->getConnection()->createTable($schema, 'tblImportLectureship');

        $this->createForeignKey($table, $tblImport);

        $this->createColumn($table, 'TeacherAcronym');
        $this->createColumn($table, 'DivisionName');
        $this->createColumn($table, 'SubjectAcronym');
        $this->createColumn($table, 'SubjectGroup');
    }

    /**
     * @param Schema $schema
     */
    public function setTableImportMapping(Schema &$schema)
    {
        $table = $this->getConnection()->createTable($schema, 'tblImportMapping');

        $this->createColumn($table, 'Type');
        $this->createColumn($table, 'Original');
        $this->createColumn($table, 'Mapping');
    }

    /**
     * @param Schema $schema
     * @param Table $tblImport
     *
     * @return Table
     */
    public function setTableImportStudent(Schema &$schema, Table $tblImport): Table
    {
        $table = $this->getConnection()->createTable($schema, 'tblImportStudent');

        $this->createForeignKey($table, $tblImport);

        $this->createColumn($table, 'FirstName');
        $this->createColumn($table, 'LastName');
        $this->createColumn($table, 'Birthday', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($table, 'GenderAcronym');
        $this->createColumn($table, 'DivisionName');
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);

        return $table;
    }

    /**
     * @param Schema $schema
     * @param Table $tblImportStudent
     */
    public function setTableImportStudentCourse(Schema &$schema, Table $tblImportStudent)
    {
        $table = $this->getConnection()->createTable($schema, 'tblImportStudentCourse');

        $this->createForeignKey($table, $tblImportStudent);

        $this->createColumn($table, 'SubjectAcronym');
        $this->createColumn($table, 'CourseNumber');
        $this->createColumn($table, 'CourseName');
    }
}