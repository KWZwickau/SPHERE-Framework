<?php
namespace SPHERE\Common\Frontend\Message;

use SPHERE\Common\Frontend\ITemplateInterface;

/**
 * Interface IMessageInterface
 *
 * @package SPHERE\Common\Frontend\Message
 */
interface IMessageInterface extends ITemplateInterface
{

    /**
     * @return string
     */
    public function getName();
}
