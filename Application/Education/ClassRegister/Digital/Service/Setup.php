<?php

namespace SPHERE\Application\Education\ClassRegister\Digital\Service;

use Doctrine\DBAL\Schema\Schema;
use SPHERE\Application\Education\ClassRegister\Digital\Service\Entity\TblCourseContent;
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
        $this->setTableLessonContent($Schema);
        $this->setTableLessonWeek($Schema);
        $this->setTableCourseContent($Schema);

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
     */
    private function setTableLessonContent(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterLessonContent');

        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblGroup', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblYear', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubstituteSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'IsCanceled', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Lesson', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'Content', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Homework', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Room', self::FIELD_TYPE_STRING);

        // todo anlegen der Indexe nach dem Umbau von Klassen und Gruppen
//        $this->createIndex($Table, array(TblLessonContent::ATTR_DATE, TblLessonContent::ATTR_LESSON, TblLessonContent::ATTR_SERVICE_TBL_DIVISION), false);
//        $this->createIndex($Table, array(TblLessonContent::ATTR_DATE, TblLessonContent::ATTR_SERVICE_TBL_DIVISION), false);
    }

    /**
     * @param Schema $Schema
     */
    private function setTableLessonWeek(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterLessonWeek');

        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblGroup', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblYear', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Remark', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'DateDivisionTeacher', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'serviceTblPersonDivisionTeacher', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'DateHeadmaster', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'serviceTblPersonHeadmaster', self::FIELD_TYPE_BIGINT, true);

        // todo anlegen der Indexe nach dem Umbau von Klassen und Gruppen
//        $this->createIndex($Table, array(TblLessonWeek::ATTR_DATE, TblLessonWeek::ATTR_SERVICE_TBL_DIVISION), false);
    }

    /**
     * @param Schema $Schema
     */
    private function setTableCourseContent(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterCourseContent');

        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubjectGroup', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Lesson', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'Content', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Homework', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Remark', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'Room', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IsDoubleLesson', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'DateHeadmaster', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'serviceTblPersonHeadmaster', self::FIELD_TYPE_BIGINT, true);

        $this->createIndex($Table, array(TblCourseContent::ATTR_SERVICE_TBL_DIVISION, TblCourseContent::ATTR_SERVICE_TBL_SUBJECT,
            TblCourseContent::ATTR_SERVICE_TBL_SUBJECT_GROUP), false);
    }
}