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
 * @Table(name="tblSupportFocus")
 * @Cache(usage="READ_ONLY")
 */
class TblSupportFocus extends Element
{

    const ATTR_TBL_SUPPORT = 'tblSupport';
    const ATTR_TBL_SUPPORT_FOCUS_TYPE = 'tblSupportFocusType';
    const ATTR_IS_PRIMARY = 'IsPrimary';

    /**
     * @Column(type="bigint")
     */
    protected $tblSupport;
    /**
     * @Column(type="bigint")
     */
    protected $tblSupportFocusType;
    /**
     * @Column(type="boolean")
     */
    protected $IsPrimary;



    /**
     * @return TblSupport
     */
    public function getTblSupport()
    {

        return Student::useService()->getSupportById($this->tblSupport);
    }

    /**
     * @param TblSupport $tblSupport
     */
    public function setTblSupport(TblSupport $tblSupport)
    {

        $this->tblSupport = $tblSupport->getId();
    }

    /**
     * @return TblSupportFocusType
     */
    public function getTblSupportFocusType()
    {

        return Student::useService()->getSupportFocusTypeById($this->tblSupportFocusType);
    }

    /**
     * @param TblSupportFocusType $tblSupportFocusType
     */
    public function setTblSupportFocusType(TblSupportFocusType $tblSupportFocusType)
    {

        $this->tblSupportFocusType = $tblSupportFocusType->getId();
    }

    /**
     * @return boolean
     */
    public function getIsPrimary()
    {
        return $this->IsPrimary;
    }

    /**
     * @param boolean $IsPrimary
     */
    public function setIsPrimary($IsPrimary = false)
    {
        $this->IsPrimary = $IsPrimary;
    }
}
