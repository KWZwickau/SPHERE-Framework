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

    const ATTR_IMG_DATA = 'ImgData';
    const ATTR_IMG_TYPE = 'ImgType';

    /**
     * @Column(type="blob")
     */
    protected $ImgData;
    /**
     * @Column(type="string")
     */
    protected $ImgType;

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


}
