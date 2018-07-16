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
 * @Table(name="tblHandyCap")
 * @Cache(usage="READ_ONLY")
 */
class TblHandyCap extends Element
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
     * @param string $SupportTime
     */
    public function setSupportTime($SupportTime)
    {

        $this->SupportTime = $SupportTime;
    }

    /**
     * @return false|TblPerson
     */
    public function getPersonEditor()
    {

        return $this->PersonEditor;
    }

    /**
     * @param string $PersonEditor
     */
    public function setPersonEditor($PersonEditor = null)
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
