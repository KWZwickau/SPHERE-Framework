<?php
namespace SPHERE\Application\People\Meta\Student\Service;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use SPHERE\System\Database\Binding\AbstractSetup;

/**
 * Class Setup
 *
 * @package SPHERE\Application\People\Meta\Student\Service
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
        $tblStudentMedicalRecord = $this->setTableStudentMedicalRecord($Schema);
        $tblStudentTransport = $this->setTableStudentTransport($Schema);
        $tblStudentBilling = $this->setTableStudentBilling($Schema);
        $tblStudentLocker = $this->setTableStudentLocker($Schema);
        $tblStudentBaptism = $this->setTableStudentBaptism($Schema);

        $tblStudentTransferEnrollment = $this->setTableStudentTransferEnrollment($Schema);
        $tblStudentTransferArrive = $this->setTableStudentTransferArrive($Schema);
        $tblStudentTransferProcess = $this->setTableStudentTransferProcess($Schema);
        $tblStudentTransferLeave = $this->setTableStudentTransferLeave($Schema);
        $tblStudentTransfer = $this->setTableStudentTransfer($Schema,
            $tblStudentTransferEnrollment, $tblStudentTransferArrive, $tblStudentTransferProcess,
            $tblStudentTransferLeave
        );

        $tblStudentAgreementCategory = $this->setTableStudentAgreementCategory($Schema);
        $tblStudentAgreementType = $this->setTableStudentAgreementType($Schema);
        $tblStudentAgreement = $this->setTableStudentAgreement($Schema,
            $tblStudentAgreementCategory, $tblStudentAgreementType
        );

        $tblStudentSubjectProfile = $this->setTableStudentSubjectProfile($Schema);
        $tblStudentSubjectForeignLanguage = $this->setTableStudentSubjectForeignLanguage($Schema);
        $tblStudentSubjectElective = $this->setTableStudentSubjectElective($Schema);
        $tblStudentSubjectTeam = $this->setTableStudentSubjectTeam($Schema);
        $tblStudentSubjectTrack = $this->setTableStudentSubjectTrack($Schema);
        $tblStudentSubject = $this->setTableStudentSubject($Schema,
            $tblStudentSubjectProfile, $tblStudentSubjectForeignLanguage, $tblStudentSubjectElective,
            $tblStudentSubjectTeam, $tblStudentSubjectTrack
        );

        $this->setTableStudent(
            $Schema,
            $tblStudentMedicalRecord,
            $tblStudentTransport,
            $tblStudentTransfer,
            $tblStudentBilling,
            $tblStudentLocker,
            $tblStudentBaptism,
            $tblStudentAgreement,
            $tblStudentSubject
        );
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
    private function setTableStudentMedicalRecord(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentMedicalRecord');
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Disease')) {
            $Table->addColumn('Disease', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Medication')) {
            $Table->addColumn('Medication', 'text');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'serviceTblPersonAttendingDoctor')) {
            $Table->addColumn('serviceTblPersonAttendingDoctor', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'InsuranceState')) {
            $Table->addColumn('InsuranceState', 'bigint');
        }
        if (!$this->getConnection()->hasColumn('tblStudentMedicalRecord', 'Insurance')) {
            $Table->addColumn('Insurance', 'string');
        }
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
        if (!$this->getConnection()->hasColumn('tblStudentLocker', 'LockerLockation')) {
            $Table->addColumn('LockerLockation', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentLocker', 'KeyNumber')) {
            $Table->addColumn('KeyNumber', 'string');
        }
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
     *
     * @return Table
     */
    private function setTableStudentTransferEnrollment(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferEnrollment');
        if (!$this->getConnection()->hasColumn('tblStudentTransferEnrollment', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferEnrollment', 'EnrollmentDate')) {
            $Table->addColumn('EnrollmentDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferEnrollment', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransferArrive(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferArrive');
        if (!$this->getConnection()->hasColumn('tblStudentTransferArrive', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferArrive', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferArrive', 'ArriveDate')) {
            $Table->addColumn('ArriveDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferArrive', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransferProcess(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferProcess');
        if (!$this->getConnection()->hasColumn('tblStudentTransferProcess', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferProcess', 'serviceTblCourse')) {
            $Table->addColumn('serviceTblCourse', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferProcess', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentTransferLeave(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransferLeave');
        if (!$this->getConnection()->hasColumn('tblStudentTransferLeave', 'serviceTblCompany')) {
            $Table->addColumn('serviceTblCompany', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferLeave', 'serviceTblType')) {
            $Table->addColumn('serviceTblType', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferLeave', 'LeaveDate')) {
            $Table->addColumn('LeaveDate', 'datetime', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentTransferLeave', 'Remark')) {
            $Table->addColumn('Remark', 'text');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentTransferEnrollment
     * @param Table  $tblStudentTransferArrive
     * @param Table  $tblStudentTransferProcess
     * @param Table  $tblStudentTransferLeave
     *
     * @return Table
     */
    private function setTableStudentTransfer(
        Schema &$Schema,
        Table $tblStudentTransferEnrollment,
        Table $tblStudentTransferArrive,
        Table $tblStudentTransferProcess,
        Table $tblStudentTransferLeave
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentTransfer');
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferEnrollment);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferArrive);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferProcess);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransferLeave);
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
     *
     * @return Table
     */
    private function setTableStudentAgreementType(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentAgreementType');
        if (!$this->getConnection()->hasColumn('tblStudentAgreementType', 'Name')) {
            $Table->addColumn('Name', 'string');
        }
        if (!$this->getConnection()->hasColumn('tblStudentAgreementType', 'Description')) {
            $Table->addColumn('Description', 'string');
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentAgreementCategory
     * @param Table  $tblStudentAgreementType
     *
     * @return Table
     */
    private function setTableStudentAgreement(
        Schema &$Schema,
        Table $tblStudentAgreementCategory,
        Table $tblStudentAgreementType
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentAgreement');
        $this->getConnection()->addForeignKey($Table, $tblStudentAgreementCategory);
        $this->getConnection()->addForeignKey($Table, $tblStudentAgreementType);
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSubjectProfile(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubjectProfile');
        if (!$this->getConnection()->hasColumn('tblStudentSubjectProfile', 'serviceTblSubjectOrientation')) {
            $Table->addColumn('serviceTblSubjectOrientation', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectProfile', 'serviceTblSubjectAdvanced')) {
            $Table->addColumn('serviceTblSubjectAdvanced', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectProfile', 'serviceTblSubjectProfile')) {
            $Table->addColumn('serviceTblSubjectProfile', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectProfile', 'serviceTblSubjectReligion')) {
            $Table->addColumn('serviceTblSubjectReligion', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSubjectForeignLanguage(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubjectForeignLanguage');
        if (!$this->getConnection()->hasColumn('tblStudentSubjectForeignLanguage', 'serviceTblSubject1')) {
            $Table->addColumn('serviceTblSubject1', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectForeignLanguage', 'serviceTblSubject2')) {
            $Table->addColumn('serviceTblSubject2', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectForeignLanguage', 'serviceTblSubject3')) {
            $Table->addColumn('serviceTblSubject3', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectForeignLanguage', 'serviceTblSubject4')) {
            $Table->addColumn('serviceTblSubject4', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSubjectElective(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubjectElective');
        if (!$this->getConnection()->hasColumn('tblStudentSubjectElective', 'serviceTblSubject1')) {
            $Table->addColumn('serviceTblSubject1', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectElective', 'serviceTblSubject2')) {
            $Table->addColumn('serviceTblSubject2', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSubjectTeam(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubjectTeam');
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTeam', 'serviceTblSubject1')) {
            $Table->addColumn('serviceTblSubject1', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTeam', 'serviceTblSubject2')) {
            $Table->addColumn('serviceTblSubject2', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTeam', 'serviceTblSubject3')) {
            $Table->addColumn('serviceTblSubject3', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     *
     * @return Table
     */
    private function setTableStudentSubjectTrack(Schema &$Schema)
    {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubjectTrack');
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectIntensive1')) {
            $Table->addColumn('serviceTblSubjectIntensive1', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectIntensive2')) {
            $Table->addColumn('serviceTblSubjectIntensive2', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectBasic1')) {
            $Table->addColumn('serviceTblSubjectBasic1', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectBasic2')) {
            $Table->addColumn('serviceTblSubjectBasic2', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectBasic3')) {
            $Table->addColumn('serviceTblSubjectBasic3', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectBasic4')) {
            $Table->addColumn('serviceTblSubjectBasic4', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectBasic5')) {
            $Table->addColumn('serviceTblSubjectBasic5', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectBasic6')) {
            $Table->addColumn('serviceTblSubjectBasic6', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectBasic7')) {
            $Table->addColumn('serviceTblSubjectBasic7', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectBasic8')) {
            $Table->addColumn('serviceTblSubjectBasic8', 'bigint', array('notnull' => false));
        }
        if (!$this->getConnection()->hasColumn('tblStudentSubjectTrack', 'serviceTblSubjectBasic9')) {
            $Table->addColumn('serviceTblSubjectBasic9', 'bigint', array('notnull' => false));
        }
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentSubjectProfile
     * @param Table  $tblStudentSubjectForeignLanguage
     * @param Table  $tblStudentSubjectElective
     * @param Table  $tblStudentSubjectTeam
     * @param Table  $tblStudentSubjectTrack
     *
     * @return Table
     */
    private function setTableStudentSubject(
        Schema &$Schema,
        Table $tblStudentSubjectProfile,
        Table $tblStudentSubjectForeignLanguage,
        Table $tblStudentSubjectElective,
        Table $tblStudentSubjectTeam,
        Table $tblStudentSubjectTrack
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentSubject');
        $this->getConnection()->addForeignKey($Table, $tblStudentSubjectProfile);
        $this->getConnection()->addForeignKey($Table, $tblStudentSubjectForeignLanguage);
        $this->getConnection()->addForeignKey($Table, $tblStudentSubjectElective);
        $this->getConnection()->addForeignKey($Table, $tblStudentSubjectTeam);
        $this->getConnection()->addForeignKey($Table, $tblStudentSubjectTrack);
        return $Table;
    }

    /**
     * @param Schema               $Schema
     * @param Table  $tblStudentMedicalRecord
     * @param Table  $tblStudentTransport
     * @param Table  $tblStudentTransfer
     * @param Table                $tblStudentBilling
     * @param Table                $tblStudentLocker
     * @param Table                $tblStudentBaptism
     * @param Table                $tblStudentAgreement
     * @param Table                $tblStudentSubject
     * @param Table                $tblStudentIntegration
     *
     * @return Table
     */
    private function setTableStudent(
        Schema &$Schema,
        Table $tblStudentMedicalRecord,
        Table $tblStudentTransport,
        Table $tblStudentTransfer,
        Table $tblStudentBilling,
        Table $tblStudentLocker,
        Table $tblStudentBaptism,
        Table $tblStudentAgreement,
        Table $tblStudentSubject,
        Table $tblStudentIntegration
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudent');
        if (!$this->getConnection()->hasColumn('tblStudent', 'serviceTblPerson')) {
            $Table->addColumn('serviceTblPerson', 'bigint', array('notnull' => false));
        }
        $this->getConnection()->addForeignKey($Table, $tblStudentMedicalRecord);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransport);
        $this->getConnection()->addForeignKey($Table, $tblStudentTransfer);
        $this->getConnection()->addForeignKey($Table, $tblStudentBilling);
        $this->getConnection()->addForeignKey($Table, $tblStudentLocker);
        $this->getConnection()->addForeignKey($Table, $tblStudentBaptism);
        $this->getConnection()->addForeignKey($Table, $tblStudentAgreement);
        $this->getConnection()->addForeignKey($Table, $tblStudentSubject);
        $this->getConnection()->addForeignKey($Table, $tblStudentIntegration);
        return $Table;
    }

    /**
     * @param Schema $Schema
     * @param Table  $tblStudentIntegrationCoaching
     * @param Table  $tblStudentIntegrationSchool
     * @param Table  $tblStudentIntegrationModule
     * @param Table  $tblStudentIntegrationDisorder
     *
     * @return Table
     */
    private function setTableStudentIntegration(
        Schema &$Schema,
        Table $tblStudentIntegrationCoaching,
        Table $tblStudentIntegrationSchool,
        Table $tblStudentIntegrationModule,
        Table $tblStudentIntegrationDisorder
    ) {

        $Table = $this->getConnection()->createTable($Schema, 'tblStudentIntegration');
        $this->getConnection()->addForeignKey($Table, $tblStudentIntegrationCoaching);
        $this->getConnection()->addForeignKey($Table, $tblStudentIntegrationSchool);
        $this->getConnection()->addForeignKey($Table, $tblStudentIntegrationModule);
        $this->getConnection()->addForeignKey($Table, $tblStudentIntegrationDisorder);
        return $Table;
    }
}
