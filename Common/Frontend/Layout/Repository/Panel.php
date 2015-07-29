<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Panel
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Panel extends Extension implements ITemplateInterface
{

    const PANEL_TYPE_DEFAULT = 'panel-default';
    const PANEL_TYPE_PRIMARY = 'panel-primary';
    const PANEL_TYPE_SUCCESS = 'panel-success';
    const PANEL_TYPE_WARNING = 'panel-warning';
    const PANEL_TYPE_INFO = 'panel-info';
    const PANEL_TYPE_DANGER = 'panel-danger';

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param string       $Title
     * @param string|array $Content
     * @param string       $Type
     * @param null|string  $Footer
     */
    public function __construct( $Title, $Content, $Type = Panel::PANEL_TYPE_DEFAULT, $Footer = null )
    {

        $this->Template = $this->getTemplate( __DIR__.'/Panel.twig' );
        $this->Template->setVariable( 'Title', $Title );
        if (is_array( $Content )) {
            $this->Template->setVariable( 'Content', array_shift( $Content ) );
            $this->Template->setVariable( 'ContentList', $Content );
        } else {
            $this->Template->setVariable( 'Content', $Content );
            $this->Template->setVariable( 'ContentList', array() );
        }
        $this->Template->setVariable( 'Footer', $Footer );
        $this->Template->setVariable( 'Type', $Type );
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getName()
    {

        return '';
    }
}
