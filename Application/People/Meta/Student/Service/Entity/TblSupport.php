<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblSupport")
 * @Cache(usage="READ_ONLY")
 */
class TblSupport extends Element
{

    const SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;
    /**
     * @Column(type="datetime")
     */
    protected $Date;
    /**
     * @Column(type="bigint")
     */
    protected $tblSupportType;
    /**
     * @Column(type="string")
     */
    protected $Company;
    /**
     * @Column(type="bigint")
     */
    protected $PersonSupport;
    /**
     * @Column(type="string")
     */
    protected $SupportTime;
    /**
     * @Column(type="bigint")
     */
    protected $PersonEditor;
    /**
     * @Column(type="string")
     */
    protected $Remark;



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
    public function getDate()
    {

        if (null === $this->Date) {
            return false;
        }
        /** @var \DateTime $SchoolAttendanceStartDate */
        $SchoolAttendanceStartDate = $this->Date;
        if ($SchoolAttendanceStartDate instanceof \DateTime) {
            return $SchoolAttendanceStartDate->format('d.m.Y');
        } else {
            return (string)$SchoolAttendanceStartDate;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setDate(\DateTime $Date = null)
    {

        $this->Date = $Date;
    }

    /**
     * @return bool|TblSupportType
     */
    public function getTblSupportType()
    {

        if (null === $this->tblSupportType) {
            return false;
        } else {
            return Student::useService()->getSupportTypeById($this->tblSupportType);
        }
    }

    /**
     * @param null|TblSupportType $tblSupportType
     */
    public function setTblSupportTyp(TblSupportType $tblSupportType = null)
    {

        $this->tblSupportType = ( null === $tblSupportType ? null : $tblSupportType->getId() );
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->Company;
    }

    /**
     * @param string $Company
     */
    public function setCompany($Company = '')
    {
        $this->Company = $Company;
    }

    /**
     * @return string
     */
    public function getPersonSupport()
    {

        return $this->PersonSupport;
    }

    /**
     * @param string $PersonSupport
     */
    public function setPersonSupport($PersonSupport = '')
    {

        $this->PersonSupport = $PersonSupport;
    }

    /**
     * @return string
     */
    public function getSupportTime()
    {

        return $this->SupportTime;
    }

    /**
     * @param string $SupportTime
     */
    public function setSupportTime($SupportTime)
    {

        $this->SupportTime = $SupportTime;
    }

    /**
     * @return string
     */
    public function getPersonEditor()
    {

        return $this->PersonEditor;
    }

    /**
     * @param string $PersonEditor
     */
    public function setPersonEditor($PersonEditor = '')
    {

        $this->PersonEditor = $PersonEditor;
    }

    /**
     * @return string
     */
    public function getRemark()
    {

        return nl2br($this->Remark);
    }

    /**
     * @param string $Remark
     */
    public function setRemark($Remark)
    {

        $this->Remark = $Remark;
    }
}
