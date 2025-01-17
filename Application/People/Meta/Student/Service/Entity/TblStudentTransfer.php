<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Corporation\Company\Company;
use SPHERE\Application\Corporation\Company\Service\Entity\TblCompany;
use SPHERE\Application\Education\School\Course\Course;
use SPHERE\Application\Education\School\Course\Service\Entity\TblCourse;
use SPHERE\Application\Education\School\Type\Service\Entity\TblType;
use SPHERE\Application\Education\School\Type\Type;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentTransfer")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentTransfer extends Element
{

    const ATTR_TBL_STUDENT = 'tblStudent';
    const ATTR_TBL_TRANSFER_TYPE = 'tblStudentTransferType';
    const ATTR_TBL_SCHOOL_ENROLLMENT_TYPE = 'tblStudentSchoolEnrollmentType';
    const ATTR_SERVICE_TBL_COMPANY = 'serviceTblCompany';

    /**
     * @Column(type="bigint")
     */
    protected $tblStudent;

    /**
     * @Column(type="bigint")
     */
    protected $tblStudentTransferType;

    /**
     * @Column(type="bigint")
     */
    protected $tblStudentSchoolEnrollmentType;

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCompany;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStateCompany;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblType;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblCourse;
    /**
     * @Column(type="datetime")
     */
    protected $TransferDate;
    /**
     * @Column(type="text")
     */
    protected $Remark;

    /**
     * @return bool|TblStudent
     */
    public function getTblStudent()
    {

        if (null === $this->tblStudent) {
            return false;
        } else {
            return Student::useService()->getStudentById($this->tblStudent);
        }
    }

    /**
     * @param null|TblStudent $tblStudent
     */
    public function setTblStudent(TblStudent $tblStudent = null)
    {

        $this->tblStudent = ( null === $tblStudent ? null : $tblStudent->getId() );
    }

    /**
     * @return bool|TblStudentTransferType
     */
    public function getTblStudentTransferType()
    {

        if (null === $this->tblStudentTransferType) {
            return false;
        } else {
            return Student::useService()->getStudentTransferTypeById($this->tblStudentTransferType);
        }
    }

    /**
     * @param null|TblStudentTransferType $tblStudentTransferType
     */
    public function setTblStudentTransferType(TblStudentTransferType $tblStudentTransferType = null)
    {

        $this->tblStudentTransferType = ( null === $tblStudentTransferType ? null : $tblStudentTransferType->getId() );
    }

    /**
     * @return string
     */
    public function getTransferDate()
    {

        if (null === $this->TransferDate) {
            return false;
        }
        /** @var \DateTime $TransferDate */
        $TransferDate = $this->TransferDate;
        if ($TransferDate instanceof \DateTime) {
            return $TransferDate->format('d.m.Y');
        } else {
            return (string)$TransferDate;
        }
    }

    /**
     * @param null|\DateTime $TransferDate
     */
    public function setTransferDate(\DateTime $TransferDate = null)
    {

        $this->TransferDate = $TransferDate;
    }

    /**
     * @return string
     */
    public function getRemark()
    {

        return $this->Remark;
    }

    /**
     * @param string $Remark
     */
    public function setRemark($Remark)
    {

        $this->Remark = $Remark;
    }

    /**
     * @return bool|TblCompany
     */
    public function getServiceTblCompany()
    {

        if (null === $this->serviceTblCompany) {
            return false;
        } else {
            return Company::useService()->getCompanyById($this->serviceTblCompany);
        }
    }

    /**
     * @param TblCompany|null $tblCompany
     */
    public function setServiceTblCompany(TblCompany $tblCompany = null)
    {

        $this->serviceTblCompany = ( null === $tblCompany ? null : $tblCompany->getId() );
    }

    /**
     * @return bool|TblCompany
     */
    public function getServiceTblStateCompany()
    {

        if (null === $this->serviceTblStateCompany) {
            return false;
        } else {
            return Company::useService()->getCompanyById($this->serviceTblStateCompany);
        }
    }

    /**
     * @param TblCompany|null $tblCompany
     */
    public function setServiceTblStateCompany(TblCompany $tblCompany = null)
    {

        $this->serviceTblStateCompany = ( null === $tblCompany ? null : $tblCompany->getId() );
    }

    /**
     * @return bool|TblType
     */
    public function getServiceTblType()
    {

        if (null === $this->serviceTblType) {
            return false;
        } else {
            return Type::useService()->getTypeById($this->serviceTblType);
        }
    }

    /**
     * @param TblType|null $tblType
     */
    public function setServiceTblType(TblType $tblType = null)
    {

        $this->serviceTblType = ( null === $tblType ? null : $tblType->getId() );
    }

    /**
     * @return bool|TblCourse
     */
    public function getServiceTblCourse()
    {

        if (null === $this->serviceTblCourse) {
            return false;
        } else {
            return Course::useService()->getCourseById($this->serviceTblCourse);
        }
    }

    /**
     * @param TblCourse|null $tblCourse
     */
    public function setServiceTblCourse(TblCourse $tblCourse = null)
    {

        $this->serviceTblCourse = ( null === $tblCourse ? null : $tblCourse->getId() );
    }

    /**
     * @return bool|TblStudentSchoolEnrollmentType
     */
    public function getTblStudentSchoolEnrollmentType()
    {

        if (null === $this->tblStudentSchoolEnrollmentType) {
            return false;
        } else {
            return Student::useService()->getStudentSchoolEnrollmentTypeById($this->tblStudentSchoolEnrollmentType);
        }
    }

    /**
     * @param null|TblStudentSchoolEnrollmentType $tblStudentSchoolEnrollmentType
     */
    public function setTblStudentSchoolEnrollmentType(TblStudentSchoolEnrollmentType $tblStudentSchoolEnrollmentType = null)
    {

        $this->tblStudentSchoolEnrollmentType = ( null === $tblStudentSchoolEnrollmentType ? null : $tblStudentSchoolEnrollmentType->getId() );
    }
}
