<?php
namespace SPHERE\System\Extension\Repository\Roadmap;

use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\Tag;
use SPHERE\Common\Frontend\Layout\Repository\Header;
use SPHERE\Common\Frontend\Layout\Repository\Listing;
use SPHERE\Common\Frontend\Layout\Structure\Layout;
use SPHERE\Common\Frontend\Layout\Structure\LayoutColumn;
use SPHERE\Common\Frontend\Layout\Structure\LayoutGroup;
use SPHERE\Common\Frontend\Layout\Structure\LayoutRow;
use SPHERE\Common\Frontend\Text\Repository\Danger;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\Common\Frontend\Text\Repository\Small;
use SPHERE\Common\Frontend\Text\Repository\Strikethrough;
use SPHERE\Common\Frontend\Text\Repository\Success;

/**
 * Class Task
 *
 * @package SPHERE\System\Extension\Repository\Roadmap
 */
class Task
{

    /** @var string $Name */
    private $Name = '';
    /** @var string $Description */
    private $Description = '';
    /** @var string $Duty */
    private $Duty = array();
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
        $this->Status = new Status();
    }

    /**
     * @param string    $Content
     * @param null|bool $isDone
     *
     * @return Task
     */
    public function createDuty($Content, $isDone = null)
    {

        if ($isDone === true) {
            $this->Status->setDone();
            $Content = new Small(new Ok().' '.new Strikethrough($Content));
        } else {
            if ($isDone === false) {
                $this->Status->setWork();
                $Content = new Small(new Remove().' '.$Content);
            } else {
                $this->Status->setPlan();
                $Content = new Small(new Disable().' '.$Content);
            }
        }
        array_push($this->Duty, $Content);

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

        return $this;
    }

    /**
     * @return string
     */
    function __toString()
    {

        return (string)new Layout(
            new LayoutGroup(array(
                new LayoutRow(array(
                    new LayoutColumn('', 2),
                    new LayoutColumn(array(
                        new Header(( $this->isDone === true
                            ? new Success(new Tag().' Task: '.$this->Name)
                            : ( $this->isDone === false
                                ? new Danger(new Tag().' Task: '.$this->Name)
                                : new Muted(new Tag().' Task: '.$this->Name)
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
                    ), 4)
                )),
                new LayoutRow(array(
                    new LayoutColumn('', 3),
                    new LayoutColumn(
                        ( $this->isDone !== true
                            ? ( empty( $this->Duty )
                                ? ''
                                : new Listing($this->Duty)
                            )
                            : ''
                        )
                        , 9)
                )),
            ))
        );
    }

    /**
     * @return Status
     */
    public function getStatus()
    {

        if (empty( $this->Duty )) {
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
