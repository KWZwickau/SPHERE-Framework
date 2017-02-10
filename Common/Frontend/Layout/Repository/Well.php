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

    /**
     * @return string
     */
    public function getContent()
    {

        return '<div class="well">'.$this->Content.'</div>';
    }
}
