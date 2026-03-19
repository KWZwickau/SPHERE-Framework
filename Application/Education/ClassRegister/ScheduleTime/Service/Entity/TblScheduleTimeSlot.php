<?php

namespace SPHERE\Application\Education\ClassRegister\ScheduleTime\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblClassRegisterScheduleTimeSlot")
 * @Cache(usage="READ_ONLY")
 */
class TblScheduleTimeSlot extends Element
{
    const ATTR_TABLE_SCHEDULE_TIME = 'tblClassRegisterScheduleTime';
    const ATTR_LESSON = 'Lesson';

    /**
     * @Column(type="bigint")
     */
    protected int $tblClassRegisterScheduleTime;
    /**
     * @Column(type="integer")
     */
    protected int $Lesson;
    /**
     * @Column(type="string")
     */
    protected string $StartTime;
    /**
     * @Column(type="string")
     */
    protected string $EndTime;

    /**
     * @param TblScheduleTime $tblScheduleTime
     *
     * @return $this
     */
    public function setTblScheduleTime(TblScheduleTime $tblScheduleTime): TblScheduleTimeSlot
    {
        $this->tblClassRegisterScheduleTime = $tblScheduleTime->getId();

        return $this;
    }

    /**
     * @param int $Lesson
     *
     * @return $this
     */
    public function setLesson(int $Lesson): TblScheduleTimeSlot
    {
        $this->Lesson = $Lesson;

        return $this;
    }

    /**
     * @return int
     */
    public function getLesson(): int
    {
        return $this->Lesson;
    }

    /**
     * @param string $StartTime
     *
     * @return $this
     */
    public function setStartTime(string $StartTime): TblScheduleTimeSlot
    {
        $this->StartTime = $StartTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getStartTime(): string
    {
        return $this->StartTime;
    }

    /**
     * @param string $EndTime
     *
     * @return $this
     */
    public function setEndTime(string $EndTime): TblScheduleTimeSlot
    {
        $this->EndTime = $EndTime;

        return $this;
    }

    /**
     * @return string
     */
    public function getEndTime(): string
    {
        return $this->EndTime;
    }
}