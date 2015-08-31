<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class LayoutTabs
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class LayoutTabs extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param LayoutTab[] $TabList
     */
    public function __construct($TabList)
    {

        $this->Template = $this->getTemplate(__DIR__.'/LayoutTabs.twig');
        $this->Template->setVariable('TabList', implode('', $TabList));
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

        return $this->Template->getContent();
    }
}
