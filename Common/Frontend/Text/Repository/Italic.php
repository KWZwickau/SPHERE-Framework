<?php
namespace SPHERE\Common\Frontend\Text\Repository;

use SPHERE\Common\Frontend\Text\ITextInterface;

/**
 * Class Italic
 *
 * @package SPHERE\Common\Frontend\Text\Repository
 */
class Italic implements ITextInterface
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

        return '<em>'.$this->getValue().'</em>';
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }
}
