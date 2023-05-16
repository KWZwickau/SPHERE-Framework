<?php

namespace SPHERE\Application\Transfer\Education\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblYear;
use SPHERE\Application\Education\Lesson\Term\Term;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Account;
use SPHERE\Application\Platform\Gatekeeper\Authorization\Account\Service\Entity\TblAccount;
use SPHERE\Application\Transfer\Education\Education;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblImport")
 * @Cache(usage="READ_ONLY")
 */
class TblImport extends Element
{
    const EXTERN_SOFTWARE_NAME_INDIWARE = 'INDIWARE';
    const EXTERN_SOFTWARE_NAME_UNTIS = 'UNTIS';

    const TYPE_IDENTIFIER_LECTURESHIP = 'LECTURESHIP';
    const TYPE_IDENTIFIER_STUDENT_COURSE = 'STUDENT_COURSE';

    const ATTR_SERVICE_TBL_YEAR = 'serviceTblYear';
    const ATTR_SERVICE_TBL_ACCOUNT = 'serviceTblAccount';
    const ATTR_EXTERN_SOFTWARE_NAME = 'ExternSoftwareName';
    const ATTR_TYPE_IDENTIFIER = 'TypeIdentifier';

    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblYear;
    /**
     * @Column(type="bigint")
     */
    protected int $serviceTblAccount;
    /**
     * @Column(type="string")
     */
    protected string $ExternSoftwareName;
    /**
     * @Column(type="string")
     */
    protected string $TypeIdentifier;
    /**
     * @Column(type="string")
     */
    protected string $FileName;

    public function __construct(TblYear $tblYear, TblAccount $tblAccount, string $externSoftwareName, string $typeIdentifier, string $fileName)
    {
        $this->setServiceTblYear($tblYear);
        $this->setServiceTblAccount($tblAccount);
        $this->setExternSoftwareName($externSoftwareName);
        $this->setTypeIdentifier($typeIdentifier);
        $this->setFileName($fileName);
    }

    /**
     * @return false|TblYear
     */
    public function getServiceTblYear()
    {
        return Term::useService()->getYearById($this->serviceTblYear);
    }

    /**
     * @param TblYear $tblYear
     */
    public function setServiceTblYear(TblYear $tblYear)
    {
        $this->serviceTblYear = $tblYear->getId();
    }

    /**
     * @return bool|TblAccount
     */
    public function getServiceTblAccount()
    {
        return Account::useService()->getAccountById($this->serviceTblAccount);
    }

    /**
     * @param TblAccount|null $tblAccount
     */
    public function setServiceTblAccount(TblAccount $tblAccount)
    {
        $this->serviceTblAccount = $tblAccount->getId();
    }

    /**
     * @return string
     */
    public function getExternSoftwareName(): string
    {
        return $this->ExternSoftwareName;
    }

    /**
     * @param string $ExternSoftwareName
     */
    public function setExternSoftwareName(string $ExternSoftwareName): void
    {
        $this->ExternSoftwareName = $ExternSoftwareName;
    }

    /**
     * @return string
     */
    public function getTypeIdentifier(): string
    {
        return $this->TypeIdentifier;
    }

    /**
     * @param string $TypeIdentifier
     */
    public function setTypeIdentifier(string $TypeIdentifier): void
    {
        $this->TypeIdentifier = $TypeIdentifier;
    }

    /**
     * @return string
     */
    public function getFileName(): string
    {
        return $this->FileName;
    }

    /**
     * @param string $FileName
     */
    public function setFileName(string $FileName): void
    {
        $this->FileName = $FileName;
    }

    /**
     * @return string
     */
    public function getShowRoute(): string
    {
        if ($this->getTypeIdentifier() == TblImport::TYPE_IDENTIFIER_LECTURESHIP) {
            if ($this->getExternSoftwareName() == TblImport::EXTERN_SOFTWARE_NAME_INDIWARE) {
                return '/Transfer/Indiware/Import/Lectureship/Show';
            } else {
                return '/Transfer/Untis/Import/Lectureship/Show';
            }
        } elseif ($this->getTypeIdentifier() == TblImport::TYPE_IDENTIFIER_STUDENT_COURSE) {
            if ($this->getExternSoftwareName() == TblImport::EXTERN_SOFTWARE_NAME_INDIWARE) {
                return '/Transfer/Indiware/Import/StudentCourse/Show';
            } else {
                return '/Transfer/Untis/Import/StudentCourse/Show';
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public function getBackRoute(): string
    {
        if ($this->getExternSoftwareName() == TblImport::EXTERN_SOFTWARE_NAME_INDIWARE) {
            return '/Transfer/Indiware/Import';
        } else {
            return '/Transfer/Untis/Import';
        }
    }

    /**
     * @return false|TblImportLectureship[]
     */
    public function getImportLectureships()
    {
        return Education::useService()->getImportLectureshipListByImport($this);
    }

    /**
     * @return false|TblImportStudent[]
     */
    public function getImportStudents()
    {
        return Education::useService()->getImportStudentListByImport($this);
    }

    /**
     * @return false|TblImportStudentCourse[]
     */
    public function getImportStudentCourses()
    {
        return Education::useService()->getImportStudentCourseListByImport($this);
    }
}