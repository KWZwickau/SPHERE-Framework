<?php
namespace SPHERE\Application\People\Meta\Student\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;
use SPHERE\System\Database\Fitting\Element;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Student\Service
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
        $this->setTableStudentInsuranceState($Schema);
        $tblStudentMedicalRecord = $this->setTableStudentMedicalRecord($Schema);
        $this->setTableStudentMasernInfo($Schema);
        $tblStudentTransport = $this->setTableStudentTransport($Schema);
        $tblStudentBilling = $this->setTableStudentBilling($Schema);
        $tblStudentLocker = $this->setTableStudentLocker($Schema);
        $tblStudentBaptism = $this->setTableStudentBaptism($Schema);
        $tblStudentSpecialNeedsLevel = $this->setTableStudentSpecialNeedsLevel($Schema);
        $tblStudentSpecialNeeds = $this->setTableStudentSpecialNeeds($Schema, $tblStudentSpecialNeedsLevel);
        $tblStudentTenseOfLesson = $this->setTableStudentTenseOfLesson($Schema);
        $tblStudentTrainingStatus = $this->setTableStudentTrainingStatus($Schema);
        $tblStudentTechnicalSchool = $this->setTableStudentTechnicalSchool($Schema, $tblStudentTenseOfLesson, $tblStudentTrainingStatus);

        $tblStudent = $this->setTableStudent(
            $Schema, $tblStudentMedicalRecord, $tblStudentTransport,
            $tblStudentBilling, $tblStudentLocker, $tblStudentBaptism,
            $tblStudentSpecialNeeds, $tblStudentTechnicalSchool
        );

        $tblStudentTransferType = $this->setTableStudentTransferType($Schema);
        $tblStudentSchoolEnrollmentType =  $this->setTableStudentSchoolEnrollmentType($Schema);
        $this->setTableStudentTransfer($Schema, $tblStudent, $tblStudentTransferType, $tblStudentSchoolEnrollmentType);

        $tblStudentAgreementCategory = $this->setTableStudentAgreementCategory($Schema);
        $tblStudentAgreementType = $this->setTableStudentAgreementType($Schema, $tblStudentAgreementCategory);
        $this->setTableStudentAgreement($Schema, $tblStudent, $tblStudentAgreementType);

        $tblStudentLiberationCategory = $this->setTableStudentLiberationCategory($Schema);
        $tblStudentLiberationType = $this->setTableStudentLiberationType($Schema, $tblStudentLiberationCategory);
        $this->setTableStudentLiberation($Schema, $tblStudent, $tblStudentLiberationType);

        $tblStudentSubjectType = $this->setTableStudentSubjectType($Schema);
        $tblStudentSubjectRanking = $this->setTableStudentSubjectRanking($Schema);
        $this->setTableStudentSubject($Schema, $tblStudent, $tblStudentSubjectType, $tblStudentSubjectRanking);

        $tblSupportType = $this->setTableSupportType($Schema);
        $tblSupport = $this->setTableSupport($Schema, $tblSupportType);
        $tblSupportFocusType = $this->setTableSupportFocusType($Schema);
        $this->setTableSupportFocus($Schema, $tblSupport, $tblSupportFocusType);

        $tblSpecial = $this->setTableSpecial($Schema);
        $tblSpecialDisorderType = $this->setTableSpecialDisorderType($Schema);
        $this->setTableSpecialDisorder($Schema, $tblSpecial, $tblSpecialDisorderType);

        $this->setTableHandyCap($Schema);

        /**
         * Migration & Protocol
         */
        $this->getConnection()->addProtocol(__CLASS__);
        if(!$UTF8){
            $this->getConnection()->setMigration($Schema, $Simulate);
        } else {
            $this->getConnection()->setUTF8();
        }

        // remove deprecated Table

        $this->getConnection()->dropTable('tblStudentDisorder');
        $this->getConnection()->dropTable('tblStudentDisorderType');
        $this->getConnection()->dropTable('tblStudentFocus');
        $this->getConnection()->dropTable('tblStudentFocusType');
        $this->getConnection()->dropTable('tblStudentIntegration');

        // remove deprecated Student Views
        $this->getConnection()->dropView('viewStudent');
        $this->getConnection()->dropView('viewStudentAgreement');
        $this->getConnection()->dropView('viewStudentBaptism');
        $this->getConnection()->dropView('viewStudentLiberation');
        $this->getConnection()->dropView('viewStudentLocker');
        $this->getConnection()->dropView('viewStudentDisorder');
        $this->getConnection()->dropView('viewStudentFocus');
        $this->getConnection()->dropView('viewStudentIntegration');
        $this->getConnection()->dropView('viewStudentMedicalRecord');
        $this->getConnection()->dropView('viewStudentSubject');
        $this->getConnection()->dropView('viewStudentTransfer');
        $this->getConnection()->dropView('viewStudentTransport');

        return $this->getConnection()->getProtocol($Simulate);
    }


    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentInsuranceState(Schema &$Schema)
    {
        $Table = $this->createTable($Schema, 'tblStudentInsuranceState');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_TEXT);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentMedicalRecord(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentMedicalRecord');
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Disease')) {
            $Table->addColumn('Disease', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Medication')) {
            $Table->addColumn('Medication', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'AttendingDoctor')) {
            $Table->addColumn('AttendingDoctor', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'InsuranceState')) {
            $Table->addColumn('InsuranceState', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Insurance')) {
            $Table->addColumn('Insurance', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'MasernDate')) {
            $Table->addColumn('MasernDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'MasernDocumentType')) {
            $Table->addColumn('MasernDocumentType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'MasernCreatorType')) {
            $Table->addColumn('MasernCreatorType', 'bigint', array('notnull' => false));
        }
        $this->createColumn($Table, 'InsuranceNumber', self::FIELD_TYPE_STRING, false, '');

//        // entfernen alter Rückstände
//        if ($this->getConnection()->hasColumn('tblStudentMedicalRecord', 'serviceTblPersonAttendingDoctor')) {
//            $Table->dropColumn('serviceTblPersonAttendingDoctor');
//        }

        return $Table;
    }

    private function setTableStudentMasernInfo(Schema $Schema)
    {

        $Table = $this->createTable($Schema, 'tblStudentMasernInfo');
        $this->createColumn($Table, 'Meta', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Type', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'TextShort', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'TextLong', self::FIELD_TYPE_TEXT);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransport(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransport');
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'Route')) {
            $Table->addColumn('Route', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'StationEntrance')) {
            $Table->addColumn('StationEntrance', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'StationExit')) {
            $Table->addColumn('StationExit', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransport', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        $this->createColumn($Table, 'IsDriverStudent', self::FIELD_TYPE_BOOLEAN, false, false);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentBilling(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentBilling');
        if (!$this->getConnection()->hasColumn('tblStudentBilling', 'serviceTblSiblingRank')) {
            $Table->addColumn('serviceTblSiblingRank', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentLocker(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentLocker');
        if (!$this->getConnection()->hasColumn('tblStudentLocker', 'LockerNumber')) {
            $Table->addColumn('LockerNumber', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentLocker', 'LockerLocation')) {
            $Table->addColumn('LockerLocation', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentLocker', 'KeyNumber')) {
            $Table->addColumn('KeyNumber', 'string');
        }
        $this->createColumn($Table, 'CombinationLockNumber', self::FIELD_TYPE_STRING);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentBaptism(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentBaptism');
        if (!$this->getConnection()->hasColumn('tblStudentBaptism', 'BaptismDate')) {
            $Table->addColumn('BaptismDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentBaptism', 'Location')) {
            $Table->addColumn('Location', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblStudentMedicalRecord
     * @param Table $tblStudentTransport
     * @param Table $tblStudentBilling
     * @param Table $tblStudentLocker
     * @param Table $tblStudentBaptism
     * @param Table $tblStudentSpecialNeeds
     * @param Table $tblStudentTechnicalSchool
     *
     * @return Table
     */
    private function setTableStudent(
        Schema &$Schema,
        Table $tblStudentMedicalRecord,
        Table $tblStudentTransport,
        Table $tblStudentBilling,
        Table $tblStudentLocker,
        Table $tblStudentBaptism,
        Table $tblStudentSpecialNeeds,
        Table $tblStudentTechnicalSchool
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudent');
        if (!$this->getConnection()->hasColumn('tblStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->removeIndex($Table, array('serviceTblPerson'));
        if (!$this->getConnection()->hasIndex($Table, array('serviceTblPerson', Element::ENTITY_REMOVE))) {
            $Table->addIndex(array('serviceTblPerson', Element::ENTITY_REMOVE));
        }
        if (!$this->getConnection()->hasColumn('tblStudent', 'Prefix')) {
            $Table->addColumn('Prefix', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudent', 'Identifier')) {
            $Table->addColumn('Identifier', 'string', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudent', 'SchoolAttendanceStartDate')) {
            $Table->addColumn('SchoolAttendanceStartDate', 'datetime', array('notnull' => false));
        }
        $this->createColumn($Table, 'HasMigrationBackground', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->createColumn($Table, 'MigrationBackground', self::FIELD_TYPE_STRING, true);
        $this->createColumn($Table, 'IsInPreparationDivisionForMigrants', self::FIELD_TYPE_BOOLEAN, false, false);
        $this->getConnection()->addForeignKey($Table, $tblStudentMedicalRecord, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransport, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentBilling, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentLocker, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentBaptism, true);
        if ($Table->hasColumn('tblStudentIntegration')){
            // Angabe exakter Index
            // if auf index funktioniert irgendwie nicht immer
//            if($Table->hasIndex('FK_C0B1893DA033F97')){
            // workaround damit es Online funktioniert
            try {
                $Table->removeForeignKey('FK_C0B1893DA033F97');
            } catch (\Exception $e) {
                // Kümmere dich um Ausnahmen (nicht notwendig, mach nur weiter)
            }
//            }

            $Table->dropColumn('tblStudentIntegration');
        }
        $this->getConnection()->addForeignKey($Table, $tblStudentSpecialNeeds, true);
        $this->getConnection()->addForeignKey($Table, $tblStudentTechnicalSchool, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransferType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferType');
        if (!$this->getConnection()->hasColumn('tblStudentTransferType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblStudent
     * @param Table $tblStudentTransferType
     * @param Table $tblStudentSchoolEnrollmentType
     *
     * @return Table
     */
    private function setTableStudentTransfer(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentTransferType,
        Table $tblStudentSchoolEnrollmentType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransfer');
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'serviceTblCourse')) {
            $Table->addColumn('serviceTblCourse', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'TransferDate')) {
            $Table->addColumn('TransferDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransfer', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        $this->createColumn($Table, 'serviceTblStateCompany', self::FIELD_TYPE_INTEGER, true);

        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferType);
        $this->getConnection()->addForeignKey($Table, $tblStudentSchoolEnrollmentType, true);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentAgreementCategory(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentAgreementCategory');
        if (!$this->getConnection()->hasColumn('tblStudentAgreementCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentAgreementCategory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentAgreementCategory
     *
     * @return Table
     */
    private function setTableStudentAgreementType(Schema &$Schema, Table $tblStudentAgreementCategory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentAgreementType');
        if (!$this->getConnection()->hasColumn('tblStudentAgreementType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentAgreementType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->createColumn($Table, 'isUnlocked', self::FIELD_TYPE_BOOLEAN);
        $this->getConnection()->addForeignKey($Table, $tblStudentAgreementCategory);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudent
     * @param Table  $tblStudentAgreementType
     *
     * @return Table
     */
    private function setTableStudentAgreement(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentAgreementType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentAgreement');
        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentAgreementType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentLiberationCategory(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentLiberationCategory');
        if (!$this->getConnection()->hasColumn('tblStudentLiberationCategory', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentLiberationCategory', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentLiberationCategory
     *
     * @return Table
     */
    private function setTableStudentLiberationType(Schema &$Schema, Table $tblStudentLiberationCategory)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentLiberationType');
        if (!$this->getConnection()->hasColumn('tblStudentLiberationType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentLiberationType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        $this->getConnection()->addForeignKey($Table, $tblStudentLiberationCategory);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudent
     * @param Table  $tblStudentLiberationType
     *
     * @return Table
     */
    private function setTableStudentLiberation(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentLiberationType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentLiberation');
        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentLiberationType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSubjectType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubjectType');
        if (!$this->getConnection()->hasColumn('tblStudentSubjectType', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSubjectRanking(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubjectRanking');
        if (!$this->getConnection()->hasColumn('tblStudentSubjectRanking', 'Identifier')) {
            $Table->addColumn('Identifier', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectRanking', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudent
     * @param Table  $tblStudentSubjectType
     * @param Table  $tblStudentSubjectRanking
     *
     * @return Table
     */
    private function setTableStudentSubject(
        Schema &$Schema,
        Table $tblStudent,
        Table $tblStudentSubjectType,
        Table $tblStudentSubjectRanking
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubject');
        $this->getConnection()->addForeignKey($Table, $tblStudent);
        $this->getConnection()->addForeignKey($Table, $tblStudentSubjectType);
        $this->getConnection()->addForeignKey($Table, $tblStudentSubjectRanking);
        if (!$this->getConnection()->hasColumn('tblStudentSubject', 'serviceTblSubject')) {
            $Table->addColumn('serviceTblSubject', 'bigint', array('notnull' => false));
        }

        $this->createColumn($Table, 'LevelFrom', self::FIELD_TYPE_INTEGER, true);
        $this->createColumn($Table, 'LevelTill', self::FIELD_TYPE_INTEGER, true);

        // todo drop nach Migration
        if (!$this->getConnection()->hasColumn('tblStudentSubject', 'serviceTblLevelFrom')) {
            $Table->addColumn('serviceTblLevelFrom', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubject', 'serviceTblLevelTill')) {
            $Table->addColumn('serviceTblLevelTill', 'bigint', array('notnull' => false));
        }

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSchoolEnrollmentType(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblStudentSchoolEnrollmentType');
        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Identifier', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSupportType(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblSupportType');
        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Description', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSupportType
     *
     * @return Table
     */
    private function setTableSupport(Schema &$Schema, Table $tblSupportType)
    {

        $table = $this->createTable($Schema, 'tblSupport');
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createForeignKey($table, $tblSupportType);
        $this->createColumn($table, 'Company', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'PersonSupport', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'SupportTime', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'PersonEditor', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Remark', self::FIELD_TYPE_TEXT);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSupport
     * @param Table  $tblSupportFocusType
     *
     * @return Table
     */
    private function setTableSupportFocus(Schema &$Schema, Table $tblSupport, Table $tblSupportFocusType)
    {

        $table = $this->createTable($Schema, 'tblSupportFocus');
        $this->createForeignKey($table, $tblSupport);
        $this->createForeignKey($table, $tblSupportFocusType);
        $this->createColumn($table, 'IsPrimary', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSupportFocusType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblSupportFocusType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSpecial(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblSpecial');
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($table, 'PersonEditor', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Remark', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'IsCanceled', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblSpecial
     * @param Table  $tblSpecialDisorderType
     *
     * @return Table
     */
    private function setTableSpecialDisorder(Schema &$Schema, Table $tblSpecial, Table $tblSpecialDisorderType)
    {

        $table = $this->createTable($Schema, 'tblSpecialDisorder');
        $this->createForeignKey($table, $tblSpecial);
        $this->createForeignKey($table, $tblSpecialDisorderType);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableSpecialDisorderType(Schema &$Schema)
    {

        $Table = $this->createTable($Schema, 'tblSpecialDisorderType');
        $this->createColumn($Table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($Table, 'Description', self::FIELD_TYPE_STRING);

        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableHandyCap(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblHandyCap');
        $this->createColumn($table, 'serviceTblPerson', self::FIELD_TYPE_BIGINT);
        $this->createColumn($table, 'Date', self::FIELD_TYPE_DATETIME);
        $this->createColumn($table, 'PersonEditor', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'LegalBasis', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'LearnTarget', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'RemarkLesson', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'RemarkRating', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'RemarkCertificate', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'IsCanceled', self::FIELD_TYPE_BOOLEAN);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSpecialNeedsLevel(Schema &$Schema)
    {

        $table = $this->createTable($Schema, 'tblStudentSpecialNeedsLevel');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Identifier', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentSpecialNeedsLevel
     *
     * @return Table
     */
    private function setTableStudentSpecialNeeds(Schema &$Schema, Table $tblStudentSpecialNeedsLevel)
    {

        $table = $this->createTable($Schema, 'tblStudentSpecialNeeds');

        if ($table->hasColumn('IsMultipleHandicapped')) {
            $table->dropColumn('IsMultipleHandicapped');
        }
//        $this->createColumn($table, 'IsMultipleHandicapped', self::FIELD_TYPE_BOOLEAN);

        $this->createColumn($table, 'IsHeavyMultipleHandicapped', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'IncreaseFactorHeavyMultipleHandicappedSchool', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'IncreaseFactorHeavyMultipleHandicappedRegionalAuthorities', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'RemarkHeavyMultipleHandicapped', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'DegreeOfHandicap', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Sign', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'ValidTo', self::FIELD_TYPE_STRING);

        $this->createForeignKey($table, $tblStudentSpecialNeedsLevel, true);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTenseOfLesson(Schema &$Schema)
    {
        $table = $this->createTable($Schema, 'tblStudentTenseOfLesson');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Identifier', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTrainingStatus(Schema &$Schema)
    {
        $table = $this->createTable($Schema, 'tblStudentTrainingStatus');

        $this->createColumn($table, 'Name', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Identifier', self::FIELD_TYPE_STRING);

        return $table;
    }

    /**
     * @param Schema $Schema
     * @param Table $tblStudentTenseOfLesson
     * @param Table $tblStudentTrainingStatus
     *
     * @return Table
     */
    private function setTableStudentTechnicalSchool(Schema &$Schema, Table $tblStudentTenseOfLesson, Table $tblStudentTrainingStatus)
    {
        $table = $this->createTable($Schema, 'tblStudentTechnicalSchool');

        $this->createColumn($table, 'serviceTblTechnicalCourse', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'serviceTblSchoolDiploma', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'serviceTblSchoolType', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'serviceTblTechnicalDiploma', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'serviceTblTechnicalType', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'PraxisLessons', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'DurationOfTraining', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'Remark', self::FIELD_TYPE_TEXT);
        $this->createColumn($table, 'YearOfSchoolDiploma', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'YearOfTechnicalDiploma', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'serviceTblTechnicalSubjectArea', self::FIELD_TYPE_BIGINT, true);
        $this->createColumn($table, 'HasFinancialAid', self::FIELD_TYPE_BOOLEAN);
        $this->createColumn($table, 'FinancialAidApplicationYear', self::FIELD_TYPE_STRING);
        $this->createColumn($table, 'FinancialAidBureau', self::FIELD_TYPE_STRING);

        $this->createForeignKey($table, $tblStudentTenseOfLesson, true);
        $this->createForeignKey($table, $tblStudentTrainingStatus, true);

        return $table;
    }
}
