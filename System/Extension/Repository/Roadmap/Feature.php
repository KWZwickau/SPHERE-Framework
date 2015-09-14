<?php
namespace SPHERE\System\Extension\Repository\Roadmap;

use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;

/**
 * Class Feature
 *
 * @package SPHERE\System\Extension\Repository\Roadmap
 */
class Feature
{

    /** @var Task[] $Task */
    private $Task = array();

    /** @var string $Name */
    private $Name = '';
    /** @var string $Description */
    private $Description = '';
    /** @var bool|null $isDone */
    private $isDone = null;
    /** @var Status $Status */
    private $Status = null;

    /**
     * @param string    $Name
     * @param string    $Description
     * @param bool|null $isDone
     */
    public function __construct($Name = '', $Description = '', $isDone = null)
    {

        $this->Name = $Name;
        $this->Description = $Description;
        $this->isDone = $isDone;
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool   $isDone Stable & Public
     *
     * @return Task
     */
    public function createTask($Name, $Description = '', $isDone = null)
    {

        $Task = new Task($Name, $Description, $isDone);
        array_push($this->Task, $Task);
        return $Task;
    }

    /**
     * @return string
     */
    function __toString()
    {

        switch ($this->Status->getState()) {
            case Status::STATE_PLAN:
                $this->isDone = null;
                break;
            case Status::STATE_WORK:
                $this->isDone = false;
                break;
            case Status::STATE_DONE:
                $this->isDone = true;
                break;
        }

        return (string)new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('', 1),
                    new LayoutColumn(array(
                        new Header(( $this->isDone === true
                            ? new Success(new TileSmall().' Feature: '.$this->Name)
                            : ( $this->isDone === false
                                ? new Danger(new TileSmall().' Feature: '.$this->Name)
                                : new Muted(new TileSmall().' Feature: '.$this->Name)
                            )
                        ), $this->Description)
                    ), 6),
                    new LayoutColumn(array(
                        new Small($this->isDone === true
                            ? new Success(new Ok().' Fertig')
                            : ( $this->isDone === false
                                ? new Danger(
                                    new CogWheels().' In Entwicklung '
                                    .number_format($this->Status->getDonePercent(), 1, ',', '').'%'
                                )
                                : new Muted(
                                    new Disable().' In Planung '
                                    .number_format($this->Status->getDonePercent(), 1, ',', '').'%'
                                )
                            )
                        )
                        .( $this->isDone === true
                            ? $this->Status
                            : $this->Status
                        )
                    ), 5)
                )),
                new LayoutRow(array(
                    new LayoutColumn('', 1),
                    new LayoutColumn(
                        '<hr/>'
                        , 11)
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        ( $this->isDone !== true
                            ? $this->Task
                            : $this->Task
                        )
                    )
                )),
            ))
        );
    }

    /**
     * @return Status
     */
    public function getStatus()
    {

        $this->Status = new Status();
        if (!empty( $this->Task )) {
            /** @var Task $Task */
            foreach ((array)$this->Task as $Task) {
//                switch ($Task->getStatus()->getState()) {
//                    case Status::STATE_PLAN:
//                        $this->Status->addPlan();
//                        break;
//                    case Status::STATE_WORK:
//                        $this->Status->addWork();
//                        break;
//                    case Status::STATE_DONE:
//                        $this->Status->addDone();
//                        break;
//                }
                $this->Status->addPlan($Task->getStatus()->getPlan());
                $this->Status->addWork($Task->getStatus()->getWork());
                $this->Status->addDone($Task->getStatus()->getDone());
            }
        } else {
            if ($this->isDone === true) {
                $this->Status->addDone();
            } else {
                if ($this->isDone === false) {
                    $this->Status->addWork();
                } else {
                    $this->Status->addPlan();
                }
            }
        }
        return $this->Status;
    }
}
