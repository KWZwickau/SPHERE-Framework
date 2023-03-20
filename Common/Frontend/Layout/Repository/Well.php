<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Well
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Well extends Extension implements ITemplateInterface
{

    /** @var string $Content */
    private $Content = '';

    /** @var string $Style */
    private $Style = '';

    /**
     * @param string $Content
     */
    public function __construct($Content)
    {

        if( is_array($Content) ) {
            $Content = implode($Content);
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

    public function setPadding($padding = '19px')
    {

        $this->Style .= 'padding: '.$padding.';';
        return $this;
    }

    public function setMarginBottom($MarginBottom = '20px')
    {

        $this->Style .= 'margin-bottom: '.$MarginBottom.';';
        return $this;
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return '<div class="well" style="'.$this->Style.'">'.$this->Content.'</div>';
    }
}
