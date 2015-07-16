<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Configuration;

/**
 * Class Container
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class Container extends Configuration implements ITemplateInterface
{

    /** @var string $Content */
    private $Content = '';

    /**
     * @param string $Content
     */
    public function __construct( $Content )
    {

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

        return '<div>'.$this->Content.'</div>';
    }
}
