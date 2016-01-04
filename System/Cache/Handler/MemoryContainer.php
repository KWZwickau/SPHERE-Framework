<?php
namespace SPHERE\System\Cache\Handler;

/**
 * Class MemoryContainer
 *
 * @package SPHERE\System\Cache\Handler
 */
class MemoryContainer
{

    /** @var int $TimeStamp */
    private $TimeStamp = 0;
    /** @var int $TimeOut */
    private $TimeOut = 0;
    /** @var mixed $Value */
    private $Value = null;

    /**
     * MemoryContainer constructor.
     *
     * @param $Value
     * @param $Timeout
     */
    public function __construct($Value, $Timeout = 0)
    {

        $this->TimeStamp = time();
        $this->Value = $Value;
        $this->TimeOut = $Timeout;
    }

    /**
     * @return bool
     */
    public function isValid()
    {

        if ($this->TimeOut === 0 || ( $this->TimeStamp + $this->TimeOut ) > time()) {
            return true;
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {

        if (is_object($this->Value)) {
            return clone $this->Value;
        } else {
            return $this->Value;
        }
    }
}
