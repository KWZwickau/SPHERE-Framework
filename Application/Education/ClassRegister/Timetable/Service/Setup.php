<?php
namespace SPHERE\Application\Education\ClassRegister\Timetable\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableNode;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableReplacement;
use SPHERE\Application\Education\ClassRegister\Timetable\Service\Entity\TblTimetableWeek;
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
        $this->setTableTimetableWeek($Schema, $tblTimeTable);
        $this->setTableTimetableReplacement($Schema);

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
     * @param Table $tblTimeTable
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
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT, true);

        $this->createForeignKey($Table, $tblTimeTable);

        $this->createIndex($Table, array(TblTimetableNode::ATTR_SERVICE_TBL_COURSE, TblTimetableNode::ATTR_DAY, TblTimetableNode::ATTR_HOUR,
            TblTimetableNode::ATTR_SERVICE_TBL_PERSON, TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE), false);
        $this->createIndex($Table, array(TblTimetableNode::ATTR_SERVICE_TBL_COURSE, TblTimetableNode::ATTR_DAY, TblTimetableNode::ATTR_HOUR,
            TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE), false);
        $this->createIndex($Table, array(TblTimetableNode::ATTR_DAY, TblTimetableNode::ATTR_SERVICE_TBL_PERSON,
            TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE), false);
        $this->createIndex($Table, array(TblTimetableNode::ATTR_TBL_CLASS_REGISTER_TIMETABLE, TblTimetableNode::ATTR_DAY), false);

        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblTimeTable
     *
     * @return Table
     */
    private function setTableTimetableWeek(Schema &$Schema, Table $tblTimeTable)
    {

        $Table = $this->createTable($Schema, 'tblClassRegisterTimetableWeek');
        $this->createColumn($Table, 'Number', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Week', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_STRING);

        $this->createForeignKey($Table, $tblTimeTable);

        $this->createIndex($Table, array(TblTimetableWeek::ATTR_WEEK, TblTimetableWeek::ATTR_DATE, TblTimetableWeek::ATTR_TBL_CLASS_REGISTER_TIMETABLE));

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableTimetableReplacement(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblClassRegisterTimetableReplacement');
        $this->createColumn($Table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($Table, 'Hour', self::FIELD_TYPE_SMALLINT); // 1 - 12
        $this->createColumn($Table, 'Room', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'IsCanceled', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->createColumn($Table, 'SubjectGroup', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'serviceTblCourse', self::FIELD_TYPE_BIGINT);
        $this->createColumn($Table, 'serviceTblSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblSubstituteSubject', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($Table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);

        $this->createIndex($Table, array('Date', 'Hour', 'serviceTblCourse', TblTimetableReplacement::ATTR_SERVICE_TBL_PERSON), false);
        $this->createIndex($Table, array('Date', 'Hour', 'serviceTblCourse'), false);
        $this->createIndex($Table, array(TblTimetableReplacement::ATTR_DATE, TblTimetableReplacement::ATTR_SERVICE_TBL_PERSON), false);

        return $Table;
    }

}
