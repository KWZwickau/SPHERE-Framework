<?php
namespace SPHERE\Common\Window;

use MOC\V\Component\Template\Component\IBridgeInterface;
use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\Common\Frontend\Link\ILinkInterface;
use SPHERE\Common\Frontend\Text\Repository\Muted;
use SPHERE\System\Extension\Extension;

/**
 * Class Stage
 *
 * @package SPHERE\Common\Window
 */
class Stage extends Extension implements ITemplateInterface
{

    /** @var IBridgeInterface $Template */
    private $Template = null;

    /** @var string $Title */
    private $Title = '';
    /** @var string $Description */
    private $Description = '';
    /** @var string $Message */
    private $Message = '';
    /** @var string $Content */
    private $Content = '';
    /** @var array $Menu */
    private $Menu = array();

    /**
     * @param null|string $Title
     * @param null|string $Description
     */
    public function __construct($Title = null, $Description = null)
    {

        $this->Template = $this->getTemplate(__DIR__.'/Stage.twig');
        if (null !== $Title) {
            $this->setTitle($Title);
        }
        if (null !== $Description) {
            $this->setDescription($Description);
        }
    }

    /**
     * @param string $Value
     *
     * @return Stage
     */
    public function setTitle($Value)
    {

        $this->Title = $Value;
        return $this;
    }

    /**
     * @param string $Value
     *
     * @return Stage
     */
    public function setDescription($Value)
    {

        $this->Description = $Value;
        return $this;
    }

    /**
     * @param string $Message
     *
     * @return Stage
     */
    public function setMessage($Message)
    {

        $this->Message = new Muted($Message);
        return $this;
    }

    /**
     * @param ILinkInterface $Button
     *
     * @return Stage
     */
    public function addButton(ILinkInterface $Button)
    {

        $this->Menu[] = $Button->__toString();
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

        $this->Template->setVariable('StageTitle', $this->Title);
        $this->Template->setVariable('StageDescription', $this->Description);
        $this->Template->setVariable('StageMessage', $this->Message);
        $this->Template->setVariable('StageContent', $this->Content);
        $this->Template->setVariable('StageMenu', $this->Menu);

        return $this->Template->getContent();
    }

    /**
     * @param string $Content
     *
     * @return Stage
     */
    public function setContent($Content)
    {

        $this->Content = $Content;
        return $this;
    }
}
