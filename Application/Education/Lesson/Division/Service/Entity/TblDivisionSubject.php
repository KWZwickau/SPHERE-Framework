<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\System\Database\Fitting\Element;

/**
 * e.g. 6 Alpha - Math
 *
 * @Entity
 * @Table(name="tblDivisionSubject")
 * @Cache(usage="READ_ONLY")
 */
class TblDivisionSubject extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $tblDivision;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @return bool|TblDivision
     */
    public function getTblDivision()
    {

        if (null === $this->tblDivision) {
            return false;
        } else {
            return Division::useService()->getDivisionById($this->tblDivision);
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
}
