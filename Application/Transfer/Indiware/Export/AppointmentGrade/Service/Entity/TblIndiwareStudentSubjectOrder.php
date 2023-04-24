<?php

namespace SPHERE\Application\Transfer\Indiware\Export\AppointmentGrade\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblIndiwareStudentSubjectOrder")
 * @Cache(usage="READ_ONLY")
 */
class TblIndiwareStudentSubjectOrder extends Element
{

    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblTask;
    /**
     * @Column(type="integer")
     */
    protected $Period;
    /**
     * @Column(type="string")
     */
    protected $FirstName;
    /**
     * @Column(type="string")
     */
    protected $LastName;
    /**
     * @Column(type="string")
     */
    protected $Birthday;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="string")
     */
    protected $Subject1;
    /**
     * @Column(type="string")
     */
    protected $Subject2;
    /**
     * @Column(type="string")
     */
    protected $Subject3;
    /**
     * @Column(type="string")
     */
    protected $Subject4;
    /**
     * @Column(type="string")
     */
    protected $Subject5;
    /**
     * @Column(type="string")
     */
    protected $Subject6;
    /**
     * @Column(type="string")
     */
    protected $Subject7;
    /**
     * @Column(type="string")
     */
    protected $Subject8;
    /**
     * @Column(type="string")
     */
    protected $Subject9;
    /**
     * @Column(type="string")
     */
    protected $Subject10;
    /**
     * @Column(type="string")
     */
    protected $Subject11;
    /**
     * @Column(type="string")
     */
    protected $Subject12;
    /**
     * @Column(type="string")
     */
    protected $Subject13;
    /**
     * @Column(type="string")
     */
    protected $Subject14;
    /**
     * @Column(type="string")
     */
    protected $Subject15;
    /**
     * @Column(type="string")
     */
    protected $Subject16;
    /**
     * @Column(type="string")
     */
    protected $Subject17;

    /**
     * @return bool|TblTask
     */
    public function getServiceTblTask()
    {
        if (null === $this->serviceTblTask) {
            return false;
        } else {
            return Grade::useService()->getTaskById($this->serviceTblTask);
        }
    }

    /**
     * @param ?TblTask $tblTask
     */
    public function setServiceTblTask(?TblTask $tblTask)
    {
        $this->serviceTblTask = (null === $tblTask ? null : $tblTask->getId());
    }

    /**
     * @return int
     * 1 = 11 first Period
     * 2 = 11 second Period
     * 3 = 12 first Period
     * 4 = 12 second Period
     */
    public function getPeriod()
    {
        return $this->Period;
    }

    /**
     * @param int $Period
     * 1 = 11 first Period
     * 2 = 11 second Period
     * 3 = 12 first Period
     * 4 = 12 second Period
     */
    public function setPeriod($Period)
    {
        $this->Period = $Period;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {

        return $this->FirstName;
    }

    /**
     * @param string $FirstName
     */
    public function setFirstName($FirstName)
    {

        $this->FirstName = $FirstName;
    }

    /**
     * @return string
     */
    public function getLastName()
    {

        return $this->LastName;
    }

    /**
     * @param string $LastName
     */
    public function setLastName($LastName)
    {

        $this->LastName = $LastName;
    }

    /**
     * @return string
     */
    public function getBirthday()
    {

        return $this->Birthday;
    }

    /**
     * @param string $Birthday
     */
    public function setBirthday($Birthday)
    {

        $this->Birthday = $Birthday;
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
     * @param TblPerson $tblPerson
     */
    public function setServiceTblPerson($tblPerson = null)
    {

        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return string
     */
    public function getSubject1()
    {

        return $this->Subject1;
    }

    /**
     * @param string $Subject1
     */
    public function setSubject1($Subject1)
    {

        $this->Subject1 = $Subject1;
    }

    /**
     * @return string
     */
    public function getSubject2()
    {

        return $this->Subject2;
    }

    /**
     * @param string $Subject2
     */
    public function setSubject2($Subject2)
    {

        $this->Subject2 = $Subject2;
    }

    /**
     * @return string
     */
    public function getSubject3()
    {

        return $this->Subject3;
    }

    /**
     * @param string $Subject3
     */
    public function setSubject3($Subject3)
    {

        $this->Subject3 = $Subject3;
    }

    /**
     * @return string
     */
    public function getSubject4()
    {

        return $this->Subject4;
    }

    /**
     * @param string $Subject4
     */
    public function setSubject4($Subject4)
    {

        $this->Subject4 = $Subject4;
    }

    /**
     * @return string
     */
    public function getSubject5()
    {

        return $this->Subject5;
    }

    /**
     * @param string $Subject5
     */
    public function setSubject5($Subject5)
    {

        $this->Subject5 = $Subject5;
    }

    /**
     * @return string
     */
    public function getSubject6()
    {

        return $this->Subject6;
    }

    /**
     * @param string $Subject6
     */
    public function setSubject6($Subject6)
    {

        $this->Subject6 = $Subject6;
    }

    /**
     * @return string
     */
    public function getSubject7()
    {

        return $this->Subject7;
    }

    /**
     * @param string $Subject7
     */
    public function setSubject7($Subject7)
    {

        $this->Subject7 = $Subject7;
    }

    /**
     * @return string
     */
    public function getSubject8()
    {

        return $this->Subject8;
    }

    /**
     * @param string $Subject8
     */
    public function setSubject8($Subject8)
    {

        $this->Subject8 = $Subject8;
    }

    /**
     * @return string
     */
    public function getSubject9()
    {

        return $this->Subject9;
    }

    /**
     * @param string $Subject9
     */
    public function setSubject9($Subject9)
    {

        $this->Subject9 = $Subject9;
    }

    /**
     * @return string
     */
    public function getSubject10()
    {

        return $this->Subject10;
    }

    /**
     * @param string $Subject10
     */
    public function setSubject10($Subject10)
    {

        $this->Subject10 = $Subject10;
    }

    /**
     * @return string
     */
    public function getSubject11()
    {

        return $this->Subject11;
    }

    /**
     * @param string $Subject11
     */
    public function setSubject11($Subject11)
    {

        $this->Subject11 = $Subject11;
    }

    /**
     * @return string
     */
    public function getSubject12()
    {

        return $this->Subject12;
    }

    /**
     * @param string $Subject12
     */
    public function setSubject12($Subject12)
    {

        $this->Subject12 = $Subject12;
    }

    /**
     * @return string
     */
    public function getSubject13()
    {

        return $this->Subject13;
    }

    /**
     * @param string $Subject13
     */
    public function setSubject13($Subject13)
    {

        $this->Subject13 = $Subject13;
    }

    /**
     * @return string
     */
    public function getSubject14()
    {

        return $this->Subject14;
    }

    /**
     * @param string $Subject14
     */
    public function setSubject14($Subject14)
    {

        $this->Subject14 = $Subject14;
    }

    /**
     * @return string
     */
    public function getSubject15()
    {

        return $this->Subject15;
    }

    /**
     * @param string $Subject15
     */
    public function setSubject15($Subject15)
    {

        $this->Subject15 = $Subject15;
    }

    /**
     * @return string
     */
    public function getSubject16()
    {

        return $this->Subject16;
    }

    /**
     * @param string $Subject16
     */
    public function setSubject16($Subject16)
    {

        $this->Subject16 = $Subject16;
    }

    /**
     * @return string
     */
    public function getSubject17()
    {

        return $this->Subject17;
    }

    /**
     * @param string $Subject17
     */
    public function setSubject17($Subject17)
    {

        $this->Subject17 = $Subject17;
    }

}