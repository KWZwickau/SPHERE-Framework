<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 25.11.2016
 * Time: 12:02
 */

namespace SPHERE\Application\Education\Certificate\Generate\Service\Entity;

use DateTime;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Generate\Generate;
use SPHERE\Application\Education\Certificate\Generator\Generator;
use SPHERE\Application\Education\Certificate\Generator\Service\Entity\TblCertificateType;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\Application\Education\Certificate\Prepare\Service\Entity\TblPrepareCertificate;
use SPHERE\Application\Education\Graduation\Grade\Grade;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Common\Common;
use SPHERE\Application\People\Meta\Common\Service\Entity\TblCommonGender;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblGenerateCertificate")
 * @Cache(usage="READ_ONLY")
 */
class TblGenerateCertificate extends Element
{
    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';

    /**
     * @Column(type="datetime")
     */
    protected $Date;

    /**
     * @Column(type="datetime")
     */
    protected $AppointedDateForAbsence;

    /**
     * @Column(type="string")
     */
    protected $Name;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCertificateType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblAppointedDateTask;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblBehaviorTask;

    /**
     * @Column(type="string")
     */
    protected $HeadmasterName;

    /**
     * @Column(type="boolean")
     */
    protected $IsDivisionTeacherAvailable;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblYear;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCommonGenderHeadmaster;

    /**
     * @return string
     */
    public function getDate()
    {
        if (null === $this->Date) {
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->Date;
        if ($Date instanceof DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @return ?DateTime
     */
    public function getDateTime(): ?DateTime
    {
        return $this->Date;
    }

    /**
     * @param null|DateTime $Date
     */
    public function setDate(DateTime $Date = null)
    {
        $this->Date = $Date;
    }

    /**
     * @return string
     */
    public function getAppointedDateForAbsence()
    {
        if (null === $this->AppointedDateForAbsence) {
            return false;
        }
        /** @var DateTime $Date */
        $Date = $this->AppointedDateForAbsence;
        if ($Date instanceof DateTime) {
            return $Date->format('d.m.Y');
        } else {
            return (string)$Date;
        }
    }

    /**
     * @param null|DateTime $AppointedDateForAbsence
     */
    public function setAppointedDateForAbsence(DateTime $AppointedDateForAbsence = null)
    {
        $this->AppointedDateForAbsence = $AppointedDateForAbsence;
    }

    /**
     * @return bool|TblYear
     */
    public function getServiceTblYear()
    {
        if (null === $this->serviceTblYear) {
            return false;
        } else {
            return Term::useService()->getYearById($this->serviceTblYear);
        }
    }

    /**
     * @param TblYear|null $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear = null)
    {
        $this->serviceTblYear = (null === $tblYear ? null : $tblYear->getId());
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {
        $this->Name = $Name;
    }

    /**
     * @return bool|TblTask
     */
    public function getServiceTblAppointedDateTask()
    {
        if (null === $this->serviceTblAppointedDateTask) {
            return false;
        } else {
            return Grade::useService()->getTaskById($this->serviceTblAppointedDateTask);
        }
    }

    /**
     * @param TblTask|null $tblTask
     */
    public function setServiceTblAppointedDateTask(TblTask $tblTask = null)
    {
        $this->serviceTblAppointedDateTask = (null === $tblTask ? null : $tblTask->getId());
    }

    /**
     * @return bool|TblTask
     */
    public function getServiceTblBehaviorTask()
    {
        if (null === $this->serviceTblBehaviorTask) {
            return false;
        } else {
            return Grade::useService()->getTaskById($this->serviceTblBehaviorTask);
        }
    }

    /**
     * @param TblTask|null $tblTask
     */
    public function setServiceTblBehaviorTask(TblTask $tblTask = null)
    {
        $this->serviceTblBehaviorTask = (null === $tblTask ? null : $tblTask->getId());
    }

    /**
     * @return mixed
     */
    public function getHeadmasterName()
    {
        return $this->HeadmasterName;
    }

    /**
     * @param mixed $HeadmasterName
     */
    public function setHeadmasterName($HeadmasterName)
    {
        $this->HeadmasterName = $HeadmasterName;
    }

    /**
     * @return boolean
     */
    public function isDivisionTeacherAvailable()
    {
        return $this->IsDivisionTeacherAvailable;
    }

    /**
     * @param boolean $IsDivisionTeacherAvailable
     */
    public function setIsDivisionTeacherAvailable($IsDivisionTeacherAvailable)
    {
        $this->IsDivisionTeacherAvailable = (boolean) $IsDivisionTeacherAvailable;
    }

    /**
     * @return bool|TblCertificateType
     */
    public function getServiceTblCertificateType()
    {
        if (null === $this->serviceTblCertificateType) {
            return false;
        } else {
            return Generator::useService()->getCertificateTypeById($this->serviceTblCertificateType);
        }
    }

    /**
     * @param TblCertificateType|null $tblCertificateType
     */
    public function setServiceTblCertificateType(TblCertificateType $tblCertificateType = null)
    {
        $this->serviceTblCertificateType = (null === $tblCertificateType ? null : $tblCertificateType->getId());
    }

    /**
     * @return bool|TblCommonGender
     */
    public function getServiceTblCommonGenderHeadmaster()
    {
        if (null === $this->serviceTblCommonGenderHeadmaster) {
            return false;
        } else {
            return Common::useService()->getCommonGenderById($this->serviceTblCommonGenderHeadmaster);
        }
    }

    /**
     * @param TblCommonGender|null $tblGender
     */
    public function setServiceTblCommonGenderHeadmaster(TblCommonGender $tblGender = null)
    {
        $this->serviceTblCommonGenderHeadmaster = (null === $tblGender ? null : $tblGender->getId());
    }

    /**
     * @param bool $isString
     *
     * @return false|Type[]|string
     */
    public function getSchoolTypes(bool $isString = false)
    {
        return Generate::useService()->getSchoolTypeListFromGenerateCertificate($this, $isString);
    }

    /**
     * @return false|TblPrepareCertificate[]
     */
    public function getPrepareList()
    {
        return Prepare::useService()->getPrepareAllByGenerateCertificate($this);
    }
}