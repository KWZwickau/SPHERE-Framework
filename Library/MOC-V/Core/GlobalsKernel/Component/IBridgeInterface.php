<?php
namespace MOC\V\Core\GlobalsKernel\Component;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Core\GlobalsKernel\Component
 */
interface IBridgeInterface
{

    /**
     * @return array
     */
    public function getGET();

    /**
     * @param array $GET
     */
    public function setGET($GET);

    /**
     * @return array
     */
    public function getPOST();

    /**
     * @param array $POST
     */
    public function setPOST($POST);

    /**
     * @return array
     */
    public function getSESSION();

    /**
     * @param array $SESSION
     */
    public function setSESSION($SESSION);

    /**
     * @return array
     */
    public function getSERVER();

    /**
     * @param array $SERVER
     */
    public function setSERVER($SERVER);
}
