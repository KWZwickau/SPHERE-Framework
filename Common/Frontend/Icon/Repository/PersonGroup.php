<?php
namespace SPHERE\Common\Frontend\Icon\Repository;

use SPHERE\Common\Frontend\Icon\IIconInterface;

/**
 * Class PersonGroup
 *
 * @package SPHERE\Common\Frontend\Icon\Repository
 */
class PersonGroup implements IIconInterface
{

    /** @var string $Value */
    private $Value = 'glyphicons glyphicons-group';

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

        return '<span class="'.$this->getValue().'" aria-hidden="true"></span>&nbsp;';
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }
}
