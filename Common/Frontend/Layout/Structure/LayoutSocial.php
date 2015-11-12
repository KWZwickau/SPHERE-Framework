<?php
namespace SPHERE\Common\Frontend\Layout\Structure;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Window\Navigation\Link\Route;
use SPHERE\System\Extension\Extension;

/**
 * Class LayoutSocial
 *
 * @package SPHERE\Common\Frontend\Layout\Structure
 */
class LayoutSocial extends Extension implements ITemplateInterface
{

    const ALIGN_TOP = 'media-top';
    const ALIGN_MIDDLE = 'media-middle';
    const ALIGN_BOTTOM = 'media-bottom';

    /** @var IBridgeInterface $Template */
    private $Template = null;
    /** @var array $MediaList */
    private $MediaList = array();

    /**
     * @param array $MediaList
     */
    public function __construct($MediaList = array())
    {

        $this->MediaList = $MediaList;
        $this->Template = $this->getTemplate(__DIR__.'/LayoutSocial.twig');
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

        $this->Template->setVariable('MediaList', $this->MediaList);
        return $this->Template->getContent();
    }

    /**
     * @param string $Headline
     * @param string $Content
     * @param string $Object
     * @param string $Route
     * @param string $Alignment
     *
     * @return LayoutSocial
     */
    public function addMediaItem($Headline, $Content, $Object, $Route = '', $Alignment = LayoutSocial::ALIGN_TOP)
    {

        array_push($this->MediaList, array(
            'Alignment' => $Alignment,
            'Headline'  => $Headline,
            'Content'   => $Content,
            'Object'    => $Object,
            'Route'     => ( empty( $Route ) ? '' : (new Route($Route))->getValue() )
        ));
        return $this;
    }

    /**
     * @param LayoutSocial $MediaList
     *
     * @return LayoutSocial
     */
    public function addMediaList(LayoutSocial $MediaList)
    {

        $Item = array_pop($this->MediaList);
        $Item['Content'] .= $MediaList;
        array_push($this->MediaList, $Item);

        return $this;
    }
}
