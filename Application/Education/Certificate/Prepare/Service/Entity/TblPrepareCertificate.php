<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 12.07.2016
 * Time: 11:19
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generate\Service\Entity\TblGenerateCertificate;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\DivisionCourse\DivisionCourse;
use SPHERE\Application\Education\Lesson\DivisionCourse\Service\Entity\TblDivisionCourse;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\People\Person\Person;
use SPHERE\Application\People\Person\Service\Entity\TblPerson;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblPrepareCertificate")
 * @Cache(usage="READ_ONLY")
 */
class TblPrepareCertificate extends Element
{
    const ATTR_SERVICE_TBL_DIVISION = 'serviceTblDivision';
    const ATTR_SERVICE_TBL_GENERATE_CERTIFICATE = 'serviceTblGenerateCertificate';

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblGenerateCertificate;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblDivision;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblPersonSigner;
    /**
     * @Column(type="boolean")
     */
    protected bool $IsPrepared = false;

    /**
     * @return string
     */
    public function getDate(): string
    {
        return ($tblGenerateCertificate = $this->getServiceTblGenerateCertificate()) ? $tblGenerateCertificate->getDate() : '';
    }

    /**
     * @return DateTime|null
     */
    public function getDateTime(): ?DateTime
    {
        return ($tblGenerateCertificate = $this->getServiceTblGenerateCertificate()) ? $tblGenerateCertificate->getDateTime() : null;
    }

    /**
     * @return bool|TblDivisionCourse
     */
    public function getServiceTblDivision()
    {
        if (null === $this->serviceTblDivision) {
            return false;
        } else {
            return DivisionCourse::useService()->getDivisionCourseById($this->serviceTblDivision);
        }
    }

    /**
     * @param TblDivisionCourse|null $tblDivision
     */
    public function setServiceTblDivision(TblDivisionCourse $tblDivision = null)
    {
        $this->serviceTblDivision = (null === $tblDivision ? null : $tblDivision->getId());
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return ($tblGenerateCertificate = $this->getServiceTblGenerateCertificate()) ? $tblGenerateCertificate->getName() : '';
    }

    /**
     * @return bool|TblTask
     */
    public function getServiceTblAppointedDateTask()
    {
        return ($tblGenerateCertificate = $this->getServiceTblGenerateCertificate()) ? $tblGenerateCertificate->getServiceTblAppointedDateTask() : false;
    }

    /**
     * @return bool|TblTask
     */
    public function getServiceTblBehaviorTask()
    {
        return ($tblGenerateCertificate = $this->getServiceTblGenerateCertificate()) ? $tblGenerateCertificate->getServiceTblBehaviorTask() : false;
    }

    /**
     * @return bool|TblPerson
     */
    public function getServiceTblPersonSigner()
    {
        if (null === $this->serviceTblPersonSigner) {
            return false;
        } else {
            return Person::useService()->getPersonById($this->serviceTblPersonSigner);
        }
    }

    /**
     * @param TblPerson|null $tblPerson
     */
    public function setServiceTblPersonSigner(TblPerson $tblPerson = null)
    {
        $this->serviceTblPersonSigner = ( null === $tblPerson ? null : $tblPerson->getId() );
    }

    /**
     * @return boolean
     */
    public function isGradeInformation(): bool
    {
        return ($tblCertificateType = $this->getCertificateType()) && $tblCertificateType->getIdentifier() == 'GRADE_INFORMATION';
    }

    /**
     * @return bool|TblGenerateCertificate
     */
    public function getServiceTblGenerateCertificate()
    {
        if (null === $this->serviceTblGenerateCertificate) {
            return false;
        } else {
            return Generate::useService()->getGenerateCertificateById($this->serviceTblGenerateCertificate);
        }
    }

    /**
     * @param TblGenerateCertificate|null $tblGenerateCertificate
     */
    public function setServiceTblGenerateCertificate(TblGenerateCertificate $tblGenerateCertificate = null)
    {
        $this->serviceTblGenerateCertificate = (null === $tblGenerateCertificate ? null : $tblGenerateCertificate->getId());
    }

    /**
     * @return bool|TblCertificateType
     */
    public function getCertificateType()
    {
        if (($tblCertificateType = $this->getServiceTblGenerateCertificate())) {
            return $tblCertificateType->getServiceTblCertificateType();
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getIsPrepared() : bool
    {
        return $this->IsPrepared;
    }

    /**
     * @param bool $IsPrepared
     */
    public function setIsPrepared(bool $IsPrepared) : void
    {
        $this->IsPrepared = $IsPrepared;
    }

    /**
     * @return bool|TblYear
     */
    public function getYear()
    {
        return ($tblDivisionCourse = $this->getServiceTblDivision()) ? $tblDivisionCourse->getServiceTblYear() : false;
    }
}