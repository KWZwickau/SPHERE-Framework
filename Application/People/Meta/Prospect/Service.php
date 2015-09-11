<?php
namespace SPHERE\Application\People\Meta\Prospect;

use SPHERE\Application\IServiceInterface;
use SPHERE\Application\People\Meta\Prospect\Service\Data;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspect;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectAppointment;
use SPHERE\Application\People\Meta\Prospect\Service\Entity\TblProspectReservation;
use SPHERE\Application\People\Meta\Prospect\Service\Setup;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\Common\Frontend\Form\IFormInterface;
use SPHERE\Common\Frontend\Message\Repository\Success;
use SPHERE\Common\Window\Redirect;
use SPHERE\System\Database\Fitting\Binding;
use SPHERE\System\Database\Fitting\Structure;
use SPHERE\System\Database\Link\Identifier;

/**
 * Class Service
 *
 * @package SPHERE\Application\People\Meta\Prospect
 */
class Service implements IServiceInterface
{

    /** @var null|Binding */
    private $Binding = null;
    /** @var null|Structure */
    private $Structure = null;

    /**
     * Define Database Connection
     *
     * @param Identifier $Identifier
     * @param string     $EntityPath
     * @param string     $EntityNamespace
     */
    public function __construct(Identifier $Identifier, $EntityPath, $EntityNamespace)
    {

        $this->Binding = new Binding($Identifier, $EntityPath, $EntityNamespace);
        $this->Structure = new Structure($Identifier);
    }

    /**
     * @param bool $doSimulation
     * @param bool $withData
     *
     * @return string
     */
    public function setupService($doSimulation, $withData)
    {

        $Protocol = (new Setup($this->Structure))->setupDatabaseSchema($doSimulation);
        if (!$doSimulation && $withData) {
            (new Data($this->Binding))->setupDatabaseContent();
        }
        return $Protocol;
    }

    /**
     * @param IFormInterface $Form
     * @param TblPerson      $tblPerson
     * @param array          $Meta
     *
     * @return IFormInterface|Redirect
     */
    public function createMeta(IFormInterface $Form = null, TblPerson $tblPerson, $Meta)
    {

        /**
         * Skip to Frontend
         */
        if (null === $Meta) {
            return $Form;
        }

        $tblProspect = $this->getProspectByPerson($tblPerson);
        if ($tblProspect) {
            (new Data($this->Binding))->updateProspectAppointment(
                $tblProspect->getTblProspectAppointment(),
                $Meta['Appointment']['ReservationDate'],
                $Meta['Appointment']['InterviewDate'],
                $Meta['Appointment']['TrialDate']
            );
            (new Data($this->Binding))->updateProspectReservation(
                $tblProspect->getTblProspectReservation(),
                $Meta['Reservation']['Year'],
                $Meta['Reservation']['Division']
            );
            (new Data($this->Binding))->updateProspect(
                $tblProspect,
                $Meta['Remark']
            );
        } else {
            $tblProspectAppointment = (new Data($this->Binding))->createProspectAppointment(
                $Meta['Appointment']['ReservationDate'],
                $Meta['Appointment']['InterviewDate'],
                $Meta['Appointment']['TrialDate']
            );
            $tblProspectReservation = (new Data($this->Binding))->createProspectReservation(
                $Meta['Reservation']['Year'],
                $Meta['Reservation']['Division']
            );
            (new Data($this->Binding))->createProspect(
                $tblPerson,
                $tblProspectAppointment,
                $tblProspectReservation,
                $Meta['Remark']
            );
        }
        return new Success('Die Daten wurde erfolgreich gespeichert')
        .new Redirect('/People/Person', 3, array('Id' => $tblPerson->getId()));
    }

    /**
     *
     * @param TblPerson $tblPerson
     *
     * @return bool|TblProspect
     */
    public function getProspectByPerson(TblPerson $tblPerson)
    {

        return (new Data($this->Binding))->getProspectByPerson($tblPerson);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspect
     */
    public function getProspectById($Id)
    {

        return (new Data($this->Binding))->getProspectById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectAppointment
     */
    public function getProspectAppointmentById($Id)
    {

        return (new Data($this->Binding))->getProspectAppointmentById($Id);
    }

    /**
     * @param int $Id
     *
     * @return bool|TblProspectReservation
     */
    public function getProspectReservationById($Id)
    {

        return (new Data($this->Binding))->getProspectReservationById($Id);
    }
}
