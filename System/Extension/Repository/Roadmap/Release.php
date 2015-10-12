<?php
namespace SPHERE\System\Extension\Repository\Roadmap;

use SPHERE\Common\Frontend\Icon\Repository\CogWheels;
use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
use SPHERE\Common\Frontend\Layout\Repository\Title;
use SPHERE\Common\Frontend\Layout\Repository\Well;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Message\Repository\Info;
use SPHERE\Common\Frontend\Message\Repository\Warning;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Success;

/**
 * Class Release
 *
 * @package SPHERE\System\Extension\Repository\Roadmap
 */
class Release
{

    /** @var Category[] $Category */
    private $Category = array();

    /** @var string $Version */
    private $Version = '0.1.0';
    /** @var string $Description */
    private $Description = '';
    /** @var bool|null $isDone */
    private $isDone = null;
    /** @var Status $Status */
    private $Status = null;

    /**
     * @param string    $Version
     * @param string    $Description
     * @param bool|null $isDone
     */
    public function __construct($Version = '0.1.0', $Description = '', $isDone = null)
    {

        $this->Version = $Version;
        $this->Description = $Description;
        $this->isDone = $isDone;
        $this->Status = new Status();
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool   $isDone Stable & Public
     *
     * @return Category
     */
    public function createCategory($Name, $Description = '', $isDone = null)
    {

        $Category = new Category($Name, $Description, $isDone);
        array_push($this->Category, $Category);
        return $Category;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        if (!empty( $this->Category )) {
            /** @var Category $Category */
            foreach ((array)$this->Category as $Category) {
                $this->Status->addPlan($Category->getStatus()->getPlan());
                $this->Status->addWork($Category->getStatus()->getWork());
                $this->Status->addDone($Category->getStatus()->getDone());
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

        $Toggle = uniqid();
        $Content = new Title(( $this->isDone === true
                ? new Success(new TileBig().' Release: '.$this->Version)
                : ( $this->isDone === false
                    ? new Danger(new TileBig().' Release: '.$this->Version)
                    : new Muted(new TileBig().' Release: '.$this->Version)
                )
            ), $this->Description)
            .new Small($this->isDone === true
                ? ''
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
                ?
                '<button type="button" class="btn btn-default" data-toggle="collapse" data-target="#'
                .$Toggle.'">'.new Ok().' Entwicklung abgeschlossen'.'</button>'
                : $this->Status
            );

        return (string)new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn(array(
                        ( null === $this->isDone
                            ? new Well($Content)
                            : ( false === $this->isDone
                                ? new Info($Content)
                                : new Warning($Content)
                            )
                        )
                    )),
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        $this->isDone !== true
                            ? new Well(implode($this->Category))
                            : '<span id="'.$Toggle.'" class="collapse">'.new Warning(implode($this->Category)).'</span>'
                    )
                )),
            ))
        );
    }
}
