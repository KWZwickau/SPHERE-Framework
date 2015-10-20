<?php
namespace MOC\V\Core\FileSystem\Component;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Core\FileSystem\Component
 */
interface IBridgeInterface
{

    /**
     * @return string
     */
    public function getLocation();

    /**
     * @return string
     */
    public function getRealPath();

    /**
     * @return string
     */
    public function __toString();
}
