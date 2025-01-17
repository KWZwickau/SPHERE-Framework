<?php
namespace SPHERE\Application\People\Meta\Prospect\Service;

use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\People\Meta\Prospect\Service
 */
class Data extends AbstractData
{

    public function setupDatabaseContent()
    {

    }


    /**
     *
     * @param TblPerson $tblPerson
     * @param bool $isForced
     *
     * @return bool|TblProspect
     */
    public function getProspectByPerson(TblPerson $tblPerson, $isForced = false)
    {

        if ($isForced) {
            return $this->getForceEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblProspect',
                array(
                    TblProspect::SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        } else {
            return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblProspect',
                array(
                    TblProspect::SERVICE_TBL_PERSON => $tblPerson->getId()
                ));
        }
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

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblProspectAppointment();
        $Entity->setReservationDate(( $ReservationDate ? new \DateTime($ReservationDate) : null ));
        $Entity->setInterviewDate(( $InterviewDate ? new \DateTime($InterviewDate) : null ));
        $Entity->setTrialDate(( $TrialDate ? new \DateTime($TrialDate) : null ));
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param string          $ReservationYear
     * @param string          $ReservationDivision
     * @param null|TblType    $tblTypeOptionA
     * @param null|TblType    $tblTypeOptionB
     * @param TblCompany|null $tblCompany
     *
     * @return TblProspectReservation
     */
    public function createProspectReservation(
        $ReservationYear,
        $ReservationDivision,
        TblType $tblTypeOptionA = null,
        TblType $tblTypeOptionB = null,
        TblCompany $tblCompany = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblProspectReservation();
        $Entity->setReservationYear($ReservationYear);
        $Entity->setReservationDivision($ReservationDivision);
        $Entity->setServiceTblTypeOptionA($tblTypeOptionA);
        $Entity->setServiceTblTypeOptionB($tblTypeOptionB);
        $Entity->setServiceTblCompany($tblCompany);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

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

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = new TblProspect();
        $Entity->setServiceTblPerson($tblPerson);
        $Entity->setTblProspectAppointment($tblProspectAppointment);
        $Entity->setTblProspectReservation($tblProspectReservation);
        $Entity->setRemark($Remark);
        $Manager->saveEntity($Entity);
        Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspect
     */
    public function getProspectById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblProspect', $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectAppointment
     */
    public function getProspectAppointmentById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblProspectAppointment',
            $Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectReservation
     */
    public function getProspectReservationById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblProspectReservation',
            $Id);
    }

