<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblCourseContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblForgotten;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblFullTimeContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonContent;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblLessonWeek;
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
        $Schema = clone $this->getConnection()->getSchema();
        $tblLessonContent = $this->setTableLessonContent($Schema);
        $this->setTableLessonContentLink($Schema, $tblLessonContent);
        $this->setTableLessonWeek($Schema);
        $tblCourseContent = $this->setTableCourseContent($Schema);
        $this->setTableFullTimeContent($Schema);
        $tblForgotten = $this->setTableForgotten($Schema, $tblLessonContent, $tblCourseContent);
        $this->setTableForgottenStudent($Schema, $tblForgotten);

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
    private function setTableLessonContent(Schema &$Schema): Table
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterLessonContent');

        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        // todo serviceTblGroup und serviceTblYear löschen nach Migration
        $this->createColumn($Table, 'serviceTblGroup', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblYear', self::FIELD_TYPE_BIGINT, true);
        //
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubstituteSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'IsCanceled', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Lesson', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'Content', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Homework', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'DueDateHomework', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'Room', self::FIELD_TYPE_STRING);

        $this->createIndex($Table, array(TblLessonContent::ATTR_DATE, TblLessonContent::ATTR_LESSON, TblLessonContent::ATTR_SERVICE_TBL_DIVISION_COURSE), false);
        $this->createIndex($Table, array(TblLessonContent::ATTR_DATE, TblLessonContent::ATTR_SERVICE_TBL_DIVISION_COURSE), false);
        $this->createIndex($Table, array('serviceTblSubject'), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblLessonContent
     */
    private function setTableLessonContentLink(Schema &$Schema, Table $tblLessonContent)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterLessonContentLink');
        $this->createColumn($Table, 'LinkId', self::FIELD_TYPE_BIGINT);

        $this->getConnection()->addForeignKey($Table, $tblLessonContent, true);
    }

    /**
     * @param Schema $Schema
     */
    private function setTableLessonWeek(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterLessonWeek');

        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        // todo serviceTblGroup und serviceTblYear löschen nach Migration
        $this->createColumn($Table, 'serviceTblGroup', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblYear', self::FIELD_TYPE_BIGINT, true);
        //
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Remark', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'DateDivisionTeacher', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'serviceTblPersonDivisionTeacher', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'DateHeadmaster', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'serviceTblPersonHeadmaster', self::FIELD_TYPE_BIGINT, true);

        $this->createIndex($Table, array(TblLessonWeek::ATTR_DATE, TblLessonWeek::ATTR_SERVICE_TBL_DIVISION_COURSE), false);
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCourseContent(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterCourseContent');

        $this->createColumn($Table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT, true);
        // todo nach migration droppen
        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubjectGroup', self::FIELD_TYPE_BIGINT, true);
        //
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Lesson', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'Content', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Homework', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'DueDateHomework', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'Remark', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Room');
        $this->createColumn($Table, 'IsDoubleLesson', self::FIELD_TYPE_SMALLINT);
        $this->createColumn($Table, 'DateHeadmaster', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'serviceTblPersonHeadmaster', self::FIELD_TYPE_BIGINT, true);

        $this->createIndex($Table, array(TblCourseContent::ATTR_SERVICE_TBL_DIVISION_COURSE), false);

        // todo nach migration droppen
        if ($this->getConnection()->hasIndex(
            $Table,
            array(TblCourseContent::ATTR_SERVICE_TBL_DIVISION, TblCourseContent::ATTR_SERVICE_TBL_SUBJECT, TblCourseContent::ATTR_SERVICE_TBL_SUBJECT_GROUP)
        )) {
            $this->getConnection()->removeIndex(
                $Table,
                array(TblCourseContent::ATTR_SERVICE_TBL_DIVISION, TblCourseContent::ATTR_SERVICE_TBL_SUBJECT, TblCourseContent::ATTR_SERVICE_TBL_SUBJECT_GROUP)
            );
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     */
    private function setTableFullTimeContent(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterFullTimeContent');

        $this->createColumn($Table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'FromDate', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'ToDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'Content', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);

        $this->createIndex(
            $Table,
            array(
                TblFullTimeContent::ATTR_SERVICE_TBL_DIVISION_COURSE, TblFullTimeContent::ATTR_FROM_DATE, TblFullTimeContent::ATTR_TO_DATE
            ),
            false
        );
    }

    /**
     * @param Schema $Schema
     * @param Table $tblLessonContent
     * @param Table $tblCourseContent
     *
     * @return Table
     */
    private function setTableForgotten(Schema &$Schema, Table $tblLessonContent, Table $tblCourseContent): Table
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterForgotten');

        $this->createColumn($Table, 'serviceTblDivisionCourse', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'IsHomework', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'Remark');

        $this->createForeignKey($Table, $tblLessonContent, true);
        $this->createForeignKey($Table, $tblCourseContent, true);

        $this->createIndex($Table, array(TblForgotten::ATTR_SERVICE_TBL_DIVISION_COURSE, TblForgotten::ATTR_SERVICE_TBL_SUBJECT), false);
        $this->createIndex($Table, array(TblForgotten::ATTR_SERVICE_TBL_DIVISION_COURSE, TblForgotten::ATTR_SERVICE_TBL_SUBJECT, TblForgotten::ATTR_DATE), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblForgotten
     */
    private function setTableForgottenStudent(Schema &$Schema, Table $tblForgotten): void
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterForgottenStudent');

        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);

        $this->createForeignKey($Table, $tblForgotten);
    }
}