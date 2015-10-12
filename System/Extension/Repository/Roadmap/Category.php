<?php
namespace SPHERE\System\Extension\Repository\Roadmap;

use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\TileList;
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
 * Class Category
 *
 * @package SPHERE\System\Extension\Repository\Roadmap
 */
class Category
{

    /** @var Feature[] $Feature */
    private $Feature = array();

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
     * @return Feature
     */
    public function createFeature($Name, $Description = '', $isDone = null)
    {

        $Feature = new Feature($Name, $Description, $isDone);
        array_push($this->Feature, $Feature);
        return $Feature;
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
                        new LayoutColumn(array(
                            new Header(new Success(new TileList().' Kategorie: '.$this->Name), $this->Description),
                        ), 11),
                        new LayoutColumn(( !empty( $this->Feature ) ? new PullRight(
                            '<button type="button" class="btn btn-default" data-toggle="collapse" data-target="#'
                            .$Toggle.'">'.new TileList().'</button>'
                        ) : '' ), 1)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn('<hr/>', 12)
                    )),
                    new LayoutRow(
                        new LayoutColumn(
                            '<span id="'.$Toggle.'" class="collapse">'.implode('', $this->Feature).'</span>'
                        )
                    ),
                ))
            );
        } else {
            return (string)new Layout(
                new LayoutGroup(array(
                    new LayoutRow(array(
                        new LayoutColumn(array(
                            new Header(( $this->isDone === false
                                ? new Danger(new TileList().' Kategorie: '.$this->Name)
                                : new Muted(new TileList().' Kategorie: '.$this->Name)
                            ), $this->Description)
                        ), 6),
                        new LayoutColumn(array(
                            new Small($this->isDone === false
                                ? new Danger(
                                    new CogWheels().' In Entwicklung '
                                    .number_format($this->Status->getDonePercent(), 1, ',', '').'%'
                                )
                                : new Muted(
                                    new Disable().' In Planung '
                                    .number_format($this->Status->getDonePercent(), 1, ',', '').'%'
                                )
                            ).$this->Status
                        ), 6)
                    )),
                    new LayoutRow(array(
                        new LayoutColumn(
                            '<hr/>'
                        )
                    )),
                    new LayoutRow(array(
                        new LayoutColumn($this->Feature)
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
        if (!empty( $this->Feature )) {
            /** @var Feature $Feature */
            foreach ((array)$this->Feature as $Feature) {
                $this->Status->addPlan($Feature->getStatus()->getPlan());
                $this->Status->addWork($Feature->getStatus()->getWork());
                $this->Status->addDone($Feature->getStatus()->getDone());
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
