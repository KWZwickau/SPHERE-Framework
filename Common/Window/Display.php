<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Script;
use SPHERE\Common\Style;
use SPHERE\System\Extension\Configuration;

/**
 * Class Display
 *
 * @package SPHERE\Common\Window
 */
class Display extends Configuration implements IFrontendInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     *
     */
    function __construct()
    {

        $this->Template = $this->getTemplate( __DIR__.'/Display.twig' );
    }

    /**
     * @return string
     */
    public function getContent()
    {
        $this->Template->setVariable( 'StyleManager', Style::getManager() );
        $this->Template->setVariable( 'ScriptManager', Script::getManager() );
        $this->Template->setVariable( 'PathBase', $this->getRequest()->getPathBase() );

        return $this->Template->getContent();
    }
}
