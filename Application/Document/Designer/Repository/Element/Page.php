<?php
namespace SPHERE\Application\Document\Designer\Repository\Element;

use MOC\V\Component\Template\Component\IBridgeInterface;
use MOC\V\Component\Template\Template;
use SPHERE\Common\Frontend\ITemplateInterface;

/**
 * Class Page
 * @package SPHERE\Application\Document\Designer\Repository\Element
 */
class Page implements ITemplateInterface
{
    /** @var IBridgeInterface|null $Template */
    private $Template = null;
    /** @var null|string */
    private $Content = null;

    function __construct($Content = null)
    {
        $this->Content = $Content;
        $this->Template = Template::getTwigTemplateString('<div class="SDD-Page">{{ Content }}</div>');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {
        if (is_array($this->Content)) {
            $this->Content = implode($this->Content);
        }

        $this->Template->setVariable('Content', $this->Content);

        return $this->Template->getContent();
    }

    public function setContent($Content)
    {
        $this->Content = $Content;
    }
}
