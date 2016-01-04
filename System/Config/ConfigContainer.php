<?php
namespace SPHERE\System\Config;

/**
 * Class ConfigContainer
 *
 * @package SPHERE\System\Config
 */
class ConfigContainer implements ConfigInterface
{

    /** @var null|ConfigContainer|mixed $Value */
    private $Value = null;

    /**
     * ConfigContainer constructor.
     *
     * @param string|array $Content
     */
    public function __construct($Content)
    {

        if (is_array($Content)) {
            array_walk($Content, function (&$Value) {

                $Value = new ConfigContainer($Value);
            });
        }
        $this->Value = $Content;
    }

    /**
     * @param string $Key
     *
     * @return null|ConfigContainer|mixed
     */
    public function getContainer($Key)
    {

        if (isset( $this->Value[$Key] )) {
            return $this->Value[$Key];
        }
        return null;
    }

    /**
     * @param string                     $Key
     * @param null|ConfigContainer|mixed $Value
     *
     * @return ConfigContainer
     */
    public function setContainer($Key, $Value)
    {

        if ($Value instanceof ConfigContainer) {
            $this->Value[$Key] = $Value;
        } else {
            $this->Value[$Key] = new ConfigContainer($Value);
        }
        return $this;
    }

    /**
     * @return mixed|ConfigContainer
     */
    public function getValue()
    {

        return $this->Value;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function __toString()
    {

        if (is_array($this->Value) || $this->Value instanceof ConfigContainer) {
            return json_encode($this->Value);
        } else {
            return $this->Value;
        }
    }
}
