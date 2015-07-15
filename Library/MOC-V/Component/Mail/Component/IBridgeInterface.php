<?php
namespace MOC\V\Component\Mail\Component;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Component\Mail\Component
 */
interface IBridgeInterface
{

    /**
     * @param string   $Host
     * @param string   $Username
     * @param string   $Password
     * @param null|int $Port
     * @param bool     $useSSL
     * @param bool     $useTLS
     *
     * @return IBridgeInterface
     */
    public function connectServer( $Host, $Username, $Password, $Port = null, $useSSL = false, $useTLS = false );

    /**
     * @return IBridgeInterface
     */
    public function disconnectServer();

}
