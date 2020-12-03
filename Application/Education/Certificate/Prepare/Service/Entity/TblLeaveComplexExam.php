<?php

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
 * @Table(name="tblLeaveComplexExam")
 * @Cache(usage="READ_ONLY")
 */
class TblLeaveComplexExam extends Element
{
    const ATTR_TBL_LEAVE_STUDENT = 'tblLeaveStudent';
    const ATTR_RANKING = 'Ranking';
    const ATTR_IDENTIFIER = 'Identifier';

    const IDENTIFIER_WRITTEN = 'WRITTEN';
    const IDENTIFIER_PRAXIS = 'PRAXIS';

    /**
     * @Column(type="bigint")
     */
    protected $tblLeaveStudent;

    /**
     * @Column(type="integer")
     */
    protected $Ranking;

    /**
     * @Column(type="string")
     */
    protected $Identifier;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblFirstSubject;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSecondSubject;

    /**
     * @Column(type="string")
     */
    protected $Grade;

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
    public function getServiceTblFirstSubject()
    {
        if (null === $this->serviceTblFirstSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblFirstSubject);
        }
    }

    /**
     * @param TblSubject|null $tblFirstSubject
     */
    public function setServiceTblFirstSubject(TblSubject $tblFirstSubject = null)
    {
        $this->serviceTblFirstSubject = ( null === $tblFirstSubject ? null : $tblFirstSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSecondSubject()
    {
        if (null === $this->serviceTblSecondSubject) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSecondSubject);
        }
    }

    /**
     * @param TblSubject|null $tblSecondSubject
     */
    public function setServiceTblSecondSubject(TblSubject $tblSecondSubject = null)
    {
        $this->serviceTblSecondSubject = ( null === $tblSecondSubject ? null : $tblSecondSubject->getId() );
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
     * @return integer
     */
    public function getRanking()
    {
        return $this->Ranking;
    }

    /**
     * @param integer $Ranking
     */
    public function setRanking($Ranking)
    {
        $this->Ranking = $Ranking;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->Identifier;
    }

    /**
     * @param string $Identifier
     */
    public function setIdentifier($Identifier)
    {
        $this->Identifier = $Identifier;
    }
}