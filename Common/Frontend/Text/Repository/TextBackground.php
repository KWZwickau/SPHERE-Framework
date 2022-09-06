<?php
namespace SPHERE\Common\Frontend\Text\Repository;

use SPHERE\Common\Frontend\Text\ITextInterface;

/**
 * Class TextBackground
 *
 * @package SPHERE\Common\Frontend\Text\Repository
 */
class TextBackground implements ITextInterface
{

    const BG_COLOR_SUCCESS = '#a0ffa0';
    const BG_COLOR_INFO = '#c7f1ff';
    const BG_COLOR_WARNING = '#fdea9c';
    const BG_COLOR_DANGER = '#ffe5d0';

    /** @var string $Value */
    private $Value = '';
    /** @var string $BackgroundColor */
    private $BackgroundColor = '';

    /**
     * @param string $Value
     * @param string $BackgroundColor
     */
    public function __construct($Value = '', $BackgroundColor = TextBackground::BG_COLOR_SUCCESS)
    {

        $this->Value = $Value;
        $this->BackgroundColor = $BackgroundColor;
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

        $Style = 'style="padding: 1px 1px;';
        if($this->BackgroundColor){
            $Style .= 'background-color: '.$this->BackgroundColor;
        }
        $Style .= ';"';

        return '<span '.$Style.'>'.$this->Value.'</span>';
    }
}
