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
 * @Table(name="tblStudentAgreementType")
 * @Cache(usage="READ_ONLY")
 */
class TblStudentAgreementType extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_TBL_STUDENT_AGREEMENT_CATEGORY = 'tblStudentAgreementCategory';

    /**
     * @Column(type="bigint")
     */
    protected $tblStudentAgreementCategory;
    /**
     * @Column(type="text")
     */
    protected $Name;
    /**
     * @Column(type="text")
     */
    protected $Description;
    /**
     * @Column(type="boolean")
     */
    protected $isUnlocked;

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
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return bool|TblStudentAgreementCategory
     */
    public function getTblStudentAgreementCategory()
    {

        if (null === $this->tblStudentAgreementCategory) {
            return false;
        } else {
            return Student::useService()->getStudentAgreementCategoryById($this->tblStudentAgreementCategory);
        }
    }

    /**
     * @param TblStudentAgreementCategory|null $tblStudentAgreementCategory
     */
    public function setTblStudentAgreementCategory(
        TblStudentAgreementCategory $tblStudentAgreementCategory = null
    ) {

        $this->tblStudentAgreementCategory = ( null === $tblStudentAgreementCategory ? null : $tblStudentAgreementCategory->getId() );
    }

    /**
     * @return bool
     */
    public function getIsUnlocked()
    {
        return $this->isUnlocked;
    }

    /**
     * @param bool $isUnlocked
     */
    public function setIsUnlocked($isUnlocked = false): void
    {
        $this->isUnlocked = $isUnlocked;
    }
}
