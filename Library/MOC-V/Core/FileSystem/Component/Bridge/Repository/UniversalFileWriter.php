<?php
namespace MOC\V\Core\FileSystem\Component\Bridge\Repository;

use MOC\V\Core\FileSystem\Component\Bridge\Bridge;
use MOC\V\Core\FileSystem\Component\IBridgeInterface;
use MOC\V\Core\FileSystem\Component\Parameter\Repository\FileParameter;
use MOC\V\Core\FileSystem\Vendor\Universal\FileWriter;

/**
 * Class UniversalFileWriter
 *
 * @package MOC\V\Core\FileSystem\Component\Bridge
 */
class UniversalFileWriter extends Bridge implements IBridgeInterface
{

    /** @var FileWriter $Instance */
    private $Instance = null;

    /**
     * @param FileParameter $FileOption
     */
    function __construct( FileParameter $FileOption )
    {

        $this->Instance = new FileWriter( $FileOption->getFile() );
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
    public function getRealPath()
    {

        $SplFileInfo = ( new \SplFileInfo( $this->Instance->getLocation() ) );
        if (!$SplFileInfo->getRealPath()) {
            $SplFileInfo = ( new \SplFileInfo( $_SERVER['DOCUMENT_ROOT'].$this->Instance->getLocation() ) );
        }
        return $SplFileInfo->getRealPath() ? $SplFileInfo->getRealPath() : '';
    }
}
