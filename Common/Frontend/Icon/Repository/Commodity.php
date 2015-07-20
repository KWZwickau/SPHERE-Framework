<?php
namespace SPHERE\Common\Frontend\Icon\Repository;

use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class Commodity
 *
 * @package SPHERE\Common\Frontend\Icon\Repository
 */
class Commodity implements IIconInterface
{

    /** @var string $Value */
    private $Value = 'glyphicon glyphicon-inbox';

    /**
     * @return string
     */
    function __toString()
    {

        return $this->getContent();
    }

    /**
     * @return string
     */
    public function getContent()
    {

        return '<span class="'.$this->getValue().'"></span>';
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }
}
