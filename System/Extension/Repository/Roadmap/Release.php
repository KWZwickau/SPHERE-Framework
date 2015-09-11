<?php
namespace SPHERE\System\Extension\Repository\Roadmap;

use SPHERE\Common\Frontend\Icon\Repository\Disable;
use SPHERE\Common\Frontend\Icon\Repository\Ok;
use SPHERE\Common\Frontend\Icon\Repository\Remove;
use SPHERE\Common\Frontend\Icon\Repository\TileBig;
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
    function __toString()
    {

        if (!empty( $this->Category )) {
            /** @var Category $Category */
            foreach ((array)$this->Category as $Category) {
                switch ($Category->getStatus()->getResult()) {
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
                            ? new Success(new TileBig().' Release: '.$this->Version)
                            : ( $this->isDone === false
                                ? new Danger(new TileBig().' Release: '.$this->Version)
                                : new Muted(new TileBig().' Release: '.$this->Version)
                            )
                        ), $this->Description)
                    ), 5),
                    new LayoutColumn(array(
                        new Small($this->isDone === true
                            ? new Success(new Ok().' Fertig')
                            : ( $this->isDone === false
                                ? new Danger(new Remove().' In Entwicklung')
                                : new Muted(new Disable().' In Planung')
                            )
                        ),
                        $this->Status
                    ), 7)
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        '<hr/>'
                    )
                )),
                new LayoutRow(array(
                    new LayoutColumn(
                        $this->Category
                    )
                )),
            ))
        );
    }
}
