<?php

namespace SPHERE\Application\Education\ClassRegister\Instruction\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Digital\Digital;
use SPHERE\Application\Education\ClassRegister\Instruction\Instruction;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblClassRegisterInstructionItem")
 * @Cache(usage="READ_ONLY")
 */
class TblInstructionItem extends Element
{
    const ATTR_TBL_INSTRUCTION = 'tblClassRegisterInstruction';
    const ATTR_SERVICE_TBL_DIVISION_COURSE = 'serviceTblDivision';
    const ATTR_DATE = 'Date';
    const ATTR_IS_MAIN = 'IsMain';

    /**
     * @Column(type="bigint")
     */
    protected $tblClassRegisterInstruction;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGroup = null;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivisionSubject = null;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear = null;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="string")
     */
    protected string $Subject;

    /**
     * @Column(type="string")
     */
    protected string $Content;

    /**
     * @Column(type="boolean")
     */
    protected $IsMain;

    /**
     * @return bool|TblInstruction
     */
    public function getTblInstruction()
    {
        if (null === $this->tblClassRegisterInstruction) {
            return false;
        } else {
            return Instruction::useService()->getInstructionById($this->tblClassRegisterInstruction);
        }
    }

    /**
     * @param TblInstruction|null $tblInstruction
     */
    public function setTblInstruction(TblInstruction $tblInstruction = null)
    {
        $this->tblClassRegisterInstruction = (null === $tblInstruction ? null : $tblInstruction->getId());
    }

    /**
     * @return bool|TblDivisionCourse
     */
    public function getServiceTblDivisionCourse()
    {
        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivisionCourse $tblDivisionCourse
     */
    public function setServiceTblDivisionCourse(TblDivisionCourse $tblDivisionCourse)
    {
        $this->serviceTblDivision = $tblDivisionCourse->getId();
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
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {
        $this->serviceTblPerson = (null === $tblPerson ? null : $tblPerson->getId());
    }

    /**
     * @return string
     */
    public function getDate()
    {
        if (null === $this->Date) {
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|DateTime $Date
     */
    public function setDate(DateTime $Date = null)
    {
        $this->Date = $Date;
    }

    /**
     * @return string
     */
    public function getSubject(): string
    {
        return $this->Subject;
    }

    /**
     * @param string $Subject
     */
    public function setSubject(string $Subject): void
    {
        $this->Subject = $Subject;
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->Content;
    }

    /**
     * @param string $Content
     */
    public function setContent(string $Content)
    {
        $this->Content = $Content;
    }

    /**
     * @return bool
     */
    public function getIsMain()
    {
        return $this->IsMain;
    }

    /**
     * @param bool $IsMain
     */
    public function setIsMain($IsMain): void
    {
        $this->IsMain = (bool) $IsMain;
    }

    /**
     * @param bool $IsToolTip
     *
     * @return string
     */
    public function getTeacherString(bool $IsToolTip = true): string
    {
        return $this->getServiceTblPerson()
            ? Digital::useService()->getTeacherString($this->getServiceTblPerson(), $IsToolTip)
            : '';
    }
}