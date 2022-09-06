<?php

namespace SPHERE\Application\Education\School\Type\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\View;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\School\Type\Service
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
        $tblCategory = $this->setTableCategory($Schema);
        $this->setTableType($Schema, $tblCategory);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        $this->getConnection()->createView(
            ( new View($this->getConnection(), 'viewSchoolType') )
                ->addLink(new TblType(), 'Id')
        );

        return $this->getConnection()->getProtocol($Simulate);
    }


    /**
     * @param Schema $Schema
     * @param Table $tblCategory
     *
     * @return Table
     */
    private function setTableType(Schema &$Schema, Table $tblCategory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblType');

        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'ShortName', self::FIELD_TYPE_STRING, false, '');
        $this->createColumn($Table, 'IsBasic', self::FIELD_TYPE_BOOLEAN, false, 0);

        $this->createForeignKey($Table, $tblCategory, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableCategory(Schema &$Schema)
    {
        $table = $this->createTable($Schema, 'tblCategory');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Identifier', self::FIELD_TYPE_STRING);

        return $table;
    }
}
