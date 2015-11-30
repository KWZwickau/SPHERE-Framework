<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 02.11.2015
 * Time: 10:32
 */

namespace SPHERE\Application\Education\Graduation\Gradebook\Service;


use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * @package SPHERE\Application\Education\Graduation\Gradebook\Service
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
        $tblGradeType = $this->setTableGradeType($Schema);
        $tblTest = $this->setTableTest($Schema, $tblGradeType);
        $this->setTableGrade($Schema, $tblGradeType, $tblTest);

        $tblScoreRule = $this->setTableScoreRule($Schema);
        $tblScoreCondition = $this->setTableScoreCondition($Schema);
        $tblScoreGroup = $this->setTableScoreGroup($Schema);
        $this->setTableScoreRuleConditionList($Schema, $tblScoreRule, $tblScoreCondition);
        $this->setTableScoreConditionGradeTypeList($Schema, $tblGradeType, $tblScoreCondition);
        $this->setTableScoreConditionGroupList($Schema, $tblScoreCondition, $tblScoreGroup);
        $this->setTableScoreGroupGradeTypeList($Schema, $tblGradeType, $tblScoreGroup);

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
     * @return Table
     */
    private function setTableGradeType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGradeType');
        if (!$this->getConnection()->hasColumn('tblGradeType', 'Code')) {
            $Table->addColumn('Code', 'string');
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

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     *
     * @return Table
     */
    private function setTableTest(Schema &$Schema, Table $tblGradeType)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblTest');
        if (!$this->getConnection()->hasColumn('tblTest', 'Date')) {
            $Table->addColumn('Date', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'CorrectionDate')) {
            $Table->addColumn('CorrectionDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'ReturnDate')) {
            $Table->addColumn('ReturnDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblPeriod')) {
            $Table->addColumn('serviceTblPeriod', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblTest', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     * @param Table $tblTest
     *
     * @return Table
     */
    private function setTableGrade(Schema &$Schema, Table $tblGradeType, Table $tblTest)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblGrade');
        if (!$this->getConnection()->hasColumn('tblGrade', 'Grade')) {
            $Table->addColumn('Grade', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'Comment')) {
            $Table->addColumn('Comment', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblPeriod')) {
            $Table->addColumn('serviceTblPeriod', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblGrade', 'serviceTblDivision')) {
            $Table->addColumn('serviceTblDivision', 'bigint', array('notnull' => false));
        }

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);
        $this->getConnection()->addForeignKey($Table, $tblTest, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableScoreRule(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreRule');
        if (!$this->getConnection()->hasColumn('tblScoreRule', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreRule', 'Description')) {
            $Table->addColumn('Description', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableScoreCondition(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreCondition');
        if (!$this->getConnection()->hasColumn('tblScoreCondition', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreCondition', 'Round')) {
            $Table->addColumn('Round', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreCondition', 'Priority')) {
            $Table->addColumn('Priority', 'integer');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableScoreGroup(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreGroup');
        if (!$this->getConnection()->hasColumn('tblScoreGroup', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreGroup', 'Round')) {
            $Table->addColumn('Round', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblScoreGroup', 'Multiplier')) {
            $Table->addColumn('Multiplier', 'string');
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblScoreRule
     * @param Table $tblScoreCondition
     * @return Table
     */
    private function setTableScoreRuleConditionList(Schema &$Schema, Table $tblScoreRule, Table $tblScoreCondition)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreRuleConditionList');

        $this->getConnection()->addForeignKey($Table, $tblScoreRule, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreCondition, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     * @param Table $tblScoreCondition
     * @return Table
     */
    private function setTableScoreConditionGradeTypeList(Schema &$Schema, Table $tblGradeType, Table $tblScoreCondition)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreConditionGradeTypeList');

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreCondition, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblScoreCondition
     * @param Table $tblScoreGroup
     * @return Table
     */
    private function setTableScoreConditionGroupList(Schema &$Schema, Table $tblScoreCondition, Table $tblScoreGroup)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreConditionGroupList');

        $this->getConnection()->addForeignKey($Table, $tblScoreCondition, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreGroup, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblGradeType
     * @param Table $tblScoreGroup
     * @return Table
     */
    private function setTableScoreGroupGradeTypeList(Schema &$Schema, Table $tblGradeType, Table $tblScoreGroup)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblScoreGroupGradeTypeList');
        if (!$this->getConnection()->hasColumn('tblScoreGroupGradeTypeList', 'Multiplier')) {
            $Table->addColumn('Multiplier', 'string');
        }

        $this->getConnection()->addForeignKey($Table, $tblGradeType, true);
        $this->getConnection()->addForeignKey($Table, $tblScoreGroup, true);

        return $Table;
    }
}