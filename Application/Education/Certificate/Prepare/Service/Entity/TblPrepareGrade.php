<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 16:03
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblGradeType;
use SPHERE\Application\Education\Lesson\Subject\Service\Entity\TblSubject;
use SPHERE\Application\Education\Lesson\Subject\Subject;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblPrepareGrade")
 * @Cache(usage="READ_ONLY")
 */
class TblPrepareGrade extends Element
{

    const ATTR_TBL_PREPARE_CERTIFICATE = 'tblPrepareCertificate';
    const ATTR_SERVICE_TBL_PERSON = 'serviceTblPerson';
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_SUBJECT = 'serviceTblSubject';
    const ATTR_SERVICE_TBL_GRADE_TYPE = 'serviceTblGradeType';

    /**
     * @Column(type="bigint")
     */
    protected $tblPrepareCertificate;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPerson;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblSubject;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGradeType;

    /**
     * @Column(type="string")
     */
    protected $Grade;

    /**
     * @return bool|TblPrepareCertificate
     */
    public function getTblPrepareCertificate()
    {

        if (null === $this->tblPrepareCertificate) {
            return false;
        } else {
            return Prepare::useService()->getPrepareById($this->tblPrepareCertificate);
        }
    }

    /**
     * @param TblPrepareCertificate|null $tblPrepareCertificate
     */
    public function setTblPrepareCertificate(TblPrepareCertificate $tblPrepareCertificate = null)
    {

        $this->tblPrepareCertificate = (null === $tblPrepareCertificate ? null : $tblPrepareCertificate->getId());
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
     * @return bool|TblGradeType
     */
    public function getServiceTblGradeType()
    {
        if (null === $this->serviceTblGradeType) {
            return false;
        } else {
            return Grade::useService()->getGradeTypeById($this->serviceTblGradeType);
        }
    }

    /**
     * @param TblGradeType|null $serviceTblGradeType
     */
    public function setServiceTblGradeType(?TblGradeType $serviceTblGradeType)
    {
        $this->serviceTblGradeType = ( null === $serviceTblGradeType ? null : $serviceTblGradeType->getId() );
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
}