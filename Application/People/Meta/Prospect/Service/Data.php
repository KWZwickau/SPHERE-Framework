<?php
namespace SPHERE\Application\People\Meta\Prospect\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Cacheable;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Prospect\Service
 */
class Data extends Cacheable
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
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblProspect
     */
    public function getProspectByPerson(TblPerson $tblPerson)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->Connection->getEntityManager(), 'TblProspect', array(
            TblProspect::SERVICE_TBL_PERSON => $tblPerson->getId()
        ));
    }

    /**
     * @param string $ReservationDate
     * @param string $InterviewDate
     * @param string $TrialDate
     *
     * @return TblProspectAppointment
     */
    public function createProspectAppointment(
        $ReservationDate,
        $InterviewDate,
        $TrialDate
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblProspectAppointment();
        $Entity->setReservationDate(( $ReservationDate ? new \DateTime($ReservationDate) : null ));
        $Entity->setInterviewDate(( $InterviewDate ? new \DateTime($InterviewDate) : null ));
        $Entity->setTrialDate(( $TrialDate ? new \DateTime($TrialDate) : null ));
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param string          $ReservationYear
     * @param string          $ReservationDivision
     * @param null|TblCompany $tblCompanyOptionA
     * @param null|TblCompany $tblCompanyOptionB
     *
     * @return TblProspectReservation
     */
    public function createProspectReservation(
        $ReservationYear,
        $ReservationDivision,
        TblCompany $tblCompanyOptionA = null,
        TblCompany $tblCompanyOptionB = null
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblProspectReservation();
        $Entity->setReservationYear($ReservationYear);
        $Entity->setReservationDivision($ReservationDivision);
        $Entity->setServiceTblCompanyOptionA($tblCompanyOptionA);
        $Entity->setServiceTblCompanyOptionB($tblCompanyOptionB);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param TblPerson              $tblPerson
     * @param TblProspectAppointment $tblProspectAppointment
     * @param TblProspectReservation $tblProspectReservation
     * @param string                 $Remark
     *
     * @return TblProspect
     */
    public function createProspect(
        TblPerson $tblPerson,
        TblProspectAppointment $tblProspectAppointment,
        TblProspectReservation $tblProspectReservation,
        $Remark
    ) {

        $Manager = $this->Connection->getEntityManager();

        $Entity = new TblProspect();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setTblProspectAppointment($tblProspectAppointment);
        $Entity->setTblProspectReservation($tblProspectReservation);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->Connection->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspect
     */
    public function getProspectById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblProspect', $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectAppointment
     */
    public function getProspectAppointmentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblProspectAppointment',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectReservation
     */
    public function getProspectReservationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->Connection->getEntityManager(), 'TblProspectReservation',
            $Id);
    }

    /**
     * @param TblProspectAppointment $tblProspectAppointment
     * @param string                 $ReservationDate
     * @param string                 $InterviewDate
     * @param string                 $TrialDate
     *
     * @return TblProspectAppointment
     */
    public function updateProspectAppointment(
        TblProspectAppointment $tblProspectAppointment,
        $ReservationDate,
        $InterviewDate,
        $TrialDate
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var null|TblProspectAppointment $Entity */
        $Entity = $Manager->getEntityById('TblProspectAppointment', $tblProspectAppointment->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setReservationDate(( $ReservationDate ? new \DateTime($ReservationDate) : null ));
            $Entity->setInterviewDate(( $InterviewDate ? new \DateTime($InterviewDate) : null ));
            $Entity->setTrialDate(( $TrialDate ? new \DateTime($TrialDate) : null ));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblProspectReservation $tblProspectReservation
     * @param string                 $ReservationYear
     * @param string                 $ReservationDivision
     * @param null|TblCompany        $tblCompanyOptionA
     * @param null|TblCompany        $tblCompanyOptionB
     *
     * @return TblProspectReservation
     */
    public function updateProspectReservation(
        TblProspectReservation $tblProspectReservation,
        $ReservationYear,
        $ReservationDivision,
        TblCompany $tblCompanyOptionA = null,
        TblCompany $tblCompanyOptionB = null
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var null|TblProspectReservation $Entity */
        $Entity = $Manager->getEntityById('TblProspectReservation', $tblProspectReservation->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setReservationYear($ReservationYear);
            $Entity->setReservationDivision($ReservationDivision);
            $Entity->setServiceTblCompanyOptionA($tblCompanyOptionA);
            $Entity->setServiceTblCompanyOptionB($tblCompanyOptionB);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblProspect $tblProspect
     * @param string      $Remark
     *
     * @return TblProspect
     */
    public function updateProspect(
        TblProspect $tblProspect,
        $Remark
    ) {

        $Manager = $this->Connection->getEntityManager();
        /** @var null|TblProspect $Entity */
        $Entity = $Manager->getEntityById('TblProspect', $tblProspect->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->Connection->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}
