<?php
namespace SPHERE\Application\Document\Explorer\Storage\Service\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use SPHERE\Application\Document\Explorer\Storage\Storage;
use SPHERE\System\Database\Fitting\Element;

/**
 * @Entity()
 * @Table(name="tblFile")
 * @Cache(usage="READ_ONLY")
 */
class TblDirectory extends Element
{

    const ATTR_NAME = 'Name';
    const ATTR_TBL_DIRECTORY = 'tblDirectory';
    const ATTR_IS_LOCKED = 'IsLocked';

    /**
     * @Column(type="bigint")
     */
    protected $tblDirectory;
    /**
     * @Column(type="string")
     */
    protected $Name;
    /**
     * @Column(type="string")
     */
    protected $Description;
    /**
     * @Column(type="boolean")
     */
    protected $IsLocked;

    /**
     * @return bool|TblDirectory
     */
    public function getTblGroup()
    {

        if (null === $this->tblDirectory) {
            return false;
        } else {
            return Storage::useService()->getDirectoryById($this->tblDirectory);
        }
    }

    /**
     * @param null|TblDirectory $tblDirectory
     */
    public function setTblGroup(TblDirectory $tblDirectory = null)
    {

        $this->tblDirectory = ( null === $tblDirectory ? null : $tblDirectory->getId() );
    }

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
     * @return bool
     */
    public function getIsLocked()
    {

        return (bool)$this->IsLocked;
    }

    /**
     * @param bool $IsLocked
     */
    public function setIsLocked($IsLocked)
    {

        $this->IsLocked = (bool)$IsLocked;
    }
}
