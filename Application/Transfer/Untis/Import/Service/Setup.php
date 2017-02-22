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
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true)
    {

        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableUntisImportLectureship($Schema);

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
}