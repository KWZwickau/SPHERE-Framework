<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction\Service;

use Doctrine\DBAL\Schema\Schema;
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
        $this->setTableInstruction($Schema);

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
    private function setTableInstruction(Schema &$Schema)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblClassRegisterInstruction');

        $this->createColumn($Table, 'Subject', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Content', self::FIELD_TYPE_TEXT);
    }
}