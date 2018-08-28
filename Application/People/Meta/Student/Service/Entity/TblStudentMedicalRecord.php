<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentMedicalRecord")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentMedicalRecord extends Element
{

    const ATTR_ATTENDING_DOCTOR = 'AttendingDoctor';

    /**
     * @Column(type="text")
     */
    protected $Disease;
    /**
     * @Column(type="text")
     */
    protected $Medication;
    /**
     * @Column(type="bigint")
     */
    protected $AttendingDoctor;
    /**
     * @Column(type="bigint")
     */
    protected $InsuranceState;
    /**
     * @Column(type="string")
     */
    protected $Insurance;

    /**
     * @return string
     */
    public function getDisease()
    {

        return $this->Disease;
    }

    /**
     * @param string $Disease
     */
    public function setDisease($Disease)
    {

        $this->Disease = $Disease;
    }

    /**
     * @return string
     */
    public function getMedication()
    {

        return $this->Medication;
    }

    /**
     * @param string $Medication
     */
    public function setMedication($Medication)
    {

        $this->Medication = $Medication;
    }

    /**
     * @return string
     */
    public function getAttendingDoctor()
    {

        return $this->AttendingDoctor;
    }

    /**
     * @param string $AttendingDoctor
     */
    public function setAttendingDoctor($AttendingDoctor = '')
    {

        $this->AttendingDoctor = $AttendingDoctor;
    }

    /**
     * @return int
     */
    public function getInsuranceState()
    {

        return $this->InsuranceState;
    }

    /**
     * @param int $InsuranceState
     */
    public function setInsuranceState($InsuranceState)
    {

        $this->InsuranceState = $InsuranceState;
    }

    /**
     * @return string
     */
    public function getInsurance()
    {

        return $this->Insurance;
    }

    /**
     * @param string $Insurance
     */
    public function setInsurance($Insurance)
    {

        $this->Insurance = $Insurance;
    }
}
