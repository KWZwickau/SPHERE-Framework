<?php
namespace MOC\V\Component\Document\Component\Bridge\Repository;

use MOC\V\Component\Document\Component\Bridge\Bridge;
use MOC\V\Component\Document\Component\Exception\ComponentException;
use MOC\V\Component\Document\Component\IBridgeInterface;
use MOC\V\Component\Document\Component\Parameter\Repository\FileParameter;
use MOC\V\Component\Document\Vendor\UniversalXml\Api;

/**
 * Class UniversalXml
 *
 * @package MOC\V\Component\Document\Component\Bridge\Repository
 */
class UniversalXml extends Bridge implements IBridgeInterface
{

    /**
     * @param FileParameter $Location
     *
     * @return IBridgeInterface
     */
    public function loadFile(FileParameter $Location)
    {

        $this->setFileParameter($Location);
        return $this;
    }

    /**
     * @param null|FileParameter $Location
     *
     * @return IBridgeInterface
     * @throws ComponentException
     */
    public function saveFile(FileParameter $Location = null)
    {

        // TODO: Implement saveFile() method.
        throw new ComponentException('saveFile() method not implemented');
    }

    /**
     * @return string
     */
    public function getContent()
    {

        $Parser = new Api(file_get_contents($this->getFileParameter()->getFile()));

        return $Parser->parseContent();
    }
}
