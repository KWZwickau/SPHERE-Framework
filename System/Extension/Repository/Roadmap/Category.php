<?php
namespace SPHERE\System\Extension\Repository\Roadmap;

use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\TileList;
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
    function __toString()
    {

        switch ($this->Status->getResult()) {
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
                    new LayoutColumn(array(
                        new Header(( $this->isDone === true
                            ? new Success(new TileList().' Kategorie: '.$this->Name)
                            : ( $this->isDone === false
                                ? new Danger(new TileList().' Kategorie: '.$this->Name)
                                : new Muted(new TileList().' Kategorie: '.$this->Name)
                            )
                        ), $this->Description)
                    ), 6),
                    new LayoutColumn(array(
                        new Small($this->isDone === true
                            ? new Success(new Ok().' Fertig')
                            : ( $this->isDone === false
                                ? new Danger(new Remove().' In Entwicklung')
                                : new Muted(new Disable().' In Planung')
                            )
                        ),
                        $this->Status
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

    /**
     * @return Status
     */
    public function getStatus()
    {

        $this->Status = new Status();
        if (!empty( $this->Feature )) {
            /** @var Feature $Feature */
            foreach ((array)$this->Feature as $Feature) {
                switch ($Feature->getStatus()->getResult()) {
                    case Status::STATE_PLAN:
                        $this->Status->setPlan();
                        break;
                    case Status::STATE_WORK:
                        $this->Status->setWork();
                        break;
                    case Status::STATE_DONE:
                        $this->Status->setDone();
                        break;
                }
            }
        } else {
            if ($this->isDone === true) {
                $this->Status->setDone();
            } else {
                if ($this->isDone === false) {
                    $this->Status->setWork();
                } else {
                    $this->Status->setPlan();
                }
            }
        }
        return $this->Status;
    }
}
