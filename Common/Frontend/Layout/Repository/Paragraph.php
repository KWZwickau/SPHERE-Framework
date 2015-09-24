<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Paragraph
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Paragraph extends Extension implements ITemplateInterface
{

    /** @var string $Content */
    private $Content = '';
    /** @var string $Description */
    private $Description = '';

    /**
     * @param string $Content
     * @param string $Description
     */
    public function __construct($Content, $Description = '')
    {

        $this->Content = $Content;
        $this->Description = $Description;
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

        if (empty( $this->Description )) {
            return '<p>'.$this->Content.'</p>';
        } else {
            return '<p>'.$this->Content.' <small>'.$this->Description.'</small></p>';
        }
    }
}
