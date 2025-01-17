<?php
namespace SPHERE\Common\Frontend\Table\Structure;

use SPHERE\Common\Frontend\ITemplateInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class TableColumn
 *
 * @package SPHERE\Common\Frontend\Table\Structure
 */
class TableColumn extends Extension implements ITemplateInterface
{

    /** @var string $Content */
    private $Content = '';
    /** @var int $Size */
    private $Size = 1;
    /** @var string $Width */
    private $Width = 'auto';

    /** @var string $BackgroundColor */
    private $BackgroundColor = 'inherit'; // initial
    /** @var string $VerticalAlign */
    private $VerticalAlign = 'baseline';
    /** @var string $Color */
    private $Color = 'inherit'; // initial
    /** @var string $Opacity */
    private $Opacity = '1';
    /** @var string $MinHeight */
    private $MinHeight = '0';
    /** @var string $Padding */
    private $Padding = '0';


    /**
     * @param string $Content
     * @param int    $Size
     * @param string $Width
     */
    public function __construct($Content, $Size = 1, $Width = 'auto')
    {

        if (is_object($Content) && $Content instanceof \DateTime) {
            $Content = $Content->format('d.m.Y H:i:s');
        }
        /**
         * Remove "small" from child tables
         */
        $Content = preg_replace(
            '!<table(.*?)class="(.*?)\ssmall"(.*?)>!is',
            '<table${1}class="${2}"${3}>', ($Content ?? ''));
        $this->Content = $Content;
        $this->Size = $Size;
        $this->Width = $Width;
    }

    /**
     * @return int
     */
    public function getSize()
    {

        return $this->Size;
    }

    /**
     * @return string
     */
    public function getWidth()
    {

        return $this->Width;
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

        return (string)$this->Content;
    }

    /**
     * @return string
     */
    public function getBackgroundColor()
    {
        return $this->BackgroundColor;
    }

    /**
     * @param string $BackgroundColor
     *
     * @return TableColumn
     */
    public function setBackgroundColor($BackgroundColor)
    {
        $this->BackgroundColor = $BackgroundColor;
        return $this;
    }

    /**
     * @return string
     */
    public function getVerticalAlign()
    {
        return $this->VerticalAlign;
    }

    /**
     * @param string $VerticalAlign
     *
     * @return TableColumn
     */
    public function setVerticalAlign($VerticalAlign)
    {
        $this->VerticalAlign = $VerticalAlign;
        return $this;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->Color;
    }

    /**
     * @param string $Color
     *
     * @return TableColumn
     */
    public function setColor($Color)
    {
        $this->Color = $Color;
        return $this;
    }

    /**
     * @return string
     */
    public function getOpacity()
    {
        return $this->Opacity;
    }

    /**
     * @param string $Opacity
     *
     * @return
     */
    public function setOpacity($Opacity)
    {
        $this->Opacity = $Opacity;
        return $this;
    }

    /**
     * @return string
     */
    public function getMinHeight()
    {
        return $this->MinHeight;
    }

    /**
     * @param string $MinHeight
     *
     * @return TableColumn
     */
    public function setMinHeight($MinHeight)
    {
        $this->MinHeight = $MinHeight;
        return $this;
    }

    /**
     * @return string
     */
    public function getPadding()
    {
        return $this->Padding;
    }

    /**
     * @param string $Padding
     *
     * @return TableColumn
     */
    public function setPadding($Padding)
    {
        $this->Padding = $Padding;
        return $this;
    }
}
