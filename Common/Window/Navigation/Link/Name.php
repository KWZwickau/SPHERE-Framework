<?php
namespace SPHERE\Common\Window\Navigation\Link;

use SPHERE\System\Extension\Extension;

/**
 * Class Name
 *
 * @package SPHERE\Common\Window\Navigation\Link
 */
class Name extends Extension
{

    /** @var string $Pattern */
    private $Pattern = '|^[a-z\söäüß\-&]+$|is';
    /** @var string $Value */
    private $Value = '';

    /**
     * @param string $Value
     *
     * @throws \Exception
     */
    public function __construct($Value)
    {

        if (preg_match($this->Pattern, $Value)) {
            $this->Value = $Value;
        } else {
            throw new \Exception(__CLASS__.' > Pattern mismatch: ('.$Value.') ['.$this->Pattern.']');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->getValue();
    }

    /**
     * @return string
     */
    public function getValue()
    {

        return $this->Value;
    }
}
