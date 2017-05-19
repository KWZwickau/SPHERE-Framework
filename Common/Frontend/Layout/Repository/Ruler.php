<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Ruler
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Ruler extends Extension implements ITemplateInterface
{

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

        return '<hr/>';
    }
}
