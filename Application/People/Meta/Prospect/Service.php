<?php
namespace SPHERE\Application\People\Meta\Prospect;

use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Prospect\Service\Data;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\ViewPeopleMetaProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Binding\AbstractService;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Prospect
 */
class Service extends AbstractService
{

    /**
     * @return false|ViewPeopleMetaProspect[]
     */
    public function viewPeopleMetaProspect()
    {

        return ( new Data($this->getBinding()) )->viewPeopleMetaProspect();
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->getBinding()))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param TblPerson $tblPerson
     * @param $Meta
     *
     * @return bool|TblProspect
     */
    public function updateMetaService(TblPerson $tblPerson, $Meta)
    {
        if (($tblProspect = $this->getProspectByPerson($tblPerson))) {
            (new Data($this->getBinding()))->updateProspectAppointment(
                $tblProspect->getTblProspectAppointment(),
                $Meta['Appointment']['ReservationDate'],
                $Meta['Appointment']['InterviewDate'],
                $Meta['Appointment']['TrialDate']
            );
            $OptionA = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionA']);
            $OptionB = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionB']);
            (new Data($this->getBinding()))->updateProspectReservation(
                $tblProspect->getTblProspectReservation(),
                $Meta['Reservation']['Year'],
                $Meta['Reservation']['Division'],
                ($OptionA ? $OptionA : null),
                ($OptionB ? $OptionB : null)
            );

            return (new Data($this->getBinding()))->updateProspect(
                $tblProspect,
                $Meta['Remark']
            );
        } else {
            $tblProspectAppointment = (new Data($this->getBinding()))->createProspectAppointment(
                $Meta['Appointment']['ReservationDate'],
                $Meta['Appointment']['InterviewDate'],
                $Meta['Appointment']['TrialDate']
            );
            $OptionA = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionA']);
            $OptionB = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionB']);
            $tblProspectReservation = (new Data($this->getBinding()))->createProspectReservation(
                $Meta['Reservation']['Year'],
                $Meta['Reservation']['Division'],
                ($OptionA ? $OptionA : null),
                ($OptionB ? $OptionB : null)
            );

            return (new Data($this->getBinding()))->createProspect(
                $tblPerson,
                $tblProspectAppointment,
                $tblProspectReservation,
                $Meta['Remark']
            );
        }
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

        return (new Data($this->getBinding()))->getProspectByPerson($tblPerson, $isForced);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspect
     */
    public function getProspectById($Id)
    {

        return (new Data($this->getBinding()))->getProspectById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectAppointment
     */
    public function getProspectAppointmentById($Id)
    {

        return (new Data($this->getBinding()))->getProspectAppointmentById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectReservation
     */
    public function getProspectReservationById($Id)
    {

        return (new Data($this->getBinding()))->getProspectReservationById($Id);
    }

    /**
     * @param TblPerson $tblPerson
     * @param string $ReservationDate
     * @param string $InterviewDate
     * @param string $TrialDate
     * @param              $ReservationYear
     * @param              $ReservationDivision
     * @param TblType|null $tblTypeOptionA
     * @param TblType|null $tblTypeOptionB
     * @param              $Remark
     *
     * @return TblProspect
     */
    public function insertMeta(
        TblPerson $tblPerson,
        $ReservationDate,
        $InterviewDate,
        $TrialDate,
        $ReservationYear,
        $ReservationDivision,
        TblType $tblTypeOptionA = null,
        TblType $tblTypeOptionB = null,
        $Remark
    ) {

        $tblProspectAppointment = (new Data($this->getBinding()))->createProspectAppointment(
            $ReservationDate,
            $InterviewDate,
            $TrialDate
        );
        $tblProspectReservation = (new Data($this->getBinding()))->createProspectReservation(
            $ReservationYear,
            $ReservationDivision,
            $tblTypeOptionA,
            $tblTypeOptionB
        );
        return (new Data($this->getBinding()))->createProspect(
            $tblPerson,
            $tblProspectAppointment,
            $tblProspectReservation,
            $Remark
        );
    }

    /**
     * @return false|TblProspectReservation[]
     */
    public function getProspectReservationAll()
    {

        return (new Data($this->getBinding()))->getProspectReservationAll();
    }

    /**
     * @param TblProspect $tblProspect
     * @param bool $IsSoftRemove
     *
     * @return bool
     */
    public function destroyProspect(TblProspect $tblProspect, $IsSoftRemove = false)
    {

        return (new Data($this->getBinding()))->destroyProspect($tblProspect, $IsSoftRemove);
    }

    /**
     * @param TblProspect $tblProspect
     *
     * @return bool
     */
    public function restoreProspect(TblProspect $tblProspect)
    {

        return (new Data($this->getBinding()))->restoreProspect($tblProspect);
    }
}
