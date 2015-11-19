<?php
namespace SPHERE\Application\Education\Lesson\Division\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Division\Division;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * e.g. 6 Alpha - Info (null) - Student, 6 Alpha - Info (Info I) - Student
 *
 * @Entity
 * @Table(name="tblSubjectStudent")
 * @Cache(usage="READ_ONLY")
 */
class TblSubjectStudent extends Element
{

    const ATTR_TBL_DIVISION_SUBJECT = 'tblDivisionSubject';
    const ATTR_TBL_SUBJECT_GROUP = 'tblSubjectGroup';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';

    /**
     * @Column(type="bigint")
     */
    protected $tblDivisionSubject;
    /**
     * @Column(type="bigint")
     */
    protected $tblSubjectGroup;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @return bool|TblDivisionSubject
     */
    public function getTblDivisionSubject()
    {

        if (null === $this->tblDivisionSubject) {
            return false;
        } else {
            return Division::useService()->getDivisionSubjectById($this->tblDivisionSubject);
        }
    }

    /**
     * @param null|TblDivisionSubject $tblDivisionSubject
     */
    public function setTblDivisionSubject(TblDivisionSubject $tblDivisionSubject = null)
    {

        $this->tblDivisionSubject = ( null === $tblDivisionSubject ? null : $tblDivisionSubject->getId() );
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
     * @return bool|TblPerson
     */
    public function getServiceTblPerson()
    {

        if (null === $this->serviceTblPerson) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPerson);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPerson(TblPerson $tblPerson = null)
    {

        $this->serviceTblPerson = ( null === $tblPerson ? null : $tblPerson->getId() );
    }
}
