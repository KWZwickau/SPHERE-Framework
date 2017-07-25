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

    /** @var IBridgeInterface $Template */
    protected $Template = null;

    /**
     * ToolTip constructor.
     * @param string $Text
     * @param string $ToolTip
     */
    public function __construct($Text, $ToolTip)
    {

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
     * @return $this
     */
    public function enableHtml()
    {
        $this->Template->setVariable('EnableHtml', true);
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return $this->Template->getContent();
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return 'ToolTip-' . sha1(uniqid("ToolTip", true));
    }
}