<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 22.05.2018
 * Time: 10:29
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblLeaveAdditionalGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblLeaveAdditionalGrade extends Element
{

    const ATTR_TBL_LEAVE_STUDENT = 'tblLeaveStudent';
    const ATTR_TBL_PREPARE_ADDITIONAL_GRADE_TYPE = 'tblPrepareAdditionalGradeType';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';

    /**
     * @Column(type="bigint")
     */
    protected $tblLeaveStudent;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="string")
     */
    protected $Grade;

    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;

    /**
     * @Column(type="bigint")
     */
    protected $tblPrepareAdditionalGradeType;

    /**
     * @return false|TblLeaveStudent
     */
    public function getTblLeaveStudent()
    {

        if (null === $this->tblLeaveStudent) {
            return false;
        } else {
            return Prepare::useService()->getLeaveStudentById($this->tblLeaveStudent);
        }
    }

    /**
     * @param TblLeaveStudent|null $tblLeaveStudent
     */
    public function setTblLeaveStudent(TblLeaveStudent $tblLeaveStudent = null)
    {

        $this->tblLeaveStudent = (null === $tblLeaveStudent ? null : $tblLeaveStudent->getId());
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject()
    {

        if (null === $this->serviceTblSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return string
     */
    public function getGrade()
    {
        return $this->Grade;
    }

    /**
     * @param string $Grade
     */
    public function setGrade($Grade)
    {
        $this->Grade = $Grade;
    }

    /**
     * @return bool|TblPrepareAdditionalGradeType
     */
    public function getTblPrepareAdditionalGradeType()
    {

        if (null === $this->tblPrepareAdditionalGradeType) {
            return false;
        } else {
            return Prepare::useService()->getPrepareAdditionalGradeTypeById($this->tblPrepareAdditionalGradeType);
        }
    }

    /**
     * @param TblPrepareAdditionalGradeType|null $tblPrepareAdditionalGradeType
     */
    public function setTblPrepareAdditionalGradeType(TblPrepareAdditionalGradeType $tblPrepareAdditionalGradeType = null)
    {

        $this->tblPrepareAdditionalGradeType = (null === $tblPrepareAdditionalGradeType ? null : $tblPrepareAdditionalGradeType->getId());
    }

    /**
     * @return bool
     */
    public function isLocked()
    {

        return $this->IsLocked;
    }

    /**
     * @param bool $IsLocked
     */
    public function setLocked($IsLocked)
    {

        $this->IsLocked = (bool)$IsLocked;
    }
}