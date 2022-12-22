<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use DateTime;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\Application\Education\Lesson\Term\Service\Entity\TblPeriod;
use SPHERE\System\Database\Fitting\Element;

class VirtualTestTask extends Element
{
    const TYPE_TEST = 'Test';
    const TYPE_TASK = 'Task';
    const TYPE_PERIOD = 'Period';

    private ?DateTime $Date;
    private ?TblTest $tblTest;
    private ?TblTask $tblTask;
    private ?TblPeriod $tblPeriod;
    private ?int $countPeriod = 0;

    /**
     * @param DateTime|null $date
     * @param TblTest|null $tblTest
     * @param TblTask|null $tblTask
     * @param TblPeriod|null $tblPeriod
     *
     * @param int $countPeriod
     */
    public function __construct(?DateTime $date, ?TblTest $tblTest, ?TblTask $tblTask = null, ?TblPeriod $tblPeriod = null, int $countPeriod = 0) {
        $this->Date = $date;
        $this->tblTest = $tblTest;
        $this->tblTask = $tblTask;
        $this->tblPeriod = $tblPeriod;
        $this->countPeriod = $countPeriod;
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->Date;
    }

    /**
     * @return TblTest|null
     */
    public function getTblTest(): ?TblTest
    {
        return $this->tblTest;
    }

    /**
     * @return TblTask|null
     */
    public function getTblTask(): ?TblTask
    {
        return $this->tblTask;
    }

    /**
     * @return TblPeriod|null
     */
    public function getTblPeriod(): ?TblPeriod
    {
        return $this->tblPeriod;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        if ($this->getTblTest()) {
            return self::TYPE_TEST;
        }
        if ($this->getTblTask()) {
            return self::TYPE_TASK;
        }
        if ($this->getTblPeriod()) {
            return self::TYPE_PERIOD;
        }

        return '';
    }

    /**
     * @return int|null
     */
    public function getCountPeriod(): ?int
    {
        return $this->countPeriod;
    }
}