<?php
namespace SPHERE\Application\Document\Explorer\Storage\Writer\Type;

use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\Application\Document\Explorer\Storage\Writer\AbstractWriter;

/**
 * Class Database
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Writer\Type
 */
class Database extends AbstractWriter
{

    /** @var false|TblFile $tblFile */
    private $tblFile = false;
    /** @var null|Temporary $Container */
    private $Container = null;

    /**
     * @param null|int $Id
     */
    public function __construct($Id = null)
    {

        if ($Id) {
            $this->tblFile = Storage::useService()->getFileById($Id);
        }
        $this->loadFile();
    }

    /**
     *
     */
    public function loadFile()
    {

        if ($this->tblFile) {
            $this->setName($this->tblFile->getName());
            $this->setDescription($this->tblFile->getDescription());
            $this->setFileName($this->tblFile->getFileName());
            $this->setFileSize($this->tblFile->getFileSize());
            $this->setFileType($this->tblFile->getFileType());
            $this->setFileExtension($this->tblFile->getFileExtension());
            $this->setFileContent($this->tblFile->getFileContent());

            $this->Container = new Temporary('SPHERE-Database', $this->getFileExtension());
            $this->Container->setName($this->getName());
            $this->Container->setDescription($this->getDescription());
            $this->Container->setFileName($this->getFileName());
            $this->Container->setFileSize($this->getFileSize());
            $this->Container->setFileType($this->getFileType());
            $this->Container->setFileExtension($this->getFileExtension());
            $this->Container->setFileContent($this->getFileContent());
        } else {
            $this->Container = new Temporary();
        }
    }

    /**
     *
     */
    public function saveFile()
    {

        // TODO: Save & Update

        if ($this->tblFile) {

        } else {
            $Entity = Storage::useService()->insertFile(
                $this->getName(),
                $this->getDescription(),
                $this->getFileName(),
                $this->getFileExtension(),
                $this->getFileContent(),
                $this->getFileType(),
                $this->getFileSize()
            );
        }
    }

    /**
     * @return null|Temporary
     */
    public function getFile()
    {

        return $this->Container;
    }
}
