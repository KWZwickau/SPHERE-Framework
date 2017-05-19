<?php

namespace SPHERE\Common\Frontend\Layout\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

class Dropdown extends Extension implements ITemplateInterface
{
    /** @var IBridgeInterface $Template */
    private $Template = null;

    /**
     * @param string $Name
     * @param string|array $Content
     * @param string $Label
     */
    public function __construct( $Name, $Content, $Label = '' )
    {

        if( is_array($Content) ) {
            $Content = implode( '', $Content);
        }

        $this->Template = $this->getTemplate(__DIR__ . '/Dropdown.twig');
        $this->Template->setVariable( 'Name', $Name );
        $this->Template->setVariable( 'Content', $Content );
        $this->Template->setVariable( 'Label', $Label );
        $this->Template->setVariable('Hash', crc32(uniqid('Dropdown',true)));
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
    public function __toString()
    {
        return $this->getContent();
    }
}
