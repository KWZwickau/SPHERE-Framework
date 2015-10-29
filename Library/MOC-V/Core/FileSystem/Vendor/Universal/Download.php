<?php
namespace MOC\V\Core\FileSystem\Vendor\Universal;

use MOC\V\Core\FileSystem\FileSystem;

/**
 * Class Download
 *
 * @package MOC\V\Core\FileSystem\Vendor\Universal
 */
class Download
{

    /** @var \MOC\V\Core\FileSystem\Component\IBridgeInterface $Location */
    private $Location = null;
    /** @var null|string $Name */
    private $Name = null;

    /**
     * @param string      $Location
     * @param null|string $Name
     */
    public function __construct($Location, $Name = null)
    {

        $this->setLocation($Location);
        $this->Name = $Name;
    }

    /**
     * @return bool|string
     */
    public function __toString()
    {

        if ($this->getRealPath()) {
            if (function_exists('mime_content_type')) {
                $Type = mime_content_type($this->getRealPath());
            } else {
                if (function_exists('finfo_file')) {
                    $Handler = finfo_open(FILEINFO_MIME);
                    $Type = finfo_file($Handler, $this->getRealPath());
                    finfo_close($Handler);
                } else {
                    $Type = "application/force-download";
                }
            }

            // Set headers.
            header('Content-Description: Download');
            header('Content-Type: '.$Type);
            header('Content-Disposition: attachment; filename="'.( $this->Name ? $this->Name : basename($this->getRealPath()) ).'"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: public, must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: '.filesize($this->getRealPath()));
            header('Connection: close');

            // Erase and flush the output buffer
            if (ob_get_level()) {
                ob_clean();
                flush();
            }

            return file_get_contents($this->getRealPath());
        } else {
            // Set headers.
            header('HTTP/1.0 404 Not Found');

            // Erase and flush the output buffer
            if (ob_get_level()) {
                ob_clean();
                flush();
            }

            return '';
        }
    }

    /**
     * @return string
     */
    public function getRealPath()
    {

        return $this->Location->getRealPath();
    }

    /**
     * @return string
     */
    public function getLocation()
    {

        return $this->Location->getLocation();
    }

    /**
     * @param string $Location
     */
    public function setLocation($Location)
    {

        $this->Location = FileSystem::getFileLoader($Location);
    }
}

