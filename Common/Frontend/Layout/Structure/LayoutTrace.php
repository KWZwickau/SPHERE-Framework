<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class LayoutTrace
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class LayoutTrace extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param array $Trace
     */
    public function __construct($Trace = array())
    {

        $this->Template = $this->getTemplate(__DIR__.'/LayoutTrace.twig');
        $this->Template->setVariable('TraceList', $Trace);
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
