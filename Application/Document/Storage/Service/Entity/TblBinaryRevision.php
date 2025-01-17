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
 * @Table(name="tblBinaryRevision")
 * @Cache(usage="READ_ONLY")
 */
class TblBinaryRevision extends Element
{
    const ATTR_TBL_FILE = 'tblFile';
    const ATTR_VERSION = 'Version';

    /**
     * @Column(type="bigint")
     */
    protected $tblBinary;

    /**
     * @Column(type="bigint")
     */
    protected $tblFile;

    /**
     * @Column(type="integer")
     */
    protected int $Version;

    /**
     * @Column(type="string")
     */
    protected $Description;

    /**
     * @return bool|TblBinary
     */
    public function getTblBinary()
    {
        return Storage::useService()->getBinaryById($this->tblBinary);
    }

    /**
     * @param TblBinary $tblBinary
     */
    public function setTblBinary(TblBinary $tblBinary)
    {
        $this->tblBinary = $tblBinary->getId();
    }

    /**
     * @return bool|TblFile
     */
    public function getTblFile()
    {
        return Storage::useService()->getFileById($this->tblFile);
    }

    /**
     * @param TblFile $tblFile
     */
    public function setTblFile(TblFile $tblFile)
    {
        $this->tblFile = $tblFile->getId();
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->Version;
    }

    /**
     * @param int $Version
     *
     * @return void
     */
    public function setVersion(int $Version): void
    {
        $this->Version = $Version;
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
    public function setDescription($Description): void
    {
        $this->Description = $Description;
    }
}