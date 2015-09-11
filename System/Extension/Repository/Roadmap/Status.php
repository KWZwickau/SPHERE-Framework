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
    public function getResult()
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

    public function setWork()
    {

        $this->Work++;
    }

    /**
     * @return int
     */
    public function getPlan()
    {

        return $this->Plan;
    }

    public function setPlan()
    {

        $this->Plan++;
    }

    /**
     * @return string
     */
    function __toString()
    {

        $All = $this->getDone() + $this->getWork() + $this->getPlan();
        $Done = 100 / $All * $this->getDone();
        $Work = 100 / $All * $this->getWork();
        $Plan = 100 / $All * $this->getPlan();

        return
            '<div class="progress" style="height: 4px; margin: 3px 0;">
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
     * @return int
     */
    public function getDone()
    {

        return $this->Done;
    }

    public function setDone()
    {

        $this->Done++;
    }


}
