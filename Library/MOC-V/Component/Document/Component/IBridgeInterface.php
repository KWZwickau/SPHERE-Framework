<?php
namespace MOC\V\Component\Document\Component;

use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;

/**
 * Interface IBridgeInterface
 *
 * @package MOC\V\Component\Document\Component
 */
interface IBridgeInterface
{

    /**
     * @param FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function loadFile(FileParameter $Location);

    /**
     * @param null|FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function saveFile(FileParameter $Location = null);
}
