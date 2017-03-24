<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;
/**
 * Class InlineBlockReceiver
 *
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class InlineBlockReceiver extends AbstractReceiver
{

    /**
     * InlineBlockReceiver constructor.
     *
     * @param string $Content
     */
    public function __construct( $Content = '' )
    {
        $this->setContent( $Content );
        parent::__construct();
    }

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
        return '<span style="display: inline-block;" class="Dynamic-Frontend ' . $this->getIdentifier() . '">'.$this->getContent().'</span>';
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return '.'.$this->getIdentifier();
    }
}
