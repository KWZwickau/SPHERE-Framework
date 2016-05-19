<?php
namespace MOC\V\Core\FileSystem\Component\Bridge;

use MOC\V\Core\AutoLoader\AutoLoader;
use MOC\V\Core\FileSystem\Component\IBridgeInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * Class Bridge
 *
 * @package MOC\V\Core\FileSystem\Component\Bridge
 */
abstract class Bridge implements IBridgeInterface
{

    /**
     * Bridge constructor.
     */
    public function __construct()
    {

        AutoLoader::getNamespaceAutoLoader(
            'Symfony\Component\HttpFoundation\File', __DIR__.'/../../../HttpKernel/Vendor/'
        );
    }

    /**
     * @return null|false|string returns null if not detected, false on error (enable the php_fileinfo extension)
     */
    public function getMimeType()
    {

        try {
            return MimeTypeGuesser::getInstance()->guess($this->getRealPath());
        } catch (\Exception $Exception) {
            return false;
        }
    }
}
