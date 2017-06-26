<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Slick
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class Slick extends Extension implements IFrontendInterface, ITemplateInterface
{
    /** @var IBridgeInterface $Template */
    private $Template = null;
    /** @var array $SlideList */
    private $SlideList = array();

    /**
     * Slick constructor.
     */
    public function __construct()
    {

        $this->Template = $this->getTemplate(__DIR__.'/Slick.twig');
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $this->Template->setVariable( 'SlideList', $this->SlideList );
        return $this->Template->getContent();
    }

    /**
     * @param string $Content
     * @return $this
     */
    public function addContent( $Content )
    {
        $this->SlideList[] = $Content;
        return $this;
    }

    /**
     * @param $Source
     * @return $this
     */
    public function addImage( $Source )
    {
//        $this->addContent( '<img src="'.$Source.'"/>' );
        $this->addContent( '<div style="background-repeat: no-repeat; background-position: center center; width: 100%; height: 454px; background-image: url('.$Source.');"></div>' );
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }
}
