<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * e.g. 6 Alpha - Student
 *
 * @Entity
 * @Table(name="tblDivisionStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionStudent extends Element
{

    const ATTR_TBL_DIVISION = 'tblDivision';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $tblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="integer")
     */
    protected $SortOrder;

    /**
     * @Column(type="datetime")
     */
    protected $LeaveDate;

    /**
     * @Column(type="boolean")
     */
    protected $UseGradesInNewDivision;

    /**
     * @param bool $IsForce
     *
     * @return bool|TblDivision
     */
    public function getTblDivision($IsForce = false)
    {

        if (null === $this->tblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->tblDivision, $IsForce);
        }
    }

    /**
     * @param null|TblDivision $tblDivision
     */
    public function setTblDivision(TblDivision $tblDivision = null)
    {

        $this->tblDivision = ( null === $tblDivision ? null : $tblDivision->getId() );
    }

    /**
     * @param bool $IsForce
     *
     * @return bool|TblPerson
     */
    public function getServiceTblPerson($IsForce = false)
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson, $IsForce);
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
     * @return integer|null
     */
    public function getSortOrder()
    {
        return $this->SortOrder;
    }

    /**
     * @param integer|null $SortOrder
     */
    public function setSortOrder($SortOrder)
    {
        $this->SortOrder = $SortOrder;
    }

    /**
     * @return string
     */
    public function getLeaveDate()
    {

        if (null === $this->LeaveDate) {
            return false;
        }
        /** @var \DateTime $Date */
        $Date = $this->LeaveDate;
        if ($Date instanceof \DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|\DateTime $Date
     */
    public function setLeaveDate(\DateTime $Date = null)
    {

        $this->LeaveDate = $Date;
    }

    /**
     * @return boolean
     */
    public function getUseGradesInNewDivision()
    {
        return $this->UseGradesInNewDivision;
    }

    /**
     * @param boolean $UseGradesInNewDivision
     */
    public function setUseGradesInNewDivision($UseGradesInNewDivision)
    {
        $this->UseGradesInNewDivision = (boolean) $UseGradesInNewDivision;
    }

    /**
     * @return \DateTime|null
     */
    public function getLeaveDateTime()
    {

        return $this->LeaveDate;
    }

    /**
     * @return bool
     */
    public function isInActive()
    {
        $now = new \DateTime('now');

        return $this->getLeaveDateTime() !== null && $now > $this->getLeaveDateTime();
    }
}
