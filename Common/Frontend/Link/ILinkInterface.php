<?php
namespace SPHERE\Common\Frontend\Link;

use SPHERE\Common\Frontend\ITemplateInterface;

interface ILinkInterface extends ITemplateInterface
{

    /**
     * @return string
     */
    public function getName();
}
