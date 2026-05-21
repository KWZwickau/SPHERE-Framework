<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentLiberation")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentLiberation extends Element
{

    const ATTR_TBL_STUDENT = 'tblStudent';
    const ATTR_TBL_STUDENT_LIBERATION_TYPE = 'tblStudentLiberationType';

    /**
     * @Column(type="bigint")
     */
    protected $tblStudent;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentLiberationType;
    /**
     * @Column(type="datetime")
     */
    protected $DateFrom = null;
    /**
     * @Column(type="datetime")
     */
    protected $DateTo = null;
    /**
     * @Column(type="text")
     */
    protected $Description;

    /**
     * @return bool|TblStudent
     */
    public function getTblStudent()
    {

        if (null === $this->tblStudent) {
            return false;
        } else {
            return Student::useService()->getStudentById($this->tblStudent);
        }
    }

    /**
     * @param null|TblStudent $tblStudent
     */
    public function setTblStudent(TblStudent $tblStudent = null)
    {

        $this->tblStudent = ( null === $tblStudent ? null : $tblStudent->getId() );
    }

    /**
     * @return bool|TblStudentLiberationType
     */
    public function getTblStudentLiberationType()
    {

        if (null === $this->tblStudentLiberationType) {
            return false;
        } else {
            return Student::useService()->getStudentLiberationTypeById($this->tblStudentLiberationType);
        }
    }

    /**
     * @param TblStudentLiberationType|null $tblStudentLiberationType
     */
    public function setTblStudentLiberationType(TblStudentLiberationType $tblStudentLiberationType = null)
    {

        $this->tblStudentLiberationType = ( null === $tblStudentLiberationType ? null : $tblStudentLiberationType->getId() );
    }

    /**
     * @param $getDateTimeObjekt
     *
     * @return string|\DateTime
     */
    public function getDateFrom($getDateTimeObjekt = false): string|\DateTime
    {

        if(null === $this->DateFrom){
            return '';
        }
        /** @var \DateTime $Date */
        $Date = $this->DateFrom;
        if($Date instanceof \DateTime){
            if($getDateTimeObjekt){
                return $Date;
            } else {
                return $Date->format('d.m.Y');
            }
        } else {
            if($getDateTimeObjekt){
                return new \DateTime($Date);
            } else {
                return (string)$Date;
            }
        }
    }

    /**
     * @param \DateTime|null $DateFrom
     *
     * @return void
     */
    public function setDateFrom(?\DateTime $DateFrom): void
    {
        $this->DateFrom = $DateFrom;
    }

    /**
     * @param $getDateTimeObjekt
     *
     * @return string|\DateTime
     */
    public function getDateTo($getDateTimeObjekt = false): string|\DateTime
    {
        if(null === $this->DateTo){
            return '';
        }
        /** @var \DateTime $Date */
        $Date = $this->DateTo;
        if($Date instanceof \DateTime){
            if($getDateTimeObjekt){
                return $Date;
            } else {
                return $Date->format('d.m.Y');
            }
        } else {
            if($getDateTimeObjekt){
                return new \DateTime($Date);
            } else {
                return (string)$Date;
            }
        }
    }

    /**
     * @param \DateTime|null $DateTo
     *
     * @return void
     */
    public function setDateTo(?\DateTime $DateTo): void
    {
        $this->DateTo = $DateTo;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription(string $Description = ''): void
    {
        $this->Description = $Description;
    }
}
