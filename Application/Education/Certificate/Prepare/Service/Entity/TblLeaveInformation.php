<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 21.02.2018
 * Time: 08:16
 */

namespace SPHERE\Application\Education\Certificate\Prepare\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\Certificate\Prepare\Prepare;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblLeaveInformation")
 * @Cache(usage="READ_ONLY")
 */
class TblLeaveInformation extends Element
{
    const ATTR_TBL_LEAVE_STUDENT = 'tblLeaveStudent';
    const ATTR_FIELD = 'Field';

    /**
     * @Column(type="bigint")
     */
    protected $tblLeaveStudent;

    /**
     * @Column(type="text")
     */
    protected $Value;

    /**
     * @Column(type="string")
     */
    protected $Field;

    /**
     * @return false|TblLeaveStudent
     */
    public function getTblLeaveStudent()
    {

        if (null === $this->tblLeaveStudent) {
            return false;
        } else {
            return Prepare::useService()->getLeaveStudentById($this->tblLeaveStudent);
        }
    }

    /**
     * @param TblLeaveStudent|null $tblLeaveStudent
     */
    public function setTblLeaveStudent(TblLeaveStudent $tblLeaveStudent = null)
    {

        $this->tblLeaveStudent = (null === $tblLeaveStudent ? null : $tblLeaveStudent->getId());
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }

    /**
     * @return string
     */
    public function getField()
    {
        return $this->Field;
    }

    /**
     * @param string $Field
     */
    public function setField($Field)
    {
        $this->Field = $Field;
    }
}