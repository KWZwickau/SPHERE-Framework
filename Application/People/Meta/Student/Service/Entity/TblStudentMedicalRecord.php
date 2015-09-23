<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentMedicalRecord")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentMedicalRecord extends Element
{

    const SERVICE_TBL_PERSON_ATTENDING_DOCTOR = 'serviceTblPersonAttendingDoctor';
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
    protected $serviceTblPersonAttendingDoctor;
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
     * @return bool|TblPerson
     */
    public function getServiceTblPersonAttendingDoctor()
    {

        if (null === $this->serviceTblPersonAttendingDoctor) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonAttendingDoctor);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonAttendingDoctor(TblPerson $tblPerson = null)
    {

        $this->serviceTblPersonAttendingDoctor = ( null === $tblPerson ? null : $tblPerson->getId() );
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
