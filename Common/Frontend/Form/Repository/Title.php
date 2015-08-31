<?php
namespace SPHERE\Common\Frontend\Form\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Title
 *
 * @package SPHERE\Common\Frontend\Form\Repository
 */
class Title extends Extension implements ITemplateInterface
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
            return '<h4>'.$this->Title.'</h4><hr/>';
        } else {
            return '<h4>'.$this->Title.' <small>'.$this->Description.'</small></h4><hr/>';
        }
    }
}
