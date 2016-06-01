<?php
namespace MOC\V\Core\FileSystem\Component\Bridge\Repository;

use MOC\V\Core\FileSystem\Component\Bridge\Bridge;
use MOC\V\Core\FileSystem\Component\IBridgeInterface;
use MOC\V\Core\FileSystem\Component\Parameter\Repository\FileParameter;
use MOC\V\Core\FileSystem\Vendor\Universal\Download;
use MOC\V\Core\GlobalsKernel\GlobalsKernel;

/**
 * Class UniversalDownload
 *
 * @package MOC\V\Core\FileSystem\Component\Bridge
 */
class UniversalDownload extends Bridge implements IBridgeInterface
{

    /** @var Download $Instance */
    private $Instance = null;

    /**
     * @param FileParameter $FileLocation
     * @param FileParameter $FileName
     */
    public function __construct(FileParameter $FileLocation, FileParameter $FileName = null)
    {

        parent::__construct();
        $this->Instance = new Download($FileLocation->getFile(), ( $FileName ? $FileName->getFile() : null ));
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

    /**
     * @return string
     */
    public function __toString()
    {

        return (string)$this->Instance;
    }
}
