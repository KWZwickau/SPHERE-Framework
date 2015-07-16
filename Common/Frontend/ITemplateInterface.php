<?php
namespace SPHERE\Common\Frontend;

/**
 * Interface ITemplateInterface
 *
 * @package SPHERE\Common\Frontend
 */
interface ITemplateInterface extends IFrontendInterface
{

    /**
     * @return string
     */
    public function getContent();

    /**
     * @return string
     */
    public function __toString();
}
