<?php
namespace SPHERE\System\Extension\Repository\Roadmap;

use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\TileSmall;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\PullRight;
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
    public function __toString()
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

        if ($this->isDone === true) {
            $Toggle = uniqid();
            return (string)new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn('', 1),
                        new LayoutColumn(array(
                            new Header(new Success(new TileSmall().' Feature: '.$this->Name), $this->Description),
                        ), 10),
                        new LayoutColumn(( !empty( $this->Task ) ? new PullRight(
                            '<button type="button" class="btn btn-default" data-toggle="collapse" data-target="#'
                            .$Toggle.'">'.new TileSmall().'</button>'
                        ) : '' ), 1)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn('', 1),
                        new LayoutColumn('<hr/>', 11)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            '<span id="'.$Toggle.'" class="collapse">'.implode('', $this->Task).'</span>'
                        )
                    ),
                ))
            );
        } else {
            return (string)new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn('', 1),
                        new LayoutColumn(array(
                            new Header(( $this->isDone === false
                                ? new Danger(new TileSmall().' Feature: '.$this->Name)
                                : new Muted(new TileSmall().' Feature: '.$this->Name)
                            ), $this->Description)
                        ), 6),
                        new LayoutColumn(array(
                            new Small(( $this->isDone === false
                                ? new Danger(
                                    new CogWheels().' In Entwicklung '
                                    .number_format($this->Status->getDonePercent(), 1, ',', '').'%'
                                )
                                : new Muted(
                                    new Disable().' In Planung '
                                    .number_format($this->Status->getDonePercent(), 1, ',', '').'%'
                                )

                            ))
                            .$this->Status
                        ), 5)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn('', 1),
                        new LayoutColumn('<hr/>', 11)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn($this->Task)
                    )),
                ))
            );
        }
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
