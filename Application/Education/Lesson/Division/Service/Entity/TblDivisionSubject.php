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

    const ATTR_TBL_DIVISION = 'tblDivision';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_TBL_SUBJECT_GROUP = 'tblSubjectGroup';


    /**
     * @Column(type="bigint")
     */
    protected $tblDivision;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;
    /**
     * @Column(type="bigint")
     */
    protected $tblSubjectGroup;

    /**
     * @Column(type="boolean")
     */
    protected $HasGrading;

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

    /**
     * @return bool|TblSubjectGroup
     */
    public function getTblSubjectGroup()
    {

        if (null === $this->tblSubjectGroup) {
            return false;
        } else {
            return Division::useService()->getSubjectGroupById($this->tblSubjectGroup);
        }
    }

    /**
     * @param null|TblSubjectGroup $tblSubjectGroup
     */
    public function setTblSubjectGroup(TblSubjectGroup $tblSubjectGroup = null)
    {

        $this->tblSubjectGroup = ( null === $tblSubjectGroup ? null : $tblSubjectGroup->getId() );
    }

    /**
     * @return mixed
     */
    public function getHasGrading()
    {
        return (boolean) $this->HasGrading;
    }

    /**
     * @param mixed $HasGrading
     */
    public function setHasGrading($HasGrading)
    {
        $this->HasGrading = (boolean) $HasGrading;
    }
}