    /**
     * @param TblProspectAppointment $tblProspectAppointment
     * @param string                 $ReservationDate
     * @param string                 $InterviewDate
     * @param string                 $TrialDate
     *
     * @return bool
     */
    public function updateProspectAppointment(
        TblProspectAppointment $tblProspectAppointment,
        $ReservationDate,
        $InterviewDate,
        $TrialDate
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblProspectAppointment $Entity */
        $Entity = $Manager->getEntityById('TblProspectAppointment', $tblProspectAppointment->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setReservationDate(( $ReservationDate ? new \DateTime($ReservationDate) : null ));
            $Entity->setInterviewDate(( $InterviewDate ? new \DateTime($InterviewDate) : null ));
            $Entity->setTrialDate(( $TrialDate ? new \DateTime($TrialDate) : null ));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblProspectReservation $tblProspectReservation
     * @param string                 $ReservationYear
     * @param string                 $ReservationDivision
     * @param null|TblType           $tblTypeOptionA
     * @param null|TblType           $tblTypeOptionB
     * @param TblCompany|null        $tblCompany
     *
     * @return bool
     */
    public function updateProspectReservation(
        TblProspectReservation $tblProspectReservation,
        $ReservationYear,
        $ReservationDivision,
        TblType $tblTypeOptionA = null,
        TblType $tblTypeOptionB = null,
        TblCompany $tblCompany = null
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblProspectReservation $Entity */
        $Entity = $Manager->getEntityById('TblProspectReservation', $tblProspectReservation->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setReservationYear($ReservationYear);
            $Entity->setReservationDivision($ReservationDivision);
            $Entity->setServiceTblTypeOptionA($tblTypeOptionA);
            $Entity->setServiceTblTypeOptionB($tblTypeOptionB);
            $Entity->setServiceTblCompany($tblCompany);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblProspect $tblProspect
     * @param string      $Remark
     *
     * @return bool
     */
    public function updateProspect(
        TblProspect $tblProspect,
        $Remark
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblProspect $Entity */
        $Entity = $Manager->getEntityById('TblProspect', $tblProspect->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setRemark($Remark);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @return false|TblProspectReservation[]
     */
    public function getProspectReservationAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(),
            'TblProspectReservation');
    }

    /**
     * @param array $FilterList
     *
     * @return array|float|int|string
     */
    public function fetchIdPersonByFilter(array $FilterList = array())
    {

        $Manager = $this->getConnection()->getEntityManager();
        $queryBuilder = $Manager->getQueryBuilder();
        $Prospect = new TblProspect();
        $ProspectReservation = new TblProspectReservation();
        $ProspectAppointment = new TblProspectAppointment();
        $query = $queryBuilder->select('tP.serviceTblPerson as PersonId, tPR.ReservationYear, tPR.ReservationDivision,
        tPR.serviceTblTypeOptionA as ReservationOptionA, tPR.serviceTblTypeOptionB as ReservationOptionB, tPA.ReservationDate, tPA.InterviewDate, tPA.TrialDate')
            ->from($Prospect->getEntityFullName(), 'tP')
            ->leftJoin($ProspectReservation->getEntityFullName(), 'tPR', 'WITH', 'tPR.Id = tP.tblProspectReservation')
            ->leftJoin($ProspectAppointment->getEntityFullName(), 'tPA', 'WITH', 'tPA.Id = tP.tblProspectAppointment')
            ->where($queryBuilder->expr()->isNull('tP.EntityRemove'));
        $ParameterCount = 1;
        foreach($FilterList as $FilterName => $FilterValue){
            if($FilterValue != null){
                if($FilterName == 'TblProspectReservation_ReservationYear'){
                    $query->andWhere($queryBuilder->expr()->eq('tPR.ReservationYear', '?'.$ParameterCount))
                        ->setParameter($ParameterCount++, $FilterValue);
                }
                if($FilterName == 'TblProspectReservation_ReservationDivision'){
                    $query->andWhere($queryBuilder->expr()->eq('tPR.ReservationDivision', '?'.$ParameterCount))
                        ->setParameter($ParameterCount++, $FilterValue);
                }
                if($FilterName == 'TblProspectReservation_serviceTblTypeOptionA'
                && $FilterValue != 0){
                    $query->andWhere(
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->eq('tPR.serviceTblTypeOptionA', '?'.$ParameterCount),
                            $queryBuilder->expr()->eq('tPR.serviceTblTypeOptionB', '?'.$ParameterCount)
                        ))
                        ->setParameter($ParameterCount++, $FilterValue);
                }
            }
        }
        $query = $query->getQuery();
        return $query->getResult();
    }

    /**
     * @param TblProspect $tblProspect
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyProspect(TblProspect $tblProspect, $IsSoftRemove = false)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblProspect $Entity */
        $Entity = $Manager->getEntityById('TblProspect', $tblProspect->getId());
        if (null !== $Entity) {
            Protocol::useService()->createDeleteEntry($this->getConnection()->getDatabase(), $Entity);
            if ($IsSoftRemove) {
                $Manager->removeEntity($Entity);
            } else {
                $Manager->killEntity($Entity);
            }
            return true;
        }
        return false;
    }

    /**
     * @param TblProspect $tblProspect
     *
     * @return bool
     */
    public function restoreProspect(TblProspect $tblProspect)
    {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var TblProspect $Entity */
        $Entity = $Manager->getEntityById('TblProspect', $tblProspect->getId());
        $Protocol = clone $Entity;
        if (null !== $Entity) {
            $Entity->setEntityRemove(null);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}
