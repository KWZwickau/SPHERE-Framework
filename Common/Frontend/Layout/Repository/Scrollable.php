<?php

namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Scrollable
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Scrollable extends Extension implements ITemplateInterface
{

    /** @var string $Content */
    private $Content = '';
    /** @var null|int $Height */
    private $Height = null;

    /**
     * @param string|array $Content
     * @param null|int $Height Pixel
     */
    public function __construct($Content, $Height = null)
    {

        if (is_array($Content)) {
            $Content = implode('', $Content);
        }
        $this->Content = $Content;
        $this->Height = $Height;
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

        return '<div class="pre-scrollable" style="overflow-y: auto;' .
            ($this->Height ? 'height:' . $this->Height . 'px;' : '')
            . '">' . $this->Content . '</div>';
    }
}
