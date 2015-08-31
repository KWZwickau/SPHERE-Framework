<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Listing
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Listing extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /** @var array $LinkList */
    private $LinkList = array();
    /** @var array $ContentList */
    private $ContentList = array();

    /**
     * @param array $TextList
     */
    public function __construct($TextList = array())
    {

        $this->Template = $this->getTemplate(__DIR__.'/Listing.twig');
        $this->Template->setVariable('TextList', $TextList);
    }

    /**
     * @param string $Title
     * @param string $Target
     *
     * @return $this
     */
    public function addLinkList($Title, $Target)
    {

        $this->LinkList[] = array('Target' => $Target, 'Title' => $Title);
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

        $this->Template->setVariable('LinkList', $this->LinkList);
        $this->Template->setVariable('ContentList', $this->ContentList);

        return $this->Template->getContent();
    }
}
