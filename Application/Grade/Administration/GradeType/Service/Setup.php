<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 02.11.2015
 * Time: 10:32
 */

namespace SPHERE\Application\Grade\Administration\GradeType\Service;


use Doctrine\DBAL\Schema\Schema;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 * @package SPHERE\Application\Grade\Administration\GradeType\Service
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
        $this->setTableGradeTypes($Schema);
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
    private function setTableGradeTypes(Schema &$Schema)
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
        if (!$this->getConnection()->hasColumn('tblGradeType', 'IsActive')) {
            $Table->addColumn('IsActive', 'boolean');
        }
        if (!$this->getConnection()->hasColumn('tblGradeType', 'IsHighlighted')) {
            $Table->addColumn('IsHighlighted', 'boolean');
        }

        return $Table;
    }
}