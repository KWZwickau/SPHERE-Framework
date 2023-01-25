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
        $this->setTableDivisionCourseLink($Schema, $tblDivisionCourse);
        $tblMemberType = $this->setTableDivisionCourseMemberType($Schema);
        $this->setTableDivisionCourseMember($Schema, $tblDivisionCourse, $tblMemberType);
        $this->setTableTeacherLectureship($Schema, $tblDivisionCourse);
        $this->setTableStudentSubject($Schema, $tblDivisionCourse);
        $this->setTableStudentEducation($Schema);
        $tblSubjectTable = $this->setTableSubjectTable($Schema);
        $this->setTableSubjectTableLink($Schema, $tblSubjectTable);

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
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'IsShownInPersonData', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'IsReporting', self::FIELD_TYPE_BOOLEAN);
//        $this->createColumn($table, 'IsUcs', self::FIELD_TYPE_BOOLEAN);

        // todo kann nach der vollständigen Migration der Alt-Daten gedropt werden
        $this->createColumn($table, 'MigrateGroupId', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'MigrateSekCourse', self::FIELD_TYPE_STRING, true);

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
     * @param Table $tblDivisionCourse
     */
    private function setTableTeacherLectureship(Schema &$Schema, Table $tblDivisionCourse)
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonTeacherLectureship');

        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'GroupName', self::FIELD_TYPE_STRING);


        // bei Foreign-Key kann kein Spaltenname angegeben werden
//        $this->createColumn($table, 'tblDivision', self::FIELD_TYPE_BIGINT, true);
//        $this->createColumn($table, 'tblCoreGroup', self::FIELD_TYPE_BIGINT, true);
//        $this->createColumn($table, 'tblTeachingGroup', self::FIELD_TYPE_BIGINT, true);

//        $this->createColumn($table, 'FromDate', self::FIELD_TYPE_DATETIME, true);
//        $this->createColumn($table, 'ToDate', self::FIELD_TYPE_DATETIME, true);

        $this->createForeignKey($table, $tblDivisionCourse);

//        $this->createIndex($table, array(TblTeacherLectureship::ATTR_SERVICE_TBL_PERSON, TblTeacherLectureship::ATTR_SERVICE_TBL_YEAR));
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDivisionCourse
     */
    private function setTableStudentSubject(Schema &$Schema, Table $tblDivisionCourse)
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonStudentSubject');

        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'HasGrading', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'serviceTblSubjectTable', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'PeriodIdentifier', self::FIELD_TYPE_STRING, true);

        $this->createForeignKey($table, $tblDivisionCourse, true);
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
        $this->createColumn($table, 'DivisionSortOrder', self::FIELD_TYPE_INTEGER, true);
        // bei Foreign-Key kann kein Spaltenname angegeben werden
        $this->createColumn($table, 'tblCoreGroup', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'CoreGroupSortOrder', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($table, 'LeaveDate', self::FIELD_TYPE_DATETIME, true);
    }

    /**
     * @param Schema $Schema
     * @param Table $tblDivisionCourse
     */
    private function setTableDivisionCourseLink(Schema &$Schema, Table $tblDivisionCourse)
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonDivisionCourseLink');

        $this->createColumn($table, 'tblSubLessonDivisionCourse', self::FIELD_TYPE_BIGINT);

        $this->createForeignKey($table, $tblDivisionCourse);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSubjectTable(Schema &$Schema): Table
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonSubjectTable');

        $this->createColumn($table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Level', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'TypeName', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Ranking', self::FIELD_TYPE_INTEGER);
        $this->createColumn($table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'StudentMetaIdentifier', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'HasGrading', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'HoursPerWeek', self::FIELD_TYPE_INTEGER, true);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblSubjectTableLink
     */
    private function setTableSubjectTableLink(Schema &$Schema, Table $tblSubjectTableLink)
    {
        $table = $this->getConnection()->createTable($Schema, 'tblLessonSubjectTableLink');

        $this->createColumn($table, 'LinkId', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'MinCount', self::FIELD_TYPE_INTEGER);

        $this->createForeignKey($table, $tblSubjectTableLink);
    }
}