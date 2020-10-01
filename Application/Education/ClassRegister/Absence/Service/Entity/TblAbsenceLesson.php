<?php

namespace SPHERE\Application\Education\ClassRegister\Absence\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Education\ClassRegister\Absence\Absence;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblAbsenceLesson")
 * @Cache(usage="READ_ONLY")
 */
class TblAbsenceLesson extends Element
{
    const ATTR_TBL_ABSENCE = 'tblAbsence';
    const ATTR_LESSON = 'Lesson';

    /**
     * @Column(type="bigint")
     */
    protected $tblAbsence;

    /**
     * @Column(type="integer")
     */
    protected $Lesson;

    /**
     * @return bool|TblAbsence
     */
    public function getTblAbsence()
    {

        if (null === $this->tblAbsence) {
            return false;
        } else {
            return Absence::useService()->getAbsenceById($this->tblAbsence);
        }
    }

    /**
     * @param TblAbsence|null $tblAbsence
     */
    public function setTblAbsence(TblAbsence $tblAbsence = null)
    {

        $this->tblAbsence = (null === $tblAbsence ? null : $tblAbsence->getId());
    }

    /**
     * @return integer
     */
    public function getLesson()
    {
        return $this->Lesson;
    }

    /**
     * @param integer $Lesson
     */
    public function setLesson($Lesson)
    {
        $this->Lesson = $Lesson;
    }
}