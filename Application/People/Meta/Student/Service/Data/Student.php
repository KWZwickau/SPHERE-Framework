<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use DateTime;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBaptism;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentBilling;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentLocker;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSchoolEnrollmentType;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSpecialNeeds;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSpecialNeedsLevel;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTechnicalSchool;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTenseOfLesson;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTrainingStatus;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransport;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Student
 *
 * @package SPHERE\Application\People\Meta\Student\Service\Data
 */
abstract class Student extends AbstractData
{

    /**
     * @param TblPerson $tblPerson
     * @param string $Prefix
     * @param string $Identifier
     * @param null $tblStudentMedicalRecord
     * @param null $tblStudentTransport
     * @param null $tblStudentBilling
     * @param null $tblStudentLocker
     * @param null $tblStudentBaptism
     * @param null $tblStudentSpecialNeeds
     * @param null $tblStudentTechnicalSchool
     * @param string $SchoolAttendanceStartDate
     * @param bool $HasMigrationBackground
     * @param bool $IsInPreparationDivisionForMigrants
     *
     * @return TblStudent
     */
    public function createStudent(
        TblPerson $tblPerson,
        $Prefix,
        $Identifier,
        $tblStudentMedicalRecord = null,
        $tblStudentTransport = null,
        $tblStudentBilling = null,
        $tblStudentLocker = null,
        $tblStudentBaptism = null,
        $tblStudentSpecialNeeds = null,
        $tblStudentTechnicalSchool = null,
        $SchoolAttendanceStartDate = '',
        $HasMigrationBackground = false,
        $IsInPreparationDivisionForMigrants = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $IsIdentifier = true;
        $IdentifierResult = $Manager->getEntity('TblStudent')
            ->findOneBy(array(
                TblStudent::ATTR_TBL_IDENTIFIER => $Identifier,
            ));
        if ($IdentifierResult) {
            $IsIdentifier = false;
        }

        $Entity = $this->getStudentByPerson($tblPerson);
        if (!$Entity) {
            $Entity = new TblStudent();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setPrefix($Prefix);
            if ($IsIdentifier) {
                $Entity->setIdentifier($Identifier);
            }
            $Entity->setTblStudentMedicalRecord($tblStudentMedicalRecord);
            $Entity->setTblStudentTransport($tblStudentTransport);
            $Entity->setTblStudentBilling($tblStudentBilling);
            $Entity->setTblStudentLocker($tblStudentLocker);
            $Entity->setTblStudentBaptism($tblStudentBaptism);
            $Entity->setTblStudentSpecialNeeds($tblStudentSpecialNeeds);
            $Entity->setTblStudentTechnicalSchool($tblStudentTechnicalSchool);
            $Entity->setSchoolAttendanceStartDate(( $SchoolAttendanceStartDate ? new DateTime($SchoolAttendanceStartDate) : null ));
            $Entity->setHasMigrationBackground($HasMigrationBackground);
            $Entity->setIsInPreparationDivisionForMigrants($IsInPreparationDivisionForMigrants);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblPerson $tblPerson
     * @param string    $Prefix
     * @param string    $Identifier
     * @param string    $SchoolAttendanceStartDate
     * @param bool      $HasMigrationBackground
     * @param string    $MigrationBackground
     * @param bool      $IsInPreparationDivisionForMigrants
     *
     * @return bool|TblStudent
     */
    public function createStudentBasic(
        TblPerson $tblPerson,
        $Prefix = '',
        $Identifier = '',
        $SchoolAttendanceStartDate = '',
        $HasMigrationBackground = false,
        $MigrationBackground = '',
        $IsInPreparationDivisionForMigrants = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $IsIdentifier = true;
        $IdentifierResult = $Manager->getEntity('TblStudent')
            ->findOneBy(array(
                TblStudent::ATTR_TBL_IDENTIFIER => $Identifier,
            ));
        if ($IdentifierResult) {
            $IsIdentifier = false;
        }

        $Entity = $this->getStudentByPerson($tblPerson, true);

        if (!$Entity) {
            $Entity = new TblStudent();
            $Entity->setServiceTblPerson($tblPerson);
            $Entity->setPrefix($Prefix);
            if ($IsIdentifier) {
                $Entity->setIdentifier($Identifier);
            }
            $Entity->setSchoolAttendanceStartDate(( $SchoolAttendanceStartDate ? new DateTime($SchoolAttendanceStartDate) : null ));
            $Entity->setHasMigrationBackground($HasMigrationBackground);
            $Entity->setMigrationBackground($MigrationBackground);
            $Entity->setIsInPreparationDivisionForMigrants($IsInPreparationDivisionForMigrants);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblStudent $tblStudent
     * @param string     $Prefix
     * @param string     $Identifier
     * @param string     $SchoolAttendanceStartDate
     * @param bool       $HasMigrationBackground
     * @param string     $MigrationBackground
     * @param bool       $IsInPreparationDivisionForMigrants
     *
     * @return bool
     */
    public function updateStudentBasic(
        TblStudent $tblStudent,
        $Prefix = '',
        $Identifier = '',
        $SchoolAttendanceStartDate = '',
        $HasMigrationBackground = false,
        $MigrationBackground = '',
        $IsInPreparationDivisionForMigrants = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $IsIdentifier = true;
        $IdentifierResult = $Manager->getEntity('TblStudent')
            ->findOneBy(array(
                TblStudent::ATTR_TBL_IDENTIFIER => $Identifier,
            ));
        if ($IdentifierResult && $Identifier !== '') {
            $IsIdentifier = false;
        }

        /** @var null|TblStudent $Entity */
        $Entity = $Manager->getEntityById('TblStudent', $tblStudent->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setPrefix($Prefix);
            if ($IsIdentifier) {
                $Entity->setIdentifier($Identifier);
            }
            $Entity->setSchoolAttendanceStartDate(( $SchoolAttendanceStartDate ? new DateTime($SchoolAttendanceStartDate) : null ));
            $Entity->setHasMigrationBackground($HasMigrationBackground);
            $Entity->setMigrationBackground($MigrationBackground);
            $Entity->setIsInPreparationDivisionForMigrants($IsInPreparationDivisionForMigrants);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);

            return true;
        }

        return false;
    }

    /**
     * @param TblStudent $tblStudent
     * @param $Prefix
     * @return bool|TblStudent
     */
    public function updateStudentPrefix(TblStudent $tblStudent, $Prefix)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudent')
            ->findOneBy(array(
                TblStudent::ENTITY_ID => $tblStudent->getId(),
            ));
        /** @var TblStudent $Entity */
        if($Entity){
            $Protocol = clone $Entity;
            $Entity->setPrefix($Prefix);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }
        return ($Entity ? $Entity : false);
    }

    /**
     * @param TblStudent $tblStudent
     * @param $Identifier
     * @return bool|TblStudent
     */
    public function updateStudentIdentifier(TblStudent $tblStudent, $Identifier)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudent')
            ->findOneBy(array(
                TblStudent::ENTITY_ID => $tblStudent->getId(),
            ));
        /** @var TblStudent $Entity */
        if($Entity){
            $Protocol = clone $Entity;
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
        }
        return ($Entity ? $Entity : false);
    }

    /**
     * @param TblStudent $tblStudent
     * @param string $Prefix
     * @param string $Identifier
     * @param null $tblStudentMedicalRecord
     * @param null $tblStudentTransport
     * @param null $tblStudentBilling
     * @param null $tblStudentLocker
     * @param null $tblStudentBaptism
     * @param null $tblStudentSpecialNeeds
     * @param null $tblStudentTechnicalSchool
     * @param string $SchoolAttendanceStartDate
     * @param bool $HasMigrationBackground
     * @param bool $IsInPreparationDivisionForMigrants
     *
     * @return bool
     */
    public function updateStudent(
        TblStudent $tblStudent,
        $Prefix = '',
        $Identifier = '',
        $tblStudentMedicalRecord = null,
        $tblStudentTransport = null,
        $tblStudentBilling = null,
        $tblStudentLocker = null,
        $tblStudentBaptism = null,
        $tblStudentSpecialNeeds = null,
        $tblStudentTechnicalSchool = null,
        $SchoolAttendanceStartDate = '',
        $HasMigrationBackground = false,
        $IsInPreparationDivisionForMigrants = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $IsIdentifier = true;
        $IdentifierResult = $Manager->getEntity('TblStudent')
            ->findOneBy(array(
                TblStudent::ATTR_TBL_IDENTIFIER => $Identifier,
            ));
        if ($IdentifierResult) {
            $IsIdentifier = false;
        }

        /** @var null|TblStudent $Entity */
        $Entity = $Manager->getEntityById('TblStudent', $tblStudent->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setPrefix($Prefix);
            if ($IsIdentifier) {
                $Entity->setIdentifier($Identifier);
            }
            $Entity->setTblStudentMedicalRecord($tblStudentMedicalRecord);
            $Entity->setTblStudentTransport($tblStudentTransport);
            $Entity->setTblStudentBilling($tblStudentBilling);
            $Entity->setTblStudentLocker($tblStudentLocker);
            $Entity->setTblStudentBaptism($tblStudentBaptism);
            $Entity->setTblStudentSpecialNeeds($tblStudentSpecialNeeds);
            $Entity->setTblStudentTechnicalSchool($tblStudentTechnicalSchool);
            $Entity->setSchoolAttendanceStartDate(( $SchoolAttendanceStartDate ? new DateTime($SchoolAttendanceStartDate) : null ));
            $Entity->setHasMigrationBackground($HasMigrationBackground);
            $Entity->setIsInPreparationDivisionForMigrants($IsInPreparationDivisionForMigrants);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblStudent $tblStudent
     * @param TblStudentMedicalRecord|null $tblStudentMedicalRecord
     * @param TblStudentTransport|null $tblStudentTransport
     * @param TblStudentBilling|null $tblStudentBilling
     * @param TblStudentLocker|null $tblStudentLocker
     * @param TblStudentBaptism|null $tblStudentBaptism
     * @param TblStudentSpecialNeeds|null $tblStudentSpecialNeeds
     * @param TblStudentTechnicalSchool|null $tblStudentTechnicalSchool
     *
     * @return bool
     */
    public function updateStudentField(
        TblStudent $tblStudent,
        TblStudentMedicalRecord $tblStudentMedicalRecord = null,
        TblStudentTransport $tblStudentTransport = null,
        TblStudentBilling $tblStudentBilling = null,
        TblStudentLocker $tblStudentLocker = null,
        TblStudentBaptism $tblStudentBaptism = null,
        TblStudentSpecialNeeds $tblStudentSpecialNeeds = null,
        TblStudentTechnicalSchool $tblStudentTechnicalSchool = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        /** @var null|TblStudent $Entity */
        $Entity = $Manager->getEntityById('TblStudent', $tblStudent->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;

            $Entity->setTblStudentMedicalRecord($tblStudentMedicalRecord);
            $Entity->setTblStudentTransport($tblStudentTransport);
            $Entity->setTblStudentBilling($tblStudentBilling);
            $Entity->setTblStudentLocker($tblStudentLocker);
            $Entity->setTblStudentBaptism($tblStudentBaptism);
            $Entity->setTblStudentSpecialNeeds($tblStudentSpecialNeeds);
            $Entity->setTblStudentTechnicalSchool($tblStudentTechnicalSchool);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblStudent
     */
    public function getStudentByPerson(TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {
            return $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', array(
                TblStudent::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', array(
                TblStudent::SERVICE_TBL_PERSON => $tblPerson->getId()
            ));
        }
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudent
     */
    public function getStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', $Id);
    }

    /**
     * @param string $Identifier
     * @param bool   $isWithRemoved -> true = get also EntityRemove
     *
     * @return bool|TblStudent
     */
    public function getStudentByIdentifier($Identifier, $isWithRemoved = false)
    {

        if($isWithRemoved) {
            return $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', array(
                TblStudent::ATTR_TBL_IDENTIFIER => $Identifier
            ));
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', array(
                TblStudent::ATTR_TBL_IDENTIFIER => $Identifier
            ));
        }
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool
     */
    public function destroyStudent(TblStudent $tblStudent)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudent $Entity */
        $Entity = $Manager->getEntityById('TblStudent', $tblStudent->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            $Manager->removeEntity($Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblStudentSchoolEnrollmentType
     */
    public function getStudentSchoolEnrollmentTypeById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblStudentSchoolEnrollmentType', $Id);
    }

    /**
     * @param $Name
     *
     * @return false|TblStudentSchoolEnrollmentType
     */
    public function getStudentSchoolEnrollmentTypeByName($Name)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentSchoolEnrollmentType', array(
            TblStudentSchoolEnrollmentType::ATTR_NAME => $Name
        ));
    }

    /**
     * @param $Identifier
     *
     * @return false|TblStudentSchoolEnrollmentType
     */
    public function getStudentSchoolEnrollmentTypeByIdentifier($Identifier)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentSchoolEnrollmentType', array(
            TblStudentSchoolEnrollmentType::ATTR_IDENTIFIER => $Identifier
        ));
    }

    /**
     * @return false|TblStudentSchoolEnrollmentType[]
     */
    public function getStudentSchoolEnrollmentTypeAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblStudentSchoolEnrollmentType');
    }

    /**
     * @param string $Identifier
     * @param string $Name
     *
     * @return TblStudentSchoolEnrollmentType
     */
    public function createStudentSchoolEnrollmentType($Identifier, $Name)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblStudentSchoolEnrollmentType')->findOneBy(array(
            TblStudentSchoolEnrollmentType::ATTR_IDENTIFIER => $Identifier
        ));

        if (null === $Entity) {
            $Entity = new TblStudentSchoolEnrollmentType();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblStudent $tblStudent
     *
     * @return bool
     */
    public function restoreStudent(TblStudent $tblStudent)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblStudent $Entity */
        $Entity = $Manager->getEntityById('TblStudent', $tblStudent->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param $Id
     *
     * @return false|TblStudentSpecialNeeds
     */
    public function getStudentSpecialNeedsById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblStudentSpecialNeeds', $Id);
    }

    /**
     * @param $Id
     *
     * @return false|TblStudentTechnicalSchool
     */
    public function getStudentTechnicalSchoolById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblStudentTechnicalSchool', $Id);
    }

    /**
     * @return false|TblStudentTechnicalSchool
     */
    public function getStudentTechnicalSchoolAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblStudentTechnicalSchool');
    }

    /**
     * @param $Id
     *
     * @return false|TblStudentSpecialNeedsLevel
     */
    public function getStudentSpecialNeedsLevelById($Id)
    {
        return $this->getCachedEntityById(__METHOD__, $this->getEntityManager(), 'TblStudentSpecialNeedsLevel', $Id);
    }

    /**
     * @param $Name
     *
     * @return false|TblStudentSpecialNeedsLevel
     */
    public function getStudentSpecialNeedsLevelByName($Name)
    {
        return $this->getCachedEntityBy(__METHOD__, $this->getEntityManager(), 'TblStudentSpecialNeedsLevel', array(
            TblStudentSpecialNeedsLevel::ATTR_NAME => $Name
        ));
    }

    /**
     * @return false|TblStudentSpecialNeedsLevel[]
     */
    public function getStudentSpecialNeedsLevelAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getEntityManager(), 'TblStudentSpecialNeedsLevel', array('EntityCreate' => 'asc'));
    }

    /**
     * @param $Name
     * @param $Identifier
     *
     * @return TblStudentSpecialNeedsLevel|null
     */
    public function createStudentSpecialNeedsLevel($Name, $Identifier)
    {
        $Manager = $this->getEntityManager();

        $Entity = $Manager->getEntity('TblStudentSpecialNeedsLevel')->findOneBy(array(
            TblStudentSpecialNeedsLevel::ATTR_IDENTIFIER => $Identifier
        ));

        if (null === $Entity) {
            $Entity = new TblStudentSpecialNeedsLevel();
            $Entity->setIdentifier($Identifier);
            $Entity->setName($Name);
            $Manager->saveEntity($Entity);

            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param $Id
     *
     * @return bool|TblStudentTenseOfLesson
     */
    public function getStudentTenseOfLessonById($Id)
    {
        /** @var TblStudentTenseOfLesson $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblStudentTenseOfLesson', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblStudentTenseOfLesson[]
     */
    public function getStudentTenseOfLessonAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentTenseOfLesson');
    }

    /**
     * @param $Id
     *
     * @return bool|TblStudentTrainingStatus
     */
    public function getStudentTrainingStatusById($Id)
    {
        /** @var TblStudentTrainingStatus $Entity */
        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblStudentTrainingStatus', $Id);
        return ( null === $Entity ? false : $Entity );
    }

    /**
     * @return bool|TblStudentTrainingStatus[]
     */
    public function getStudentTrainingStatusAll()
    {
        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudentTrainingStatus');
    }
}
