<?php
namespace SPHERE\Common\Frontend\Text\Repository;

use SPHERE\Common\Frontend\Text\ITextInterface;

/**
 * Class Info
 *
 * @package SPHERE\Common\Frontend\Text\Repository
 */
class Info implements ITextInterface
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

        return '<span class="text-info">'.$this->getValue().'</span>';
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }
}
