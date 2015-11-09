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
 * @Table(name="tblStudentSubjectTrack")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentSubjectTrack extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectIntensive1;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectIntensive2;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectBasic1;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectBasic2;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectBasic3;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectBasic4;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectBasic5;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectBasic6;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectBasic7;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectBasic8;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubjectBasic9;

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectIntensive1()
    {

        if (null === $this->serviceTblSubjectIntensive1) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectIntensive1);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectIntensive1(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectIntensive1 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubject2()
    {

        if (null === $this->serviceTblSubjectIntensive2) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectIntensive2);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectIntensive2(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectIntensive2 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectBasic1()
    {

        if (null === $this->serviceTblSubjectBasic1) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectBasic1);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectBasic1(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectBasic1 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectBasic2()
    {

        if (null === $this->serviceTblSubjectBasic2) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectBasic2);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectBasic2(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectBasic2 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectBasic3()
    {

        if (null === $this->serviceTblSubjectBasic3) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectBasic3);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectBasic3(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectBasic3 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectBasic4()
    {

        if (null === $this->serviceTblSubjectBasic4) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectBasic4);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectBasic4(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectBasic4 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectBasic5()
    {

        if (null === $this->serviceTblSubjectBasic5) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectBasic5);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectBasic5(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectBasic5 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectBasic6()
    {

        if (null === $this->serviceTblSubjectBasic6) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectBasic6);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectBasic6(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectBasic6 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectBasic7()
    {

        if (null === $this->serviceTblSubjectBasic7) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectBasic7);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectBasic7(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectBasic7 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectBasic8()
    {

        if (null === $this->serviceTblSubjectBasic8) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectBasic8);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectBasic8(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectBasic8 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

    /**
     * @return bool|TblSubject
     */
    public function getServiceTblSubjectBasic9()
    {

        if (null === $this->serviceTblSubjectBasic9) {
            return false;
        } else {
            return Subject::useService()->getSubjectById($this->serviceTblSubjectBasic9);
        }
    }

    /**
     * @param TblSubject|null $tblSubject
     */
    public function setServiceTblSubjectBasic9(TblSubject $tblSubject = null)
    {

        $this->serviceTblSubjectBasic9 = ( null === $tblSubject ? null : $tblSubject->getId() );
    }

}
