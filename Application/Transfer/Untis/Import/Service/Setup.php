<?php
namespace SPHERE\Application\Transfer\Untis\Import\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Transfer\Untis\Import\Service
 */
class Setup extends AbstractSetup
{

    /**
     * @param bool $Simulate
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableUntisImportLectureship($Schema);
        $tblUntisImportStudent = $this->setTableUntisImportStudent($Schema);
        $this->setTableUntisImportStudentCourse($Schema, $tblUntisImportStudent);

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
     * @return Table $tblSchoolAccount
     *
     * @return Table
     */
    private function setTableUntisImportLectureship(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblUntisImportLectureship');
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'serviceTblYear')) {
            $Table->addColumn('serviceTblYear', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'SchoolClass')) {
            $Table->addColumn('SchoolClass', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'TeacherAcronym')) {
            $Table->addColumn('TeacherAcronym', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'serviceTblTeacher')) {
            $Table->addColumn('serviceTblTeacher', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'SubjectName')) {
            $Table->addColumn('SubjectName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'SubjectGroupName')) {
            $Table->addColumn('SubjectGroupName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'SubjectGroup')) {
            $Table->addColumn('SubjectGroup', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblUntisImportLectureship', 'IsIgnore')) {
            $Table->addColumn('IsIgnore', 'boolean');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableUntisImportStudent(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblUntisImportStudent');
        $this->createColumn($Table, 'serviceTblYear', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblAccount', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Level', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IsIgnore', self::FIELD_TYPE_BOOLEAN);
//        $this->getConnection()->addForeignKey($Table, $tblItemType);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblUntisImportStudent
     *
     * @return Table
     */
    private function setTableUntisImportStudentCourse(Schema &$Schema, $tblUntisImportStudent)
    {

        $Table = $this->createTable($Schema, 'tblUntisImportStudentCourse');
        $this->createColumn($Table, 'SubjectName', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'SubjectGroup', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'CourseNumber', self::FIELD_TYPE_INTEGER);
        $this->createColumn($Table, 'IsIntensiveCourse', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'IsIgnoreCourse', self::FIELD_TYPE_BOOLEAN);
        $this->getConnection()->addForeignKey($Table, $tblUntisImportStudent);

        return $Table;
    }
}