<?php
namespace SPHERE\Common\Frontend\Ajax\Emitter;

use SPHERE\Common\Frontend\Ajax\Receiver\AbstractReceiver;
use SPHERE\System\Extension\Extension;

/**
 * Class AbstractEmitter
 *
 * @package SPHERE\Common\Frontend\Ajax
 */
abstract class AbstractEmitter extends Extension
{
    /** @var AbstractReceiver[] $AjaxReceiver */
    private $AjaxReceiver = array();

    /**
     * @return AbstractReceiver[]
     */
    final public function getAjaxReceiver()
    {
        return $this->AjaxReceiver;
    }

    /**
     * @param AbstractReceiver[] $AjaxReceiver
     */
    final public function setAjaxReceiver($AjaxReceiver)
    {
        $this->AjaxReceiver = $AjaxReceiver;
    }
}