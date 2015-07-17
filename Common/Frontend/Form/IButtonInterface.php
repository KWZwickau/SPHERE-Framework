<?php
namespace SPHERE\Common\Frontend\Form;

use SPHERE\Common\Frontend\ITemplateInterface;

interface IButtonInterface extends ITemplateInterface
{

    /**
     * @return string
     */
    public function getName();
}
