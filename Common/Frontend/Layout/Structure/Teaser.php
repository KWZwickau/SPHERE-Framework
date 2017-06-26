<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Teaser
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Teaser extends Extension implements IFrontendInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /** @var array $ContentList */
    private $ContentList = array();

    private $HasActive = false;

    /**
     * Teaser constructor.
     */
    public function __construct()
    {

        $this->Template = $this->getTemplate(__DIR__.'/Teaser.twig');
    }

    /**
     * @param string $Source
     * @param string $Header
     * @param ILinkInterface $Link
     * @param string $Description
     * @param string $Title
     * @param bool $Toggle
     *
     * @return $this
     */
    public function addItem(
        $Source,
        $Header = '',
        ILinkInterface $Link = null,
        $Description = '',
        $Title = '',
        $Toggle = false)
    {

        if( empty( $Title ) ) {
            $Title = basename( $Source );
        }

        if( $Toggle === true ) {
            $this->HasActive = true;
        }

        $this->ContentList[] = array(
            'Source' => $Source,
            'Title'  => $Title,
            'Toggle' => $Toggle,
            'Caption' => $Header,
            'Description' => $Description,
            'Link' => ( $Link ? $Link->getContent() : '' )
        );
        return $this;
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

        if( !$this->HasActive && !empty( $this->ContentList ) ) {
            $this->ContentList[(rand( 1, count( $this->ContentList ) ) -1)]['Toggle'] = true;
        }

        $this->Template->setVariable('Hash', md5(serialize($this->ContentList)));
        $this->Template->setVariable('ContentList', $this->ContentList);
        return $this->Template->getContent();
    }
}
