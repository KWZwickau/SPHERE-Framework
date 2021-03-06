<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Division\Service\Entity\TblLevel;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSubject extends Element
{

    const ATTR_TBL_STUDENT = 'tblStudent';
    const ATTR_TBL_STUDENT_SUBJECT_TYPE = 'tblStudentSubjectType';
    const ATTR_TBL_STUDENT_SUBJECT_RANKING = 'tblStudentSubjectRanking';
    const SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const SERVICE_TBL_LEVEL_FROM = 'serviceTblLevelFrom';
    const SERVICE_TBL_LEVEL_TILL = 'serviceTblLevelTill';
    
    /**
     * @Column(type="bigint")
     */
    protected $tblStudent;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentSubjectType;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentSubjectRanking;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblLevelFrom;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblLevelTill;
    
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
     * @return bool|TblStudentSubjectType
     */
    public function getTblStudentSubjectType()
    {

        if (null === $this->tblStudentSubjectType) {
            return false;
        } else {
            return Student::useService()->getStudentSubjectTypeById($this->tblStudentSubjectType);
        }
    }

    /**
     * @param null|TblStudentSubjectType $tblStudentSubjectType
     */
    public function setTblStudentSubjectType(TblStudentSubjectType $tblStudentSubjectType = null)
    {

        $this->tblStudentSubjectType = ( null === $tblStudentSubjectType ? null : $tblStudentSubjectType->getId() );
    }

    /**
     * @return bool|TblStudentSubjectRanking
     */
    public function getTblStudentSubjectRanking()
    {

        if (null === $this->tblStudentSubjectRanking) {
            return false;
        } else {
            return Student::useService()->getStudentSubjectRankingById($this->tblStudentSubjectRanking);
        }
    }

    /**
     * @param null|TblStudentSubjectRanking $tblStudentSubjectRanking
     */
    public function setTblStudentSubjectRanking(TblStudentSubjectRanking $tblStudentSubjectRanking = null)
    {

        $this->tblStudentSubjectRanking = ( null === $tblStudentSubjectRanking ? null : $tblStudentSubjectRanking->getId() );
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
     * @return bool|TblLevel
     */
    public function getServiceTblLevelFrom()
    {

        if (null === $this->serviceTblLevelFrom) {
            return false;
        } else {
            return Division::useService()->getLevelById($this->serviceTblLevelFrom);
        }
    }

    /**
     * @param TblLevel|null $tblLevel
     */
    public function setServiceTblLevelFrom(TblLevel $tblLevel = null)
    {

        $this->serviceTblLevelFrom = ( null === $tblLevel ? null : $tblLevel->getId() );
    }

    /**
     * @return bool|TblLevel
     */
    public function getServiceTblLevelTill()
    {

        if (null === $this->serviceTblLevelTill) {
            return false;
        } else {
            return Division::useService()->getLevelById($this->serviceTblLevelTill);
        }
    }

    /**
     * @param TblLevel|null $tblLevel
     */
    public function setServiceTblLevelTill(TblLevel $tblLevel = null)
    {

        $this->serviceTblLevelTill = ( null === $tblLevel ? null : $tblLevel->getId() );
    }
}
