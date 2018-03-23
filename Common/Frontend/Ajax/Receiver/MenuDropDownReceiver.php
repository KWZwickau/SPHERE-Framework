<?php
namespace SPHERE\Common\Frontend\Ajax\Receiver;

use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class MenuDropDownReceiver
 * @package SPHERE\Common\Frontend\Ajax\Receiver
 */
class MenuDropDownReceiver extends AbstractReceiver
{
    /** @var string $Name */
    private $Name = '';
    /** @var null|IIconInterface $Icon */
    private $Icon = null;

    /**
     * MenuDropDownReceiver constructor.
     *
     * @param string $Content
     * @param string $Name
     * @param null|IIconInterface $Icon
     */
    public function __construct( $Content = '', $Name = '', IIconInterface $Icon = null )
    {
        $this->Name = $Name;
        $this->Icon = $Icon;
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

        return '<li class="Dynamic-Frontend dropdown">'
            .'<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">'
                .( $this->Icon ? $this->Icon : '' )
                .( $this->Name ? $this->Name : '' )
            .'</a>'
            .'<ul class="' . $this->getIdentifier() . ' dropdown-menu">'
                .$this->getContent()
            .'</ul>'
        .'</li>';
    }

    /**
     * @return mixed
     */
    public function getSelector()
    {
        return '.'.$this->getIdentifier();
    }
}