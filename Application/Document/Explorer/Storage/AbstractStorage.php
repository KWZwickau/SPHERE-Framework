<?php
namespace SPHERE\Application\Document\Explorer\Storage;

use MOC\V\Core\GlobalsKernel\GlobalsKernel;

/**
 * Class AbstractStorage
 *
 * @package SPHERE\Application\Document\Explorer\Storage
 */
abstract class AbstractStorage
{

    /** @var string $Name */
    private $Name = '';
    /** @var string $Description */
    private $Description = '';
    /** @var string $FileLocation */
    private $FileLocation = '';
    /** @var string $FilePath */
    private $FilePath = '';
    /** @var string $FileName */
    private $FileName = '';
    /** @var string $FileExtension */
    private $FileExtension = '';
    /** @var string $FileContent */
    private $FileContent = '';
    /** @var string $FileType */
    private $FileType = '';
    /** @var int $FileSize */
    private $FileSize = 0;

    /**
     * @return string
     */
    public function getName()
    {

        if (empty( $this->Name )) {
            $this->Name = preg_replace('![^0-9a-z\s]!is', '', $this->FileName);
        }
        return $this->Name;
    }

    /**
     * @param string $Name
     */
    public function setName($Name)
    {

        $this->Name = $Name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {

        return $this->Description;
    }

    /**
     * @param string $Description
     */
    public function setDescription($Description)
    {

        $this->Description = $Description;
    }

    /**
     * @return string
     */
    public function getFileName()
    {

        if (empty( $this->FileName )) {
            $this->FileName = preg_replace('![^0-9a-z]!is', '', $this->Name);
        }
        return $this->FileName;
    }

    /**
     * @param string $FileName
     */
    public function setFileName($FileName)
    {

        $this->FileName = $FileName;
    }

    /**
     * @return string
     */
    public function getFileExtension()
    {

        return $this->FileExtension;
    }

    /**
     * @param string $FileExtension
     */
    public function setFileExtension($FileExtension)
    {

        $this->FileExtension = $FileExtension;
    }

    /**
     * @return string
     */
    public function getFileContent()
    {

        return $this->FileContent;
    }

    /**
     * @param string $FileContent
     */
    public function setFileContent($FileContent)
    {

        $this->FileContent = $FileContent;
    }

    /**
     * @return string
     */
    public function getFileType()
    {

        return $this->FileType;
    }

    /**
     * @param string $FileType
     */
    public function setFileType($FileType)
    {

        $this->FileType = $FileType;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {

        if (empty( $this->FileSize )) {
            $this->setFileSize(filesize($this->getRealPath()));
        }
        return $this->FileSize;
    }

    /**
     * @param int $FileSize
     */
    public function setFileSize($FileSize)
    {

        $this->FileSize = $FileSize;
    }

    /**
     * @return string
     */
    public function getRealPath()
    {

        $SERVER = GlobalsKernel::getGlobals()->getSERVER();
        $SplFileInfo = (new \SplFileInfo($this->getFileLocation()));
        if (!$SplFileInfo->getRealPath()) {
            $SplFileInfo = (new \SplFileInfo($SERVER['DOCUMENT_ROOT'].$this->getFileLocation()));
        }
        return $SplFileInfo->getRealPath() ? $SplFileInfo->getRealPath() : '';
    }

    /**
     * @return string
     */
    public function getFileLocation()
    {

        return $this->FileLocation;
    }

    /**
     * @param string $FileLocation
     */
    public function setFileLocation($FileLocation)
    {

        $this->FileLocation = $FileLocation;
    }

    /**
     * @return string
     */
    public function getFilePath()
    {

        if (empty( $this->FilePath )) {
            $this->setFilePath(dirname($this->getFileLocation()));
        }
        return $this->FilePath;
    }

    /**
     * @param string $FilePath
     */
    public function setFilePath($FilePath)
    {

        $this->FilePath = $FilePath;
    }
}
