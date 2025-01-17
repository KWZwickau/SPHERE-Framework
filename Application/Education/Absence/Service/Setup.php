<?php

namespace SPHERE\Application\Education\Absence\Service;

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
        $tblAbsence = $this->setTableAbsence($Schema);
        $this->setTableAbsenceLesson($Schema, $tblAbsence);

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
    private function setTableAbsence(Schema &$Schema): Table
    {
        $Table = $this->createTable($Schema, 'tblAbsence');
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'FromDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'ToDate', self::FIELD_TYPE_DATETIME, true);
        $this->createColumn($Table, 'Remark');
        $this->createColumn($Table, 'Status', self::FIELD_TYPE_SMALLINT);
        $this->createColumn($Table, 'Type', self::FIELD_TYPE_SMALLINT);
        $this->createColumn($Table, 'IsCertificateRelevant', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($Table, 'serviceTblPersonStaff', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPersonCreator', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'Source', self::FIELD_TYPE_SMALLINT);

        $this->createIndex($Table, array('serviceTblPerson', 'FromDate', 'ToDate'), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblAbsence
     */
    private function setTableAbsenceLesson(Schema &$Schema, Table $tblAbsence)
    {
        $Table = $this->getConnection()->createTable($Schema, 'tblAbsenceLesson');
        $this->createColumn($Table, 'Lesson', self::FIELD_TYPE_INTEGER);

        $this->getConnection()->addForeignKey($Table, $tblAbsence);
    }
}