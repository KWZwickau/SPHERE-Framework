<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentIntegration")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentIntegration extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;

    /**
     * @Column(type="datetime")
     */
    protected $CoachingRequestDate;
    /**
     * @Column(type="datetime")
     */
    protected $CoachingCounselDate;
    /**
     * @Column(type="datetime")
     */
    protected $CoachingDecisionDate;

    /**
     * @Column(type="boolean")
     */
    protected $CoachingRequired;

    /**
     * @Column(type="string")
     */
    protected $CoachingTime;
    /**
     * @Column(type="text")
     */
    protected $CoachingRemark;

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Student::useService()->getStudentById($this->serviceTblPerson);
        }
    }

    /**
     * @param null|TblPerson $serviceTblPerson
     */
    public function setServiceTblPerson(TblPerson $serviceTblPerson = null)
    {

        $this->serviceTblPerson = ( null === $serviceTblPerson ? null : $serviceTblPerson->getId() );
    }

    /**
     * @return string
     */
    public function getCoachingRequestDate()
    {

        if (null === $this->CoachingRequestDate) {
            return false;
        }
        /** @var \DateTime $IntegrationDate */
        $IntegrationDate = $this->CoachingRequestDate;
        if ($IntegrationDate instanceof \DateTime) {
            return $IntegrationDate->format('d.m.Y');
        } else {
            return (string)$IntegrationDate;
        }
    }

    /**
     * @param null|\DateTime $CoachingRequestDate
     */
    public function setCoachingRequestDate(\DateTime $CoachingRequestDate = null)
    {

        $this->CoachingRequestDate = $CoachingRequestDate;
    }

    /**
     * @return string
     */
    public function getCoachingCounselDate()
    {

        if (null === $this->CoachingCounselDate) {
            return false;
        }
        /** @var \DateTime $IntegrationDate */
        $IntegrationDate = $this->CoachingCounselDate;
        if ($IntegrationDate instanceof \DateTime) {
            return $IntegrationDate->format('d.m.Y');
        } else {
            return (string)$IntegrationDate;
        }
    }

    /**
     * @param null|\DateTime $CoachingCounselDate
     */
    public function setCoachingCounselDate(\DateTime $CoachingCounselDate = null)
    {

        $this->CoachingCounselDate = $CoachingCounselDate;
    }

    /**
     * @return string
     */
    public function getCoachingDecisionDate()
    {

        if (null === $this->CoachingDecisionDate) {
            return false;
        }
        /** @var \DateTime $IntegrationDate */
        $IntegrationDate = $this->CoachingDecisionDate;
        if ($IntegrationDate instanceof \DateTime) {
            return $IntegrationDate->format('d.m.Y');
        } else {
            return (string)$IntegrationDate;
        }
    }

    /**
     * @param null|\DateTime $CoachingDecisionDate
     */
    public function setCoachingDecisionDate(\DateTime $CoachingDecisionDate = null)
    {

        $this->CoachingDecisionDate = $CoachingDecisionDate;
    }

    /**
     * @return bool|TblCompany
     */
    public function getServiceTblCompany()
    {

        if (null === $this->serviceTblCompany) {
            return false;
        } else {
            return Company::useService()->getCompanyById($this->serviceTblCompany);
        }
    }

    /**
     * @param TblCompany|null $tblCompany
     */
    public function setServiceTblCompany(TblCompany $tblCompany = null)
    {

        $this->serviceTblCompany = ( null === $tblCompany ? null : $tblCompany->getId() );
    }

    /**
     * @return bool
     */
    public function getCoachingRequired()
    {

        return (bool)$this->CoachingRequired;
    }

    /**
     * @param bool $CoachingRequired
     */
    public function setCoachingRequired($CoachingRequired)
    {

        $this->CoachingRequired = (bool)$CoachingRequired;
    }

    /**
     * @return string
     */
    public function getCoachingTime()
    {

        return $this->CoachingTime;
    }

    /**
     * @param string $CoachingTime
     */
    public function setCoachingTime($CoachingTime)
    {

        $this->CoachingTime = $CoachingTime;
    }

    /**
     * @return string
     */
    public function getCoachingRemark()
    {

        return $this->CoachingRemark;
    }

    /**
     * @param string $CoachingRemark
     */
    public function setCoachingRemark($CoachingRemark)
    {

        $this->CoachingRemark = $CoachingRemark;
    }
}
