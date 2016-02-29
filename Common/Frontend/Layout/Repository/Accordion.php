<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\IFrontendInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Accordion
 *
 * @package SPHERE\Common\Frontend\Layout\Repository
 */
class Accordion extends Extension implements IFrontendInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /** @var array $ContentList */
    private $ContentList = array();

    /**
     * @param bool|string $Title
     */
    public function __construct($Title = false)
    {

        $this->Template = $this->getTemplate(__DIR__.'/Accordion.twig');
        $this->Template->setVariable('Title', $Title);
    }

    /**
     * @param string $Header
     *
     * @param string $Content
     * @param bool   $Toggle
     *
     * @return $this
     */
    public function addItem($Header, $Content, $Toggle = false)
    {

        $this->ContentList[] = array(
            'Content' => $Content,
            'Toggle'  => $Toggle,
            'Header'  => $Header,
            'Hash' => md5(serialize(func_get_args()))
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

        $this->Template->setVariable('Hash', md5(serialize($this->ContentList)));
        $this->Template->setVariable('ContentList', $this->ContentList);
        return $this->Template->getContent();
    }
}
