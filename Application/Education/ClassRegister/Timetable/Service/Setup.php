<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\Education\ClassRegister\Timetable\Service
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
        $tblTimeTable = $this->setTableTimetable($Schema);
        $this->setTableTimetableNode($Schema, $tblTimeTable);

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
    private function setTableTimetable(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblClassRegisterTimetable');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'DateFrom', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'DateTo', self::FIELD_TYPE_DATETIME);


        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTimetableNode(Schema &$Schema, Table $tblTimeTable)
    {

        $Table = $this->createTable($Schema, 'tblClassRegisterTimetableNode');
        $this->createColumn($Table, 'Hour', self::FIELD_TYPE_SMALLINT); // 1 - 12
        $this->createColumn($Table, 'Day', self::FIELD_TYPE_SMALLINT);  // 1 - 6 als Mo - Fr (Sa)
        $this->createColumn($Table, 'Week', self::FIELD_TYPE_STRING);   // A / B Woche etc.
        $this->createColumn($Table, 'Room', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'SubjectGroup', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Level', self::FIELD_TYPE_STRING);  // Stufe
        $this->createColumn($Table, 'serviceTblCourse', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);

        $this->createForeignKey($Table, $tblTimeTable);

        return $Table;
    }

}
