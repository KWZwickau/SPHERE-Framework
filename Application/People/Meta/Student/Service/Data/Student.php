<?php
namespace SPHERE\Application\People\Meta\Student\Service\Data;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentSchoolEnrollmentType;
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
     * @param $Identifier
     * @param null $tblStudentMedicalRecord
     * @param null $tblStudentTransport
     * @param null $tblStudentBilling
     * @param null $tblStudentLocker
     * @param null $tblStudentBaptism
     * @param null $tblStudentIntegration
     * @param string $SchoolAttendanceStartDate
     * @param bool $HasMigrationBackground
     * @param bool $IsInPreparationDivisionForMigrants
     *
     * @return TblStudent
     */
    public function createStudent(
        TblPerson $tblPerson,
        $Identifier,
        $tblStudentMedicalRecord = null,
        $tblStudentTransport = null,
        $tblStudentBilling = null,
        $tblStudentLocker = null,
        $tblStudentBaptism = null,
        $tblStudentIntegration = null,
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
            if ($IsIdentifier) {
                $Entity->setIdentifier($Identifier);
            }
            $Entity->setTblStudentMedicalRecord($tblStudentMedicalRecord);
            $Entity->setTblStudentTransport($tblStudentTransport);
            $Entity->setTblStudentBilling($tblStudentBilling);
            $Entity->setTblStudentLocker($tblStudentLocker);
            $Entity->setTblStudentBaptism($tblStudentBaptism);
            $Entity->setTblStudentIntegration($tblStudentIntegration);
            $Entity->setSchoolAttendanceStartDate(( $SchoolAttendanceStartDate ? new \DateTime($SchoolAttendanceStartDate) : null ));
            $Entity->setHasMigrationBackground($HasMigrationBackground);
            $Entity->setIsInPreparationDivisionForMigrants($IsInPreparationDivisionForMigrants);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblStudent $tblStudent
     * @param $Identifier
     * @param null $tblStudentMedicalRecord
     * @param null $tblStudentTransport
     * @param null $tblStudentBilling
     * @param null $tblStudentLocker
     * @param null $tblStudentBaptism
     * @param null $tblStudentIntegration
     * @param string $SchoolAttendanceStartDate
     * @param bool $HasMigrationBackground
     * @param bool $IsInPreparationDivisionForMigrants
     *
     * @return bool
     */
    public function updateStudent(
        TblStudent $tblStudent,
        $Identifier,
        $tblStudentMedicalRecord = null,
        $tblStudentTransport = null,
        $tblStudentBilling = null,
        $tblStudentLocker = null,
        $tblStudentBaptism = null,
        $tblStudentIntegration = null,
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

            if ($IsIdentifier) {
                $Entity->setIdentifier($Identifier);
            }
            $Entity->setTblStudentMedicalRecord($tblStudentMedicalRecord);
            $Entity->setTblStudentTransport($tblStudentTransport);
            $Entity->setTblStudentBilling($tblStudentBilling);
            $Entity->setTblStudentLocker($tblStudentLocker);
            $Entity->setTblStudentBaptism($tblStudentBaptism);
            $Entity->setTblStudentIntegration($tblStudentIntegration);
            $Entity->setSchoolAttendanceStartDate(( $SchoolAttendanceStartDate ? new \DateTime($SchoolAttendanceStartDate) : null ));
            $Entity->setHasMigrationBackground($HasMigrationBackground);
            $Entity->setIsInPreparationDivisionForMigrants($IsInPreparationDivisionForMigrants);

            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblStudent
     */
    public function getStudentByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblStudent', array(
            TblStudent::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
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
}
