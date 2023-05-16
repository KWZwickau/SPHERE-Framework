<?php
namespace SPHERE\Common\Frontend\Layout\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\Icon\Repository\ChevronDown;
use SPHERE\Common\Frontend\Icon\Repository\ChevronRight;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Link\Repository\Link;
use SPHERE\System\Extension\Extension;

/**
 * Class CustomPanel
 *
 * @package SPHERE\Common\Frontend\Layout\Repository\Elements
 */
class CustomPanel extends Extension implements ITemplateInterface
{
    const PANEL_TYPE_DEFAULT = 'panel-default';
    const PANEL_TYPE_PRIMARY = 'panel-primary';
    const PANEL_TYPE_SUCCESS = 'panel-success';
    const PANEL_TYPE_WARNING = 'panel-warning';
    const PANEL_TYPE_INFO = 'panel-info';
    const PANEL_TYPE_DANGER = 'panel-danger';
    /** @var string $Hash */
    protected $Hash = '';
    /** @var string $Title */
    private $Title = '';
    /** @var IBridgeInterface $Template */
    private $Template = null;
    /** @var string|array $Content */
    private $Content = '';
    /** @var string|array $Footer */
    private $Footer = '';
    /** @var string $FormName */
    private $FormName = '';

    /**
     * @param           $Title
     * @param           $Content
     * @param           $Footer
     */
    public function __construct($Title, $Content, $Footer = null)
    {
        $this->Template = $this->getTemplate(__DIR__.'/CustomPanel.twig');
        $this->Title = $Title;
        $this->Template->setVariable('Title', $this->Title);
        $this->Content = (is_array($Content) ? array_filter($Content) : $Content);
        $this->Footer = (is_array($Footer) ? array_filter($Footer) : $Footer);
        $this->FormName = $this->getName();
        $this->Template->setVariable('Type', Panel::PANEL_TYPE_DEFAULT);
    }

    /**
     * @param $Type
     *
     * @return $this
     */
    public function setPanelType($Type = Panel::PANEL_TYPE_DEFAULT)
    {

        $this->Template->setVariable('Type', $Type);
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        if(empty($this->FormName)) {
            return md5(serialize($this->Content));
        } else {
            return $this->FormName;
        }
    }

    /**
     * @return array
     */
    public function getElementList()
    {
        if(!is_array($this->Content) && is_string($this->Content)) {
            return (array)$this->Content;
        } else {
            return $this->Content;
        }
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
        if(is_array($this->Content)) {
            $this->Template->setVariable('Content', '');
            $this->Template->setVariable('ContentList', $this->Content);
        } else {
            $this->Template->setVariable('Content', $this->Content);
            $this->Template->setVariable('ContentList', array());
        }
        if(is_array($this->Footer)) {
            $this->Template->setVariable('Footer', trim(implode('', $this->Footer)));
        } else {
            $this->Template->setVariable('Footer', trim((string)$this->Footer));
        }
        return $this->Template->getContent();
    }

    /**
     * @param $isOpen
     *
     * @return $this
     */
    public function setAccordeon($isOpen = false)
    {
        $this->Template->setVariable('toggleOpen', $isOpen);
        $this->Template->setVariable('toggleIconRight', new ChevronRight());
        $this->Template->setVariable('toggleIconDown', new ChevronDown());
        $this->Template->setVariable('Hash', $this->getHash());
        return $this;
    }

    /**
     * @param Link|null $link
     *
     * @return $this
     */
    public function setLink(Link $link = null)
    {
        $this->Template->setVariable('Link', $link);
        return $this;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        if(empty($this->Hash)) {
            $Content = $this->Content;
            if(is_array($this->Content)) {
                array_walk($Content, function(&$G) {

                    if(is_object($G)) {
                        $G = serialize($G);
                    }
                });
                $this->Hash = md5(json_encode($Content));
            } else {
                $this->Hash = md5(json_encode($Content));
            }
        }
        return $this->Hash;
    }

    /**
     * @param string $Hash
     * Ist der Inhalt zu anderem Panel identisch, wird ein eindeutiger Identifier benötigt
     *
     * @return CustomPanel
     */
    public function setHash($Hash)
    {
        $Hash = crc32($Hash);
        $this->Hash = 'CustomPanel-'.$Hash;
        $this->Template->setVariable('Hash', $this->Hash);
        return $this;
    }
}
