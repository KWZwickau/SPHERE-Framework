<?php
namespace SPHERE\Common\Frontend\Ajax\Template;

use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter;
use SPHERE\Common\Frontend\Ajax\Emitter\ScriptEmitter\NotifyScript;
use SPHERE\Common\Frontend\Ajax\Pipeline;
use SPHERE\Common\Frontend\Ajax\Receiver\InlineReceiver;

/**
 * Class Notify
 *
 * @package SPHERE\Common\Frontend\Ajax\Template
 */
class Notify
{
    const TYPE_INFO = 'info';
    const TYPE_SUCCESS = 'success';
    const TYPE_WARNING = 'warning';
    const TYPE_DANGER = 'danger';

    /** @var null|InlineReceiver $Receiver */
    private $Receiver = null;

    /**
     * Notify constructor.
     *
     * @param string $Title
     * @param string $Message ''
     * @param string $Type self::TYPE_INFO
     * @param int $Delay 1500
     */
    public function __construct($Title, $Message = '', $Type = self::TYPE_INFO, $Delay = 1500)
    {

        $Script = new NotifyScript($Title, $Message, $Type, $Delay);
        $this->Receiver = new InlineReceiver();
        $Emitter = new ScriptEmitter($this->Receiver, $Script);
        $Pipeline = new Pipeline();

        $Pipeline->addEmitter($Emitter);
        $this->Receiver->initContent($Pipeline);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string)$this->Receiver;
    }
}