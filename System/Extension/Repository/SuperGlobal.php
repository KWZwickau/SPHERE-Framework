<?php
namespace SPHERE\System\Extension\Repository;

/**
 * Class SuperGlobal
 *
 * @package SPHERE\System\Extension\Repository
 */
class SuperGlobal
{

    public $GET;
    public $POST;
    public $REQUEST;
    public $SESSION;
    public $FILES;

    /**
     * @param $GET
     * @param $POST
     * @param $REQUEST
     * @param $SESSION
     * @param $FILES
     */
    public function __construct($GET, $POST, $REQUEST, $SESSION, $FILES)
    {

        $this->GET = $GET;
        $this->POST = $POST;
        $this->REQUEST = $REQUEST;
        $this->SESSION = $SESSION;
        $this->FILES = $FILES;
    }

    public function saveGet()
    {

        $_GET = $this->GET;
    }

    public function savePost()
    {

        $_POST = $this->POST;
    }

    public function saveSession()
    {

        $_SESSION = $this->SESSION;
    }

    public function saveRequest()
    {

        $_REQUEST = $this->REQUEST;
    }
}
