<?php

namespace SPHERE\Application\Transfer\Indiware\Import\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Transfer\Indiware\Import\Service
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
        $this->setTableIndiwareImportLectureship($Schema);
        $tblIndiwareImportStudent = $this->setTableIndiwareImportStudent($Schema);
        $this->setTableIndiwareImportStudentCourse($Schema, $tblIndiwareImportStudent);

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
     * @return Table $tblIndiwareImportLectureship
     *
     * @return Table
     */
    private function setTableIndiwareImportLectureship(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblIndiwareImportLectureship');
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'serviceTblYear')) {
            $Table->addColumn('serviceTblYear', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'SchoolClass')) {
            $Table->addColumn('SchoolClass', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'TeacherAcronym')) {
            $Table->addColumn('TeacherAcronym', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'serviceTblTeacher')) {
            $Table->addColumn('serviceTblTeacher', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'SubjectName')) {
            $Table->addColumn('SubjectName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'SubjectGroupName')) {
            $Table->addColumn('SubjectGroupName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'SubjectGroup')) {
            $Table->addColumn('SubjectGroup', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportLectureship', 'IsIgnore')) {
            $Table->addColumn('IsIgnore', 'boolean');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table $tblIndiwareImportStudentCourse
     *
     * @return Table
     */
    private function setTableIndiwareImportStudent(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblIndiwareImportStudent');
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudent', 'serviceTblYear')) {
            $Table->addColumn('serviceTblYear', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudent', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudent', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudent', 'Level')) {
            $Table->addColumn('Level', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudent', 'IsIgnore')) {
            $Table->addColumn('IsIgnore', 'boolean');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table $tblIndiwareImportStudentCourse
     *
     * @return Table
     */
    private function setTableIndiwareImportStudentCourse(Schema &$Schema, $tblIndiwareImportStudent)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblIndiwareImportStudentCourse');

        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'SubjectName')) {
            $Table->addColumn('SubjectName', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'SubjectGroup')) {
            $Table->addColumn('SubjectGroup', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'CourseNumber')) {
            $Table->addColumn('CourseNumber', 'integer');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'IsIntensiveCourse')) {
            $Table->addColumn('IsIntensiveCourse', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'IsIgnoreCourse')) {
            $Table->addColumn('IsIgnoreCourse', 'boolean');
        }
        $this->createForeignKey($Table, $tblIndiwareImportStudent);

        return $Table;
    }
}