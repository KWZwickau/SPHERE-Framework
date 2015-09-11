<?php
namespace SPHERE\Application\People\Meta\Prospect\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblProspectAppointment")
 * @Cache(usage="READ_ONLY")
 */
class TblProspectAppointment extends Element
{

    /**
     * @Column(type="datetime")
     */
    protected $ReservationDate;
    /**
     * @Column(type="datetime")
     */
    protected $InterviewDate;
    /**
     * @Column(type="datetime")
     */
    protected $TrialDate;

    /**
     * @return string
     */
    public function getReservationDate()
    {

        if (null === $this->ReservationDate) {
            return false;
        }
        /** @var \DateTime $ReservationDate */
        $ReservationDate = $this->ReservationDate;
        if ($ReservationDate instanceof \DateTime) {
            return $ReservationDate->format('d.m.Y');
        } else {
            return (string)$ReservationDate;
        }
    }

    /**
     * @param null|\DateTime $ReservationDate
     */
    public function setReservationDate(\DateTime $ReservationDate = null)
    {

        $this->ReservationDate = $ReservationDate;
    }

    /**
     * @return string
     */
    public function getInterviewDate()
    {

        if (null === $this->InterviewDate) {
            return false;
        }
        /** @var \DateTime $InterviewDate */
        $InterviewDate = $this->InterviewDate;
        if ($InterviewDate instanceof \DateTime) {
            return $InterviewDate->format('d.m.Y');
        } else {
            return (string)$InterviewDate;
        }
    }

    /**
     * @param  null|\DateTime $InterviewDate
     */
    public function setInterviewDate(\DateTime $InterviewDate = null)
    {

        $this->InterviewDate = $InterviewDate;
    }

    /**
     * @return string
     */
    public function getTrialDate()
    {

        if (null === $this->TrialDate) {
            return false;
        }
        /** @var \DateTime $TrialDate */
        $TrialDate = $this->TrialDate;
        if ($TrialDate instanceof \DateTime) {
            return $TrialDate->format('d.m.Y');
        } else {
            return (string)$TrialDate;
        }
    }

    /**
     * @param null|\DateTime $TrialDate
     */
    public function setTrialDate(\DateTime $TrialDate = null)
    {

        $this->TrialDate = $TrialDate;
    }
}
