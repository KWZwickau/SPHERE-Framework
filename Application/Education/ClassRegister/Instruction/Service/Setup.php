<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

class Setup extends AbstractSetup
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
        $tblInstruction = $this->setTableInstruction($Schema);
        $tblInstructionItem = $this->setTableInstructionItem($Schema, $tblInstruction);
        $this->setTableInstructionItemStudent($Schema, $tblInstructionItem);

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
    private function setTableInstruction(Schema &$Schema): Table
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterInstruction');

        $this->createColumn($Table, 'Subject', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Content', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblInstruction
     *
     * @return Table
     */
    private function setTableInstructionItem(Schema &$Schema, Table $tblInstruction): Table
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterInstructionItem');

        $this->createColumn($Table, 'serviceTblDivision', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblGroup', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblYear', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Content', self::FIELD_TYPE_TEXT);

        $this->createForeignKey($Table, $tblInstruction);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblInstructionItem
     */
    private function setTableInstructionItemStudent(Schema &$Schema, Table $tblInstructionItem)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterInstructionItemStudent');

        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);

        $this->createForeignKey($Table, $tblInstructionItem);
    }
}