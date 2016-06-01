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
     * @return null|false|string returns null if not detected, false on error (enable the php_fileinfo extension)
     */
    public function getMimeType();

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
