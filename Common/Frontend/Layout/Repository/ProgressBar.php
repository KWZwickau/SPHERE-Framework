<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class ProgressBar
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class ProgressBar extends Extension implements ITemplateInterface
{

    const BAR_COLOR_SUCCESS = 'progress-bar-success';
    const BAR_COLOR_WARNING = 'progress-bar-warning';
    const BAR_COLOR_DANGER = 'progress-bar-danger';
    const BAR_COLOR_INFO = 'progress-bar-info';
    const BAR_COLOR_STRIPED = 'progress-bar-striped';

    /** @var IBridgeInterface $Template */
    private $Template = null;
    /** @var float $Done */
    private $Done = 0.0;
    /** @var float $Work */
    private $Work = 0.0;
    /** @var float $Plan */
    private $Plan = 100.0;

    /** @var int $Size */
    private $Size = 4;

    /** @var string $ColorDone */
    private $ColorDone = self::BAR_COLOR_SUCCESS;
    /** @var string $ColorWork */
    private $ColorWork = self::BAR_COLOR_WARNING;
    /** @var string $ColorPlan */
    private $ColorPlan = self::BAR_COLOR_STRIPED;

    /**
     * ProgressBar constructor.
     *
     * @param float $Done
     * @param float $Work
     * @param float $Plan
     * @param int $Height 4px
     */
    public function __construct($Done, $Work, $Plan, $Height = 4)
    {

        $this->setStatus($Done, $Work, $Plan);
        $this->setSize($Height);
        $this->Template = $this->getTemplate(__DIR__.'/ProgressBar.twig');
    }

    /**
     * @param float $Done
     * @param float $Work
     * @param float $Plan
     *
     * @return ProgressBar
     */
    public function setStatus($Done, $Work, $Plan)
    {

        $this->Done = (float)$Done;
        $this->Work = (float)$Work;
        $this->Plan = (float)$Plan;
        return $this;
    }

    /**
     * @param $Height
     *
     * @return ProgressBar
     */
    public function setSize($Height)
    {
        $this->Size = (int)$Height;
        return $this;
    }

    /**
     * @param string $Done
     * @param string $Work
     * @param string $Plan
     *
     * @return ProgressBar
     */
    public function setColor(
        $Done = ProgressBar::BAR_COLOR_SUCCESS,
        $Work = ProgressBar::BAR_COLOR_WARNING,
        $Plan = ProgressBar::BAR_COLOR_STRIPED
    ) {

        $this->ColorDone = $Done;
        $this->ColorWork = $Work;
        $this->ColorPlan = $Plan;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $this->Template->setVariable('Size', $this->Size);

        $this->Template->setVariable('Done', $this->Done);
        $this->Template->setVariable('Work', $this->Work);
        $this->Template->setVariable('Plan', $this->Plan);

        $this->Template->setVariable('ColorDone', $this->ColorDone);
        $this->Template->setVariable('ColorWork', $this->ColorWork);
        $this->Template->setVariable('ColorPlan', $this->ColorPlan);

        return $this->Template->getContent();
    }
}
