<?php
namespace SPHERE\Application\People\Meta\Teacher\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblTeacher")
 * @Cache(usage="READ_ONLY")
 */
class TblTeacher extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_ACRONYM = 'Acronym';
    const ATTR_EMPLOYMENT_START = 'EmploymentStart';
    const ATTR_EMPLOYMENT_END = 'EmploymentEnd';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="string")
     */
    protected $Acronym;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $EmploymentStart;

    /**
     * @Column(type="datetime", nullable=true)
     */
    protected $EmploymentEnd;

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

    /**
     * @return string
     */
    public function getAcronym()
    {
        return $this->Acronym;
    }

    /**
     * @param string $Acronym
     */
    public function setAcronym($Acronym)
    {
        $this->Acronym = $Acronym;
    }

    /**
     * @param string $format
     * @return false|string
     */
    public function getEmploymentStart($format = 'd.m.Y')
    {
        if (null === $this->EmploymentStart) {
            return '';
        }
        /** @var \DateTime $EmploymentStart */
        $EmploymentStart = $this->EmploymentStart;
        if ($EmploymentStart instanceof \DateTime) {
            return $EmploymentStart->format($format);
        }
        return (string)$EmploymentStart;
    }

    /**
     * @param \DateTime|null $EmploymentStart
     */
    public function setEmploymentStart(\DateTime $EmploymentStart = null)
    {
        $this->EmploymentStart = $EmploymentStart;
    }

    /**
     * @param string $format
     * @return false|string
     */
    public function getEmploymentEnd($format = 'd.m.Y')
    {
        if (null === $this->EmploymentEnd) {
            return '';
        }
        /** @var \DateTime $EmploymentEnd */
        $EmploymentEnd = $this->EmploymentEnd;
        if ($EmploymentEnd instanceof \DateTime) {
            return $EmploymentEnd->format($format);
        }
        return (string)$EmploymentEnd;
    }

    /**
     * @param \DateTime|null $EmploymentEnd
     */
    public function setEmploymentEnd(\DateTime $EmploymentEnd = null)
    {
        $this->EmploymentEnd = $EmploymentEnd;
    }

}