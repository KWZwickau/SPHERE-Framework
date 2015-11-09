<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentSubjectTeam")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSubjectTeam extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject1;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject2;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject3;

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject1()
    {

        if (null === $this->serviceTblSubject1) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject1);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject1(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject1 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject2()
    {

        if (null === $this->serviceTblSubject2) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject2);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject2(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject2 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject3()
    {

        if (null === $this->serviceTblSubject3) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubject3);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubject3(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubject3 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }
}
