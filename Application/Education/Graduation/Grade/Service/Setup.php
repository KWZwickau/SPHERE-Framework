<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

class Setup  extends AbstractSetup
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

        // todo
//        $tblGradeType = $this->setTableGradeType($Schema);
//        $tblGradeText = $this->setTableGradeText($Schema);
//        $this->setTableGrade($Schema, $tblGradeType, $tblGradeText);

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
    private function setTableGradeType(Schema &$Schema)
    {
        // todo
        $Table = $this->getConnection()->createTable($Schema, 'tblGradeType');
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Code')) {
            $Table->addColumn('Code', 'string');
        }
        $this->getConnection()->removeIndex($Table, array('Code'));
        if (!$this->getConnection()->hasIndex($Table, array('Code', Element::ENTITY_REMOVE))) {
            $Table->addUniqueIndex(array('Code', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'IsHighlighted')) {
            $Table->addColumn('IsHighlighted', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'serviceTblTestType')) {
            $Table->addColumn('serviceTblTestType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'IsActive')) {
            $Table->addColumn('IsActive', 'boolean', array('default' => true));
        }
        $this->createColumn($Table, 'IsPartGrade', self::FIELD_TYPE_BOOLEAN, false, '0');

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     * @param Table $tblGradeText
     *
     * @return Table
     */
    private function setTableGrade(Schema &$Schema, Table $tblGradeType, Table $tblGradeText)
    {
        // todo
        $Table = $this->getConnection()->createTable($Schema, 'tblGrade');
        if (!$this->getConnection()->hasColumn('tblGrade', 'Grade')) {
            $Table->addColumn('Grade', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'Comment')) {
            $Table->addColumn('Comment', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'Trend')) {
            $Table->addColumn('Trend', 'smallint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblSubjectGroup')) {
            $Table->addColumn('serviceTblSubjectGroup', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblPeriod')) {
            $Table->addColumn('serviceTblPeriod', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblTest')) {
            $Table->addColumn('serviceTblTest', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblTestType')) {
            $Table->addColumn('serviceTblTestType', 'bigint', array('notnull' => false));
        }
        if (!$Table->hasColumn('Date')) {
            $Table->addColumn('Date', 'datetime', array('notnull' => false));
        }
        $this->createColumn($Table, 'serviceTblPersonTeacher', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'PublicComment', self::FIELD_TYPE_STRING);

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);
        $this->createForeignKey($Table, $tblGradeText, true);

//        $this->createIndex($Table, array('serviceTblPerson', 'serviceTblTest'), false);
        $this->createIndex($Table, array('serviceTblPerson', 'serviceTblTest'), true);

//        // alten nicht unique index entfernen
//        if (($indexList = $Table->getIndexes())) {
//            foreach ($indexList as $index) {
//                if (!$index->isUnique()) {
//                    $hasPersonColumn = false;
//                    $hasTestColumn = false;
//                    if (($columns = $index->getColumns())) {
//                        foreach ($columns as $column) {
//                            if ($column == 'serviceTblPerson') {
//                                $hasPersonColumn = true;
//                            }
//                            if ($column == 'serviceTblTest') {
//                                $hasTestColumn = true;
//                            }
//                        }
//
//                        if ($hasPersonColumn && $hasTestColumn) {
//                            $Table->dropIndex($index->getName());
//                        }
//                    }
//                }
//            }
//        }
//        $Table->addUniqueIndex(array('serviceTblPerson', 'serviceTblTest'), 'UNIQ_TblGradeServiceTblPersonServiceTblTest');


        $this->createIndex($Table, array('serviceTblDivision', 'serviceTblSubject'), false);
        $this->createIndex($Table, array(TblGrade::ATTR_SERVICE_TBL_TEST, TblGrade::ENTITY_REMOVE), false);

        return $Table;
    }
}