<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Headline
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Headline extends Extension implements ITemplateInterface
{

    /** @var string $Title */
    private $Title = '';
    /** @var string $Description */
    private $Description = '';

    /**
     * @param string $Title
     * @param string $Description
     */
    public function __construct($Title, $Description = '')
    {

        $this->Title = $Title;
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
            return '<h4>'.$this->Title.'</h4>';
        } else {
            return '<h4>'.$this->Title.' <small>'.$this->Description.'</small></h4>';
        }
    }
}
