<?php
namespace SPHERE\System\Extension\Repository\Roadmap;

/**
 * Class Status
 *
 * @package SPHERE\System\Extension\Repository\Roadmap
 */
class Status
{

    const STATE_PLAN = 0;
    const STATE_WORK = 1;
    const STATE_DONE = 2;

    private $Work = 0;
    private $Done = 0;
    private $Plan = 0;

    /**
     * Returns combined Status
     *
     * Status::STATE_PLAN
     * Status::STATE_WORK
     * Status::STATE_DONE
     *
     * @return int
     */
    public function getState()
    {

        if ($this->getWork()) {
            return self::STATE_WORK;
        } else {
            if ($this->getPlan()) {
                return self::STATE_PLAN;
            }
            return self::STATE_DONE;
        }
    }

    /**
     * @return int
     */
    public function getWork()
    {

        return $this->Work;
    }

    /**
     * @return int
     */
    public function getPlan()
    {

        return $this->Plan;
    }

    /**
     * @param int $Count
     */
    public function addWork($Count = 1)
    {

        $this->Work += $Count;
    }

    /**
     * @param int $Count
     */
    public function addPlan($Count = 1)
    {

        $this->Plan += $Count;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        $Done = $this->getDonePercent();
        $Work = 100 / $this->getCount() * $this->getWork();
        $Plan = 100 / $this->getCount() * $this->getPlan();

        return
            '<div class="progress" style="height: 4px; margin: 0;">
          <div class="progress-bar progress-bar-success" style="width: '.$Done.'%;">
            <span class="sr-only">'.$Done.'% Done</span>
          </div>
          <div class="progress-bar progress-bar-warning progress-bar-striped active" style="width: '.$Work.'%;">
            <span class="sr-only">'.$Work.'% Work</span>
          </div>
          <div class="progress-bar progress-bar-striped" style="width: '.$Plan.'%; background-color: #DDD;">
            <span class="sr-only">'.$Plan.'% Plan</span>
          </div>
        </div>';
    }

    /**
     * @return float
     */
    public function getDonePercent()
    {

        return 100 / $this->getCount() * $this->getDone();
    }

    /**
     * @return int
     */
    public function getCount()
    {

        return $this->getPlan() + $this->getWork() + $this->getDone();
    }

    /**
     * @return int
     */
    public function getDone()
    {

        return $this->Done;
    }

    /**
     * @param int $Count
     */
    public function addDone($Count = 1)
    {

        $this->Done += $Count;
    }
}
