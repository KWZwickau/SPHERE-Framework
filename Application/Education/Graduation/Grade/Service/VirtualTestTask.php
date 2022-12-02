<?php

namespace SPHERE\Application\Education\Graduation\Grade\Service;

use DateTime;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTask;
use SPHERE\Application\Education\Graduation\Grade\Service\Entity\TblTest;
use SPHERE\System\Database\Fitting\Element;

class VirtualTestTask extends Element
{
    private ?DateTime $Date;
    private ?TblTest $tblTest;
    private ?TblTask $tblTask;
    private bool $IsTask;

    /**
     * @param DateTime|null $date
     * @param TblTest|null $tblTest
     * @param TblTask|null $tblTask
     */
    public function __construct(?DateTime $date, ?TblTest $tblTest, ?TblTask $tblTask) {
        $this->Date = $date;
        $this->tblTest = $tblTest;
        $this->tblTask = $tblTask;
        $this->IsTask = $tblTask !== null;
    }

    /**
     * @return DateTime|null
     */
    public function getDate(): ?DateTime
    {
        return $this->Date;
    }

    /**
     * @return bool
     */
    public function getIsTask(): bool
    {
        return $this->IsTask;
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
}