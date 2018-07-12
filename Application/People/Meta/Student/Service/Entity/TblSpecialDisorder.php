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
 * @Table(name="tblSpecialDisorder")
 * @Cache(usage="READ_ONLY")
 */
class TblSpecialDisorder extends Element
{

    const ATTR_TBL_SPECIAL = 'tblSpecial';
    const ATTR_TBL_DISORDER = 'tblStudentDisorderType';

    /**
     * @Column(type="bigint")
     */
    protected $tblSpecial;
    /**
     * @Column(type="bigint")
     */
    protected $tblStudentDisorderType;

    /**
     * @return false|TblSpecial
     */
    public function getTblSpecial()
    {
        if (null === $this->tblSpecial) {
            return false;
        } else {
            return Student::useService()->getSpecialById($this->tblSpecial);
        }
    }

    /**
     * @param TblSpecial $tblSpecial
     */
    public function setTblSpecial(TblSpecial $tblSpecial)
    {

        $this->tblSpecial = $tblSpecial->getId();
    }

    /**
     * @return false|TblStudentDisorderType
     */
    public function getTblStudentDisorderType()
    {
        if (null === $this->tblStudentDisorderType) {
            return false;
        } else {
            return Student::useService()->getStudentDisorderTypeById($this->tblStudentDisorderType);
        }
    }

    /**
     * @param TblStudentDisorderType $tblStudentDisorderType
     */
    public function setTblStudentDisorderType(TblStudentDisorderType $tblStudentDisorderType)
    {

        $this->tblStudentDisorderType = $tblStudentDisorderType->getId();
    }

}
