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
    const ATTR_TBL_DISORDER = 'tblSpecialDisorderType';

    /**
     * @Column(type="bigint")
     */
    protected $tblSpecial;
    /**
     * @Column(type="bigint")
     */
    protected $tblSpecialDisorderType;

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
     * @return false|TblSpecialDisorderType
     */
    public function getTblSpecialDisorderType()
    {
        if (null === $this->tblSpecialDisorderType) {
            return false;
        } else {
            return Student::useService()->getSpecialDisorderTypeById($this->tblSpecialDisorderType);
        }
    }

    /**
     * @param TblSpecialDisorderType $tblSpecialDisorderType
     */
    public function setTblSpecialDisorderType(TblSpecialDisorderType $tblSpecialDisorderType)
    {

        $this->tblSpecialDisorderType = $tblSpecialDisorderType->getId();
    }

}
