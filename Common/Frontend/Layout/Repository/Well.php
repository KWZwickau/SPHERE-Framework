<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Configuration;

/**
 * Class Well
 *
 * @package KREDA\Sphere\Client\Frontend\Layout\Type
 */
class Well extends Configuration implements ITemplateInterface
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

        return '<div class="well">'.$this->Content.'</div>';
    }
}
