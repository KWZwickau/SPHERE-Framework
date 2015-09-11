<?php
namespace SPHERE\Common\Frontend\Text\Repository;

use SPHERE\Common\Frontend\Text\ITextInterface;

/**
 * Class Strikethrough
 *
 * @package SPHERE\Common\Frontend\Text\Repository
 */
class Strikethrough implements ITextInterface
{

    /** @var string $Value */
    private $Value = '';

    /**
     * @param $Value
     */
    public function __construct($Value)
    {

        $this->Value = $Value;
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

        return '<s>'.$this->getValue().'</s>';
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }
}
