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

    /**
     * @param $Key
     * @param $Location
     */
    function __construct($Key, $Location)
    {

        AutoLoader::getNamespaceAutoLoader('Upload', __DIR__.'/../../../Library/Upload/src');

        $this->Storage = new FileSystem($Location);
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

    public function doUpload()
    {

        $this->setValidator();
        $this->tryUpload();
        return $this;
    }

    private function setValidator()
    {

        $this->File->addValidations($this->Validation);
    }

    private function tryUpload()
    {

        try {
            $this->File->upload();
        } catch (UploadException $Exception) {
            throw new \Exception(print_r($this->File->getErrors(), true));
        }
    }
}
