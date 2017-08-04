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
 * @Table(name="tblStudentFocus")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentFocus extends Element
{

    const ATTR_TBL_STUDENT = 'tblStudent';
    const ATTR_TBL_STUDENT_FOCUS_TYPE = 'tblStudentFocusType';
    const ATTR_IS_PRIMARY = 'IsPrimary';

    /**
     * @Column(type="bigint")
     */
    protected $tblStudent;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentFocusType;

    /**
     * @Column(type="boolean")
     */
    protected $IsPrimary;

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
     * @return bool|TblStudentFocusType
     */
    public function getTblStudentFocusType()
    {

        if (null === $this->tblStudentFocusType) {
            return false;
        } else {
            return Student::useService()->getStudentFocusTypeById($this->tblStudentFocusType);
        }
    }

    /**
     * @param null|TblStudentFocusType $tblStudentFocusType
     */
    public function setTblStudentFocusType(TblStudentFocusType $tblStudentFocusType = null)
    {

        $this->tblStudentFocusType = ( null === $tblStudentFocusType ? null : $tblStudentFocusType->getId() );
    }

    /**
     * @return boolean
     */
    public function isPrimary()
    {
        return (boolean) $this->IsPrimary;
    }

    /**
     * @param boolean $IsPrimary
     */
    public function setIsPrimary($IsPrimary)
    {
        $this->IsPrimary = (boolean) $IsPrimary;
    }
}
