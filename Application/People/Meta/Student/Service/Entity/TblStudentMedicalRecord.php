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
     * @Column(type="string")
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

    /**
     * @return string
     */
    public function getDisplayInsuranceState()
    {

        $result = '';
        switch ($this->getInsuranceState()) {
            case 1:  $result = 'Pflicht'; break;
            case 2:  $result = 'Freiwillig'; break;
            case 3:  $result = 'Privat'; break;
            case 4:  $result =' Familie Vater'; break;
            case 5:  $result = 'Familie Mutter'; break;
        }

        return $result;
    }

    public static function getInsuranceStateArray()
    {
        return array(
            0 => '',
            1 => 'Pflicht',
            2 => 'Freiwillig',
            3 => 'Privat',
            4 => 'Familie Vater',
            5 => 'Familie Mutter',
        );
    }
}
