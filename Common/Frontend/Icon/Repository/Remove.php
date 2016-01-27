<?php
namespace SPHERE\Common\Frontend\Icon\Repository;

use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class RemoveIcon
 *
 * @package SPHERE\Common\Frontend\Icon\Repository
 */
class Remove implements IIconInterface
{

    /** @var string $Value */
    private $Value = 'glyphicons glyphicons-remove-circle';

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

        return '<span class="'.$this->getValue().'" aria-hidden="true"></span>';
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }
}
