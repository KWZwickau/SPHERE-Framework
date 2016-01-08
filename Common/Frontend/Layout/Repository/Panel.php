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
    /** @var bool|int $Filter */
    private $Filter = false;
    /** @var string $FormName */
    private $FormName = '';

    /**
     * @param string       $Title
     * @param string|array $Content
     * @param string       $Type
     * @param null|string  $Footer
     * @param bool         $Filter
     */
    public function __construct($Title, $Content, $Type = Panel::PANEL_TYPE_DEFAULT, $Footer = null, $Filter = false)
    {

        $this->Template = $this->getTemplate(__DIR__.'/Panel.twig');
        $this->Title = $Title;
        $this->Filter = $Filter;
        $this->Template->setVariable('Title', $this->Title);
        $this->Content = ( is_array($Content) ? array_filter($Content) : $Content );
        $this->Footer = ( is_array($Footer) ? array_filter($Footer) : $Footer );
        $this->Template->setVariable('Filter', $Filter);
        $this->Template->setVariable('Type', $Type);
        $this->FormName = $this->getName();
    }

    /**
     * @return string
     */
    public function getName()
    {

        if (empty( $this->FormName )) {
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

        if (!is_array($this->Content) && is_string($this->Content)) {
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

        if (is_array($this->Content)) {
            if ($this->Filter) {
                $this->Template->setVariable('Hash', $this->getHash());
                if (!is_numeric($this->Filter)) {
                    $this->Filter = 50;
                }
                $this->Template->setVariable('FilterSize', $this->Filter);
                if (isset($this->getGlobal()->POST['PanelSearch-' . md5($this->Title)])) {
                    $Value = $this->getGlobal()->POST['PanelSearch-' . md5($this->Title)];
                    $this->Template->setVariable('FilterValue', "'".$Value."'");
                } else {
                    $Value = '';
                    $this->Template->setVariable('FilterValue', '');
                }
                array_unshift($this->Content,
                    '<input type="text" class="form-control search" name="PanelSearch-' . md5($this->Title) . '" placeholder="Filtern" value="' . $Value . '">'
                    .( $this->Filter < count($this->Content)
                        ? new PullRight(new Label($this->Filter.' von '.count($this->Content).' Einträgen'))
                        : new PullRight(new Label(count($this->Content).' Einträge'))
                    )
                );
            } else {
                $this->Template->setVariable('FilterValue', '');
            }
            $this->Template->setVariable('Content', array_shift($this->Content));
            $this->Template->setVariable('ContentList', $this->Content);
        } else {
            $this->Template->setVariable('Content', $this->Content);
            $this->Template->setVariable('ContentList', array());
        }
        if (is_array($this->Footer)) {
            $this->Template->setVariable('Footer', trim(implode('', $this->Footer)));
        } else {
            $this->Template->setVariable('Footer', trim((string)$this->Footer));
        }
        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getHash()
    {

        if (empty( $this->Hash )) {
            $Content = $this->Content;
            array_walk($Content, function (&$G) {

                if (is_object($G)) {
                    $G = serialize($G);
                }
            });
            $this->Hash = md5(json_encode($Content));
        }
        return $this->Hash;
    }
}
