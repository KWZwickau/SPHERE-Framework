<?php
namespace SPHERE\Application\People\Meta\Prospect;

use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Prospect\Service\Data;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
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
     * @param bool $doSimulation
     * @param bool $withData
     * @param bool $UTF8
     *
     * @return string
     */
    public function setupService($doSimulation, $withData, $UTF8)
    {

        $Protocol= '';
        if(!$withData){
            $Protocol = (new Setup($this->getStructure()))->setupDatabaseSchema($doSimulation, $UTF8);
        }
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

        $OptionA = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionA']);
        $OptionB = Type::useService()->getTypeById($Meta['Reservation']['SchoolTypeOptionB']);
        if(isset($Meta['Reservation']['TblCompany'])){
            $tblCompany = Company::useService()->getCompanyById($Meta['Reservation']['TblCompany']);
        } else {
            $tblCompany = false;
        }

        if (($tblProspect = $this->getProspectByPerson($tblPerson))) {
            (new Data($this->getBinding()))->updateProspectAppointment(
                $tblProspect->getTblProspectAppointment(),
                $Meta['Appointment']['ReservationDate'],
                $Meta['Appointment']['InterviewDate'],
                $Meta['Appointment']['TrialDate']
            );
            (new Data($this->getBinding()))->updateProspectReservation(
                $tblProspect->getTblProspectReservation(),
                $Meta['Reservation']['Year'],
                $Meta['Reservation']['Division'],
                ($OptionA ? $OptionA : null),
                ($OptionB ? $OptionB : null),
                ($tblCompany ? $tblCompany : null)
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
            $tblProspectReservation = (new Data($this->getBinding()))->createProspectReservation(
                $Meta['Reservation']['Year'],
                $Meta['Reservation']['Division'],
                ($OptionA ? $OptionA : null),
                ($OptionB ? $OptionB : null),
                ($tblCompany ? $tblCompany : null)
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
     * @return false|TblProspectReservation[]
     */
    public function getProspectReservationAll()
    {

        return (new Data($this->getBinding()))->getProspectReservationAll();
    }

    /**
     * @param array $FilterList
     *
     * @return mixed
     */
    public function fetchIdPersonByFilter(array $FilterList = array())
    {

        return (new Data($this->getBinding()))->fetchIdPersonByFilter($FilterList);
    }

    /**
     * @param TblPerson       $tblPerson
     * @param string          $ReservationDate
     * @param string          $InterviewDate
     * @param string          $TrialDate
     * @param string          $ReservationYear
     * @param string          $ReservationDivision
     * @param TblType|null    $tblTypeOptionA
     * @param TblType|null    $tblTypeOptionB
     * @param TblCompany|null $tblCompany
     * @param string          $Remark
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
        TblCompany $tblCompany = null,
        $Remark = ''
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
            $tblTypeOptionB,
            $tblCompany
        );
        return (new Data($this->getBinding()))->createProspect(
            $tblPerson,
            $tblProspectAppointment,
            $tblProspectReservation,
            $Remark
        );
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
