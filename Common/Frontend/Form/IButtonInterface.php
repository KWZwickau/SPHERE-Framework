<?php
namespace SPHERE\Common\Frontend\Form;

use SPHERE\Common\Frontend\ITemplateInterface;

/**
 * Interface IButtonInterface
 *
 * @package SPHERE\Common\Frontend\Form
 */
interface IButtonInterface extends ITemplateInterface
{

    /**
     * @return string
     */
    public function getName();
}
