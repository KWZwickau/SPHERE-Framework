<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Container
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class Container extends Extension implements ITemplateInterface
{

    /** @var string $Content */
    private $Content = '';

    private array $styles = array();

    /**
     * @param string|array $Content
     */
    public function __construct($Content)
    {
        if( is_array($Content) ) {
            $Content = implode( '', $Content);
        }
        $this->Content = $Content;
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
    public function getContent(): string
    {
        $styleString = '';
        if (!empty($this->styles)) {
            $string = implode('', $this->styles);
            $styleString = "style=\"$string\"";
        }

        return "<div $styleString>$this->Content</div>";
    }

    /**
     * @param array $styles
     *
     * @return Container
     */
    public function setStyle(array $styles): Container
    {
        $this->styles = $styles;

        return $this;
    }
}
