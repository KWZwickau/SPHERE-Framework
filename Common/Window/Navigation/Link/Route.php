<?php
namespace SPHERE\Common\Window\Navigation\Link;

use SPHERE\System\Extension\Configuration;

/**
 * Class Route
 *
 * @package SPHERE\Common\Window\Navigation\Link
 */
class Route extends Configuration
{

    /** @var string $Pattern */
    private $Pattern = '|^[a-z/]+$|is';
    /** @var string $Value */
    private $Value = '';

    public function __construct( $Value )
    {

        if (preg_match( $this->Pattern, $Value )) {
            $this->Value = $this->getRequest()->getUrlBase().$Value;
        } else {
            throw new \Exception( __CLASS__.' > Pattern mismatch: ('.$Value.') ['.$this->Pattern.']' );
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
