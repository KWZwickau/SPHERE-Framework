<?php
/**
 * Created by PhpStorm.
 * User: Kauschke
 * Date: 14.03.2017
 * Time: 09:42
 */

namespace SPHERE\Common\Frontend\Text\Repository;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class ToolTip
 *
 * @package SPHERE\Common\Frontend\Text\Repository
 */
class ToolTip extends Extension
{
    /** @var string $Value */
    private $Value = '';

    private $ToolTip = '';

    /** @var IBridgeInterface $Template */
    protected $Template = null;

    public function __construct($Text, $ToolTip = false)
    {

        $this->setValue($Text);
        $this->setToolTip($ToolTip);

        $this->Template = $this->getTemplate(__DIR__.'/ToolTip.twig');
        $this->Template->setVariable('ElementName', $Text);

        if ($ToolTip) {
            if (is_string($ToolTip)) {
                $this->Template->setVariable('ElementToolTip', $ToolTip);
            } else {
                $this->Template->setVariable('ElementToolTip', $Text);
            }
        }

        $this->Template->setVariable('ElementHash', $this->getHash());
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->Template->getContent();
//        return '<div data-trigger="hover" data-toggle="popover" data-content="' . $this->getToolTip() . '">' . $this->getValue() . '</div>';
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->Value;
    }

    /**
     * @param string $Value
     */
    public function setValue($Value)
    {
        $this->Value = $Value;
    }

    /**
     * @return string
     */
    public function getToolTip()
    {
        return $this->ToolTip;
    }

    /**
     * @param string $ToolTip
     */
    public function setToolTip($ToolTip)
    {
        $this->ToolTip = $ToolTip;
    }

    public function getHash()
    {
        return 'Link-ToolTip-' . sha1($this->getValue() . $this->getToolTip());
    }
}