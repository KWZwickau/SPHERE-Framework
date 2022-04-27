<?php

namespace SPHERE\Application\Education\Lesson\DivisionCourse\Service;

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
        $Schema = clone $this->getConnection()->getSchema();
        $tblType = $this->setTableDivisionCourseType($Schema);
        $tblDivisionCourse = $this->setTableDivisionCourse($Schema, $tblType);
        $tblMemberType = $this->setTableDivisionCourseMemberType($Schema);
        $this->setTableDivisionCourseMember($Schema, $tblDivisionCourse, $tblMemberType);
        $this->setTableTeacherLectureship($Schema);
        $this->setTableStudentSubject($Schema);
        $this->setTableStudentEducation($Schema);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        return $this->getConnection()->getProtocol($Simulate);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableDivisionCourseType(Schema &$Schema): Table
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonDivisionCourseType');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Identifier', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblType
     *
     * @return Table
     */
    private function setTableDivisionCourse(Schema &$Schema, Table $tblType): Table
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonDivisionCourse');

        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Description', self::FIELD_TYPE_STRING);

        $this->createForeignKey($table, $tblType);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableDivisionCourseMemberType(Schema &$Schema): Table
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonDivisionCourseMemberType');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Identifier', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDivisionCourse
     * @param Table $tblMemberType
     */
    private function setTableDivisionCourseMember(Schema &$Schema, Table $tblDivisionCourse,Table $tblMemberType)
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonDivisionCourseMember');

        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Description', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'SortOrder', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'LeaveDate', self::FIELD_TYPE_DATETIME, true);

        $this->createForeignKey($table, $tblDivisionCourse);
        $this->createForeignKey($table, $tblMemberType);
    }

    /**
     * @param Schema $Schema
     */
    private function setTableTeacherLectureship(Schema &$Schema)
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonTeacherLectureship');

        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);

        // bei Foreign-Key kann kein Spaltenname angegeben werden
        $this->createColumn($table, 'tblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'tblCoreGroup', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'tblTeachingGroup', self::FIELD_TYPE_BIGINT, true);

        $this->createColumn($table, 'FromDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($table, 'ToDate', self::FIELD_TYPE_DATETIME, true);

//        $this->createIndex($table, array(TblTeacherLectureship::ATTR_SERVICE_TBL_PERSON, TblTeacherLectureship::ATTR_SERVICE_TBL_YEAR));
    }

    /**
     * @param Schema $Schema
     */
    private function setTableStudentSubject(Schema &$Schema)
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonStudentSubject');

        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'IsAdvancedCourse', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'LeaveDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($table, 'serviceTblPeriod', self::FIELD_TYPE_BIGINT, true);
    }

    /**
     * @param Schema $Schema
     */
    private function setTableStudentEducation(Schema &$Schema)
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonStudentEducation');

        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);

        $this->createColumn($table, 'serviceTblCompany', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'Level', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'serviceTblCourse', self::FIELD_TYPE_BIGINT, true);

        // bei Foreign-Key kann kein Spaltenname angegeben werden
        $this->createColumn($table, 'tblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'tblCoreGroup', self::FIELD_TYPE_BIGINT, true);

        $this->createColumn($table, 'CoreGroupSortOrder', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'DivisionSortOrder', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'LeaveDate', self::FIELD_TYPE_DATETIME, true);
    }
}