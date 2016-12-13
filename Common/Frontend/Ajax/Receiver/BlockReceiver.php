<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;
/**
 * Class BlockReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class BlockReceiver extends AbstractReceiver
{
    /**
     * @return string
     */
    public function getHandler()
    {
        return 'jQuery("' . $this->getSelector() . '").html(' . self::RESPONSE_CONTAINER . ');';
    }

    /**
     * @return string
     */
    public function getContainer()
    {
        return '<div class="' . $this->getIdentifier() . '"></div>';
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return '.'.$this->getIdentifier();
    }
}