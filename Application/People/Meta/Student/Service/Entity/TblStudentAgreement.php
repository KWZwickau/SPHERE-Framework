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
 * @Table(name="tblStudentAgreement")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentAgreement extends Element
{

    const ATTR_TBL_STUDENT = 'tblStudent';

    /**
     * @Column(type="bigint")
     */
    protected $tblStudent;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentAgreementType;

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
     * @return bool|TblStudentAgreementType
     */
    public function getTblStudentAgreementType()
    {

        if (null === $this->tblStudentAgreementType) {
            return false;
        } else {
            return Student::useService()->getStudentAgreementTypeById($this->tblStudentAgreementType);
        }
    }

    /**
     * @param TblStudentAgreementType|null $tblStudentAgreementType
     */
    public function setTblStudentAgreementType(TblStudentAgreementType $tblStudentAgreementType = null)
    {

        $this->tblStudentAgreementType = ( null === $tblStudentAgreementType ? null : $tblStudentAgreementType->getId() );
    }
}
