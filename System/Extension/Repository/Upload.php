<?php
namespace SPHERE\System\Extension\Repository;

use MOC\V\Core\AutoLoader\AutoLoader;
use Upload\Exception\UploadException;
use Upload\File;
use Upload\Storage\FileSystem;
use Upload\Validation\Mimetype;
use Upload\Validation\Size;

/**
 * Class Upload
 *
 * @package SPHERE\System\Extension\Repository
 */
class Upload
{

    private $Storage;
    private $File;
    private $Validation = array();
    private $isMoved = false;
    private $Location;

    /**
     * @param string $Key
     * @param string $Location
     * @param bool   $Overwrite
     */
    public function __construct($Key, $Location, $Overwrite = false)
    {

        AutoLoader::getNamespaceAutoLoader('Upload', __DIR__.'/../../../Library/Upload/src');

        $this->Location = $Location;
        $this->Storage = new FileSystem($this->Location, $Overwrite);
        $this->File = new File($Key, $this->Storage);
    }

    /**
     * Ensure file is of type e.g. "image/png"
     *
     * @param $MimeType
     *
     * @return $this
     */
    public function validateMimeType($MimeType)
    {

        array_push($this->Validation, new Mimetype($MimeType));
        return $this;
    }

    /**
     * Ensure file is no larger than e.g. "5M" (use "B", "K", M", or "G")
     *
     * @param $Size
     *
     * @return $this
     */
    public function validateMaxSize($Size)
    {

        array_push($this->Validation, new Size($Size));
        return $this;
    }

    /**
     * @return string
     */
    public function getMimeType()
    {

        if (!$this->isMoved) {
            return $this->File->getMimetype();
        } else {
            $FileInfo = new \finfo(FILEINFO_MIME);
            $MimeType = $FileInfo->file($this->getLocation().DIRECTORY_SEPARATOR.$this->getFilename());
            $MimeTypeParts = preg_split('/\s*[;,]\s*/', $MimeType);
            return strtolower($MimeTypeParts[0]);
        }
    }

    /**
     * @return string
     */
    public function getLocation()
    {

        return realpath($this->Location);
    }

    /**
     * @return string
     */
    public function getFilename()
    {

        return $this->File->getNameWithExtension();
    }

    /**
     * @return bool|string
     */
    public function getContent()
    {

        if ($this->isMoved) {
            return file_get_contents($this->getLocation().DIRECTORY_SEPARATOR.$this->getFilename());
        } else {
            return file_get_contents($this->File->getRealPath());
        }
    }

    /**
     * @return array
     */
    public function getDimensions()
    {

        if ($this->isMoved) {
            list( $width, $height ) = getimagesize($this->getLocation().DIRECTORY_SEPARATOR.$this->getFilename());
            return array(
                'width'  => $width,
                'height' => $height
            );
        } else {
            return $this->File->getDimensions();
        }
    }

    /**
     * @return int
     */
    public function getSize()
    {

        return $this->File->getSize();
    }

    /**
     * @return string
     */
    public function getName()
    {

        return $this->File->getName();
    }

    /**
     * @return string
     */
    public function getExtension()
    {

        return $this->File->getExtension();
    }

    /**
     * @return Upload
     * @throws \Exception
     */
    public function doUpload()
    {

        $this->isMoved = true;

        $this->setValidator();
        $this->tryUpload();
        return $this;
    }

    private function setValidator()
    {

        $this->File->addValidations($this->Validation);
    }

    /**
     * @throws \Exception
     */
    private function tryUpload()
    {

        try {
            $this->File->upload();
        } catch (UploadException $Exception) {
            throw new \Exception(json_encode($this->File->getErrors()));
        }
    }
}
