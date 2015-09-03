<?php
namespace MOC\V\Component\Template\Component;

use MOC\V\Component\Template\Component\Parameter\Repository\FileParameter;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Component\Template\Component
 */
interface IBridgeInterface
{

    /**
     * @param FileParameter $Location
     * @param bool          $Reload
     *
     * @return IBridgeInterface
     */
    public function loadFile(FileParameter $Location, $Reload = false);

    /**
     * @param string $Identifier
     * @param mixed  $Value
     *
     * @return IBridgeInterface
     */
    public function setVariable($Identifier, $Value);

    /**
     * @return string
     */
    public function getContent();
}
