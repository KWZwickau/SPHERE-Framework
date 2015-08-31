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
    public $SESSION;

    /**
     * @param $GET
     * @param $POST
     * @param $SESSION
     */
    public function __construct($GET, $POST, $SESSION)
    {

        $this->GET = $GET;
        $this->POST = $POST;
        $this->SESSION = $SESSION;
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
}
