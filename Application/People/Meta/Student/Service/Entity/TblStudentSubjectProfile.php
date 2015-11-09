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
 * @Table(name="tblStudentSubjectProfile")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSubjectProfile extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectOrientation;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectAdvanced;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectProfile;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectReligion;

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectOrientation()
    {

        if (null === $this->serviceTblSubjectOrientation) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectOrientation);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectOrientation(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectOrientation = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectAdvanced()
    {

        if (null === $this->serviceTblSubjectAdvanced) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectAdvanced);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectAdvanced(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectAdvanced = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectProfile()
    {

        if (null === $this->serviceTblSubjectProfile) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectProfile);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectProfile(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectProfile = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectReligion()
    {

        if (null === $this->serviceTblSubjectReligion) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectReligion);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectReligion(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectReligion = ( null === $tblSubject ? null : $tblSubject->getId() );
    }
}
