<?php
namespace SPHERE\Common\Window\Navigation\Link;

use SPHERE\Common\Frontend\Icon\IIconInterface;
use SPHERE\System\Extension\Extension;

/**
 * Class Icon
 *
 * @package SPHERE\Common\Window\Navigation\Link
 */
class Icon extends Extension
{

    /** @var IIconInterface $Value */
    private $Value = null;

    /**
     * @param IIconInterface $Value
     */
    public function __construct(IIconInterface $Value)
    {

        $this->Value = $Value;
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->getValue();
    }

    /**
     * @return IIconInterface
     */
    public function getValue()
    {

        return $this->Value;
    }
}
