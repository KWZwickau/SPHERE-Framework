<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Title
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class Title extends Extension implements ITemplateInterface
{

    /** @var string $Title */
    private $Title = '';
    /** @var string $Description */
    private $Description = '';
    /** @var array $Menu */
    private $Menu = array();

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

        if (empty( $this->Menu )) {
            if (empty( $this->Description )) {
                return '<h4>'.$this->Title.'</h4><hr/>';
            } else {
                return '<h4>'.$this->Title.' <small>'.$this->Description.'</small></h4><hr/>';
            }
        } else {
            if (empty( $this->Description )) {
                return '<h4>'.$this->Title.'</h4><hr/>'
                .'<div class="btn-group" style="margin-bottom: 10px;">'.implode($this->Menu).'</div>';
            } else {
                return '<h4>'.$this->Title.' <small>'.$this->Description.'</small></h4><hr/>'
                .'<div class="btn-group" style="margin-bottom: 10px;">'.implode($this->Menu).'</div>';
            }
        }
    }

    /**
     * @param ILinkInterface $Button
     *
     * @return Title
     */
    public function addButton(ILinkInterface $Button)
    {

        $this->Menu[] = $Button->__toString();
        return $this;
    }
}
