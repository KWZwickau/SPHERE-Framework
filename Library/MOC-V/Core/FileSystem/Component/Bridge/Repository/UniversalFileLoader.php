<?php
namespace MOC\V\Core\FileSystem\Component\Bridge\Repository;

use MOC\V\Core\FileSystem\Component\Bridge\Bridge;
use MOC\V\Core\FileSystem\Component\IBridgeInterface;
use MOC\V\Core\FileSystem\Component\Parameter\Repository\FileParameter;
use MOC\V\Core\FileSystem\Vendor\Universal\FileLoader;
use MOC\V\Core\GlobalsKernel\GlobalsKernel;

/**
 * Class UniversalFileLoader
 *
 * @package MOC\V\Core\FileSystem\Component\Bridge
 */
class UniversalFileLoader extends Bridge implements IBridgeInterface
{

    /** @var FileLoader $Instance */
    private $Instance = null;

    /**
     * @param FileParameter $FileOption
     */
    public function __construct(FileParameter $FileOption)
    {

        parent::__construct();
        $this->Instance = new FileLoader($FileOption->getFile());
    }

    /**
     * @return string
     */
    public function __toString()
    {

        if ($this->getRealPath()) {
            return (string)file_get_contents($this->getRealPath());
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getRealPath()
    {

        $SERVER = GlobalsKernel::getGlobals()->getSERVER();
        $SplFileInfo = (new \SplFileInfo($this->Instance->getLocation()));
        if (!$SplFileInfo->getRealPath()) {
            $SplFileInfo = (new \SplFileInfo($SERVER['DOCUMENT_ROOT'].$this->Instance->getLocation()));
        }
        return $SplFileInfo->getRealPath() ? $SplFileInfo->getRealPath() : '';
    }

    /**
     * @return string
     */
    public function getLocation()
    {

        return $this->Instance->getLocation();
    }
}
