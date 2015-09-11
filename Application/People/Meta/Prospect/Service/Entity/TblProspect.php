<?php
namespace SPHERE\Application\People\Meta\Prospect\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Prospect\Prospect;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblProspect")
 * @Cache(usage="READ_ONLY")
 */
class TblProspect extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="text")
     */
    protected $Remark;
    /**
     * @Column(type="bigint")
     */
    protected $tblProspectAppointment;
    /**
     * @Column(type="bigint")
     */
    protected $tblProspectReservation;

    /**
     * @return string
     */
    public function getRemark()
    {

        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark($Remark)
    {

        $this->Remark = $Remark;
    }

    /**
     * @return bool|TblProspectAppointment
     */
    public function getTblProspectAppointment()
    {

        if (null === $this->tblProspectAppointment) {
            return false;
        } else {
            return Prospect::useService()->getProspectAppointmentById($this->tblProspectAppointment);
        }
    }

    /**
     * @param null|TblProspectAppointment $tblProspectAppointment
     */
    public function setTblProspectAppointment(TblProspectAppointment $tblProspectAppointment = null)
    {

        $this->tblProspectAppointment = ( null === $tblProspectAppointment ? null : $tblProspectAppointment->getId() );
    }

    /**
     * @return bool|TblProspectReservation
     */
    public function getTblProspectReservation()
    {

        if (null === $this->tblProspectReservation) {
            return false;
        } else {
            return Prospect::useService()->getProspectReservationById($this->tblProspectReservation);
        }
    }

    /**
     * @param null|TblProspectReservation $tblProspectReservation
     */
    public function setTblProspectReservation(TblProspectReservation $tblProspectReservation = null)
    {

        $this->tblProspectReservation = ( null === $tblProspectReservation ? null : $tblProspectReservation->getId() );
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }
}
