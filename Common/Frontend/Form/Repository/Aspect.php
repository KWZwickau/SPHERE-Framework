<?php
namespace SPHERE\Common\Frontend\Form\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Aspect
 *
 * @package SPHERE\Common\Frontend\Form\Repository
 */
class Aspect extends Extension implements ITemplateInterface
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
            return '<h5>'.$this->Title.'</h5>';
        } else {
            return '<h5>'.$this->Title.' <small>'.$this->Description.'</small></h5>';
        }
    }
}
