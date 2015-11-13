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
 * @Table(name="tblStudentDisorder")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentDisorder extends Element
{

    const ATTR_TBL_STUDENT = 'tblStudent';
    const ATTR_TBL_STUDENT_DISORDER_TYPE = 'tblStudentDisorderType';

    /**
     * @Column(type="bigint")
     */
    protected $tblStudent;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentDisorderType;

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
     * @return bool|TblStudentDisorderType
     */
    public function getTblStudentDisorderType()
    {

        if (null === $this->tblStudentDisorderType) {
            return false;
        } else {
            return Student::useService()->getStudentDisorderTypeById($this->tblStudentDisorderType);
        }
    }

    /**
     * @param null|TblStudentDisorderType $tblStudentDisorderType
     */
    public function setTblStudentDisorderType(TblStudentDisorderType $tblStudentDisorderType = null)
    {

        $this->tblStudentDisorderType = ( null === $tblStudentDisorderType ? null : $tblStudentDisorderType->getId() );
    }
}
