<?php
namespace SPHERE\Application\Document\Explorer\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblFile")
 * @Cache(usage="READ_ONLY")
 */
class TblFile extends Element
{

    const ATTR_NAME = 'Name';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="string")
     */
    protected $FileName;
    /**
     * @Column(type="string")
     */
    protected $FileExtension;
    /**
     * @Column(type="blob")
     */
    protected $FileContent;
    /**
     * @Column(type="string")
     */
    protected $FileType;
    /**
     * @Column(type="integer")
     */
    protected $FileSize;

    /**
     * @return string
     */
    public function getName()
    {

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
     * @return string|resource
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

        return $this->FileSize;
    }

    /**
     * @param int $FileSize
     */
    public function setFileSize($FileSize)
    {

        $this->FileSize = $FileSize;
    }
}
