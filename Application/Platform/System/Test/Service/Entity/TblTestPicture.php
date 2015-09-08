<?php
namespace SPHERE\Application\Platform\System\Test\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblTestPicture")
 * @Cache(usage="NONSTRICT_READ_WRITE")
 */
class TblTestPicture extends Element
{

    const ATTR_IMG_NAME = 'Name';
    const ATTR_IMG_FILE_NAME = 'FileName';
    const ATTR_IMG_EXTENSION = 'Extension';

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $FileName;
    /**
     * @Column(type="string")
     */
    protected $Extension;
    /**
     * @Column(type="blob")
     */
    protected $ImgData;
    /**
     * @Column(type="string")
     */
    protected $ImgType;
    /**
     * @Column(type="integer")
     */
    protected $Size;
    /**
     * @Column(type="integer")
     */
    protected $Width;
    /**
     * @Column(type="integer")
     */
    protected $Height;

    /**
     * @return string $Name
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
     * @return string $FileName
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
     * @return string $Extension
     */
    public function getExtension()
    {

        return $this->Extension;
    }

    /**
     * @param string $Extension
     */
    public function setExtension($Extension)
    {

        $this->Extension = $Extension;
    }

    /**
     * @return resource $ImgData
     */
    public function getImgData()
    {

        return $this->ImgData;
    }

    /**
     * @param resource $ImgData
     */
    public function setImgData($ImgData)
    {

        $this->ImgData = $ImgData;
    }

    /**
     * @return string $ImgType
     */
    public function getImgType()
    {

        return $this->ImgData;
    }

    /**
     * @param string $ImgType
     */
    public function setImgType($ImgType)
    {

        $this->ImgType = $ImgType;
    }

    /**
     * @return string $Size
     */
    public function getSize()
    {

        return $this->Size;
    }

    /**
     * @param string $Size
     */
    public function setSize($Size)
    {

        $this->Size = $Size;
    }

    /**
     * @return string $Width
     */
    public function getWidth()
    {

        return $this->Width;
    }

    /**
     * @param string $Width
     */
    public function setWidth($Width)
    {

        $this->Width = $Width;
    }

    /**
     * @return string $Height
     */
    public function getHeight()
    {

        return $this->Height;
    }

    /**
     * @param string $Height
     */
    public function setHeight($Height)
    {

        $this->Height = $Height;
    }


}
