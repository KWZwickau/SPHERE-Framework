<?php
namespace MOC\V\Core\GlobalsKernel\Vendor\Universal;

/**
 * Class Globals
 *
 * @package MOC\V\Core\GlobalsKernel\Vendor\Universal
 */
class Globals
{

    /** @var array $GET */
    private $GET;
    /** @var array $POST */
    private $POST;
    /** @var array $SESSION */
    private $SESSION;
    /** @var array $SERVER */
    private $SERVER;

    /**
     * @param array $GET
     * @param array $POST
     * @param array $SESSION
     * @param array $SERVER
     */
    public function __construct($GET, $POST, $SESSION, $SERVER)
    {

        $this->GET = $GET;
        $this->POST = $POST;
        $this->SESSION = $SESSION;
        $this->SERVER = $SERVER;
    }

    /**
     * @return array
     */
    public function getGET()
    {

        return $this->GET;
    }

    /**
     * @param array $GET
     *
     * @return Globals
     */
    public function setGET($GET)
    {

        $this->GET = $GET;
        $_GET = $this->GET;
        return $this;
    }

    /**
     * @return array
     */
    public function getPOST()
    {

        return $this->POST;
    }

    /**
     * @param array $POST
     *
     * @return Globals
     */
    public function setPOST($POST)
    {

        $this->POST = $POST;
        $_POST = $this->POST;
        return $this;
    }

    /**
     * @return array
     */
    public function getSESSION()
    {

        return $this->SESSION;
    }

    /**
     * @param array $SESSION
     *
     * @return Globals
     */
    public function setSESSION($SESSION)
    {

        $this->SESSION = $SESSION;
        $_SESSION = $this->SESSION;
        return $this;
    }

    /**
     * @return array
     */
    public function getSERVER()
    {

        return $this->SERVER;
    }

    /**
     * @param array $SERVER
     *
     * @return Globals
     */
    public function setSERVER($SERVER)
    {

        $this->SERVER = $SERVER;
        $_SERVER = $this->SERVER;
        return $this;
    }
}
