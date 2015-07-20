<?php
namespace SPHERE\Common\Frontend\Link;

use SPHERE\Common\Frontend\ITemplateInterface;

/**
 * Interface ILinkInterface
 *
 * @package SPHERE\Common\Frontend\Link
 */
interface ILinkInterface extends ITemplateInterface
{

    /**
     * @return string
     */
    public function getName();
}
