<?php

namespace SPHERE\Application\ParentStudentAccess\OnlineContactDetails\Service;

use Doctrine\DBAL\Schema\Schema;
use SPHERE\System\Database\Binding\AbstractSetup;

class Setup extends AbstractSetup
{
    /**
     * @param bool $Simulate
     * @param false $UTF8
     *
     * @return string
     */
    public function setupDatabaseSchema($Simulate = true, $UTF8 = false): string
    {
        /**
         * Table
         */
        $Schema = clone $this->getConnection()->getSchema();
        $this->setTableOnlineContact($Schema);

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
    private function setTableOnlineContact(Schema &$Schema)
    {
        $Table = $this->createTable($Schema, 'tblOnlineContact');
        $this->createColumn($Table, 'ContactType', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblToPerson', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblContact', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'Remark', self::FIELD_TYPE_TEXT);
        $this->createColumn($Table, 'serviceTblPersonCreator', self::FIELD_TYPE_BIGINT);
    }
}