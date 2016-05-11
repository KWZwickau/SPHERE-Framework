<?php
namespace SPHERE\Application\Document\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Document\Storage\Storage;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity
 * @Table(name="tblFileType")
 * @Cache(usage="READ_ONLY")
 */
class TblFileType extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_EXTENSION = 'Extension';
    const ATTR_MIME_TYPE = 'MimeType';

    /**
     * @Column(type="string")
     */
    protected $tblFileCategory;

    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Extension;
    /**
     * @Column(type="string")
     */
    protected $MimeType;

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
     * @return string
     */
    public function getMimeType()
    {

        return $this->MimeType;
    }

    /**
     * @param string $MimeType
     */
    public function setMimeType($MimeType)
    {

        $this->MimeType = $MimeType;
    }

    /**
     * @return bool|TblFileCategory
     */
    public function getTblFileCategory()
    {

        if (null === $this->tblFileCategory) {
            return false;
        } else {
            return Storage::useService()->getFileCategoryById($this->tblFileCategory);
        }
    }

    /**
     * @param null|TblFileCategory $tblFileCategory
     */
    public function setTblFileCategory(TblFileCategory $tblFileCategory = null)
    {

        $this->tblFileCategory = ( null === $tblFileCategory ? null : $tblFileCategory->getId() );
    }
}
