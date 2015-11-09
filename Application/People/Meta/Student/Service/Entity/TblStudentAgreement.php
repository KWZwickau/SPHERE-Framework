<?php
namespace SPHERE\Application\People\Meta\Student\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\People\Meta\Student\Student;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblStudentAgreement")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentAgreement extends Element
{

    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStudentAgreementCategory;
    /**
     * @Column(type="bigint")
     */
    protected $serviceTblStudentAgreementType;

    /**
     * @return bool|TblStudentAgreementCategory
     */
    public function getServiceTblStudentAgreementCategory()
    {

        if (null === $this->serviceTblStudentAgreementCategory) {
            return false;
        } else {
            return Student::useService()->getStudentAgreementCategoryById($this->serviceTblStudentAgreementCategory);
        }
    }

    /**
     * @param TblStudentAgreementCategory|null $tblStudentAgreementCategory
     */
    public function setServiceTblStudentAgreementCategory(
        TblStudentAgreementCategory $tblStudentAgreementCategory = null
    ) {

        $this->serviceTblStudentAgreementCategory = ( null === $tblStudentAgreementCategory ? null : $tblStudentAgreementCategory->getId() );
    }

    /**
     * @return bool|TblStudentAgreementType
     */
    public function getServiceTblStudentAgreementType()
    {

        if (null === $this->serviceTblStudentAgreementType) {
            return false;
        } else {
            return Student::useService()->getStudentAgreementTypeById($this->serviceTblStudentAgreementType);
        }
    }

    /**
     * @param TblStudentAgreementType|null $tblStudentAgreementType
     */
    public function setServiceTblStudentAgreementType(TblStudentAgreementType $tblStudentAgreementType = null)
    {

        $this->serviceTblStudentAgreementType = ( null === $tblStudentAgreementType ? null : $tblStudentAgreementType->getId() );
    }
}
