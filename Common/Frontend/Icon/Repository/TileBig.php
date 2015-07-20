<?php
namespace SPHERE\Common\Frontend\Icon\Repository;

use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class TileBigIcon
 *
 * @package SPHERE\Common\Frontend\Icon\Repository
 */
class TileBig implements IIconInterface
{

    /** @var string $Value */
    private $Value = 'halflings halflings-barcode';

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
