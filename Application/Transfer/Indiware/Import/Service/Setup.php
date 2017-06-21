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
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableIndiwareImportLectureship($Schema);
        $this->setTableIndiwareImportStudentCourse($Schema);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        $this->getConnection()->setMigration($Schema, $Simulate);
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
    private function setTableIndiwareImportStudentCourse(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblIndiwareImportStudentCourse');
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'serviceTblYear')) {
            $Table->addColumn('serviceTblYear', 'bigint');
        }
//        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'FirstName')) {
//            $Table->addColumn('FirstName', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'LastName')) {
//            $Table->addColumn('LastName', 'string');
//        }
//        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'Birthday')) {
//            $Table->addColumn('Birthday', self::FIELD_TYPE_DATETIME, array('notnull' => false));
//        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'SubjectName')) {
            $Table->addColumn('SubjectName', 'string');
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
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'serviceTblAccount')) {
            $Table->addColumn('serviceTblAccount', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblIndiwareImportStudentCourse', 'IsIgnore')) {
            $Table->addColumn('IsIgnore', 'boolean');
        }

        return $Table;
    }
}