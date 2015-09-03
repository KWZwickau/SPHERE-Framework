<?php
namespace MOC\V\Core\SecureKernel\Component;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Core\SecureKernel\Component
 */
interface IBridgeInterface
{

    /**
     * @param string $Host
     * @param int    $Port
     * @param int    $Timeout
     *
     * @return IBridgeInterface
     */
    public function openConnection($Host, $Port = 22, $Timeout = 10);

    /**
     * @return IBridgeInterface
     */
    public function closeConnection();
}
