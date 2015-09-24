<?php
namespace SPHERE\Application\People\Meta\Student\Service;

use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudent;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentMedicalRecord;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransfer;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferArrive;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferEnrollment;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferLeave;
use SPHERE\Application\People\Meta\Student\Service\Entity\TblStudentTransferProcess;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\DataCacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Student\Service
 */
class Data extends DataCacheable
{

    /** @var null|Binding $Connection */
    private $Connection = null;

    /**
     * @param Binding $Connection
     */
    function __construct(Binding $Connection)
    {

        $this->Connection = $Connection;
    }

    public function setupDatabaseContent()
    {

    }

    /**
     * @param TblPerson               $tblPerson
     * @param TblStudentMedicalRecord $tblStudentMedicalRecord
     *
     * @return TblStudent
     */
    public function createStudent(
        TblPerson $tblPerson,
        TblStudentMedicalRecord $tblStudentMedicalRecord
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblStudent();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setTblStudentMedicalRecord($tblStudentMedicalRecord);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudent
     */
    public function getStudentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblStudent', $Id);
    }

    /**
     * @param TblPerson $tblPerson
     *
     * @return bool|TblStudent
     */
    public function getStudentByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->Connection->getEntityManager(), 'TblStudent', array(
            TblStudent::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param string         $Disease
     * @param string         $Medication
     * @param null|TblPerson $tblPersonAttendingDoctor
     * @param int            $InsuranceState
     * @param string         $Insurance
     *
     * @return TblStudentMedicalRecord
     */
    public function createStudentMedicalRecord(
        $Disease,
        $Medication,
        TblPerson $tblPersonAttendingDoctor,
        $InsuranceState,
        $Insurance
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblStudentMedicalRecord();
        $Entity->setDisease($Disease);
        $Entity->setMedication($Medication);
        $Entity->setServiceTblPersonAttendingDoctor($tblPersonAttendingDoctor);
        $Entity->setInsuranceState($InsuranceState);
        $Entity->setInsurance($Insurance);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblStudentMedicalRecord $tblStudentMedicalRecord
     * @param string                  $Disease
     * @param string                  $Medication
     * @param null|TblPerson          $tblPersonAttendingDoctor
     * @param int                     $InsuranceState
     * @param string                  $Insurance
     *
     * @return TblStudentMedicalRecord
     */
    public function updateStudentMedicalRecord(
        TblStudentMedicalRecord $tblStudentMedicalRecord,
        $Disease,
        $Medication,
        TblPerson $tblPersonAttendingDoctor,
        $InsuranceState,
        $Insurance
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var null|TblStudentMedicalRecord $Entity */
        $Entity = $Manager->getEntityById('TblStudentMedicalRecord', $tblStudentMedicalRecord->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setDisease($Disease);
            $Entity->setMedication($Medication);
            $Entity->setServiceTblPersonAttendingDoctor($tblPersonAttendingDoctor);
            $Entity->setInsuranceState($InsuranceState);
            $Entity->setInsurance($Insurance);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentMedicalRecord
     */
    public function getStudentMedicalRecordById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblStudentMedicalRecord',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransfer
     */
    public function getStudentTransferById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblStudentTransfer',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferArrive
     */
    public function getStudentTransferArriveById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblStudentTransferArrive',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferEnrollment
     */
    public function getStudentTransferEnrollmentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(),
            'TblStudentTransferEnrollment',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferLeave
     */
    public function getStudentTransferLeaveById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblStudentTransferLeave',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblStudentTransferProcess
     */
    public function getStudentTransferProcessById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(),
            'TblStudentTransferProcess',
            $Id);
    }
}
