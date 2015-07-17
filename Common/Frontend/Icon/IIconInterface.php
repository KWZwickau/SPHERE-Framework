<?php
namespace SPHERE\Common\Frontend\Icon;

use SPHERE\Common\Frontend\ITemplateInterface;

/**
 * Interface IIconInterface
 *
 * @package SPHERE\Common\Frontend\Icon
 */
interface IIconInterface extends ITemplateInterface
{

    /**
     * @return string
     */
    public function getValue();
}
