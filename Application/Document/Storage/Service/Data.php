<?php
namespace SPHERE\Application\Document\Storage\Service;

use SPHERE\Application\Document\Storage\Service\Entity\TblBinary;
use SPHERE\Application\Document\Storage\Service\Entity\TblDirectory;
use SPHERE\Application\Document\Storage\Service\Entity\TblFile;
use SPHERE\Application\Document\Storage\Service\Entity\TblPartition;
use SPHERE\Application\Platform\System\Protocol\Protocol;
use SPHERE\System\Database\Binding\AbstractData;

/**
 * Class Data
 *
 * @package SPHERE\Application\Document\Explorer\Storage\Service
 */
class Data extends AbstractData
{

    /**
     * @return void
     */
    public function setupDatabaseContent()
    {

        $this->createPartition('Zeugnisse', 'revisionssichere Archivierung', true,
            TblPartition::IDENTIFIER_CERTIFICATE_STORAGE);
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param bool   $IsLocked
     * @param string $Identifier
     *
     * @return TblPartition
     */
    public function createPartition($Name, $Description = '', $IsLocked = false, $Identifier = '')
    {

        $Manager = $this->getConnection()->getEntityManager();

        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblPartition')->findOneBy(array(
                TblPartition::ATTR_IS_LOCKED  => $IsLocked,
                TblPartition::ATTR_IDENTIFIER => $Identifier,
                TblPartition::ENTITY_REMOVE   => null
            ));
        } else {
            $Entity = $Manager->getEntity('TblPartition')->findOneBy(array(
                TblPartition::ATTR_NAME     => $Name,
                TblPartition::ENTITY_REMOVE => null
            ));
        }

        if (null === $Entity) {
            $Entity = new TblPartition();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblPartition $tblPartition
     * @param string       $Name
     * @param string       $Description
     * @param TblDirectory $tblDirectory
     * @param bool         $IsLocked
     * @param string       $Identifier
     *
     * @return TblDirectory
     */
    public function createDirectory(
        TblPartition $tblPartition,
        $Name,
        $Description,
        TblDirectory $tblDirectory = null,
        $IsLocked = false,
        $Identifier = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        if ($IsLocked) {
            $Entity = $Manager->getEntity('TblDirectory')->findOneBy(array(
                TblDirectory::ATTR_IS_LOCKED     => $IsLocked,
                TblDirectory::ATTR_IDENTIFIER    => strtoupper($Identifier),
                TblDirectory::ATTR_TBL_PARTITION => $tblPartition->getId(),
                TblDirectory::ATTR_TBL_DIRECTORY => ( $tblDirectory ? $tblDirectory->getId() : null ),
                TblDirectory::ENTITY_REMOVE      => null
            ));
        } else {
            $Entity = $Manager->getEntity('TblDirectory')->findOneBy(array(
                TblDirectory::ATTR_NAME          => $Name,
                TblDirectory::ATTR_TBL_PARTITION => $tblPartition->getId(),
                TblDirectory::ATTR_TBL_DIRECTORY => ( $tblDirectory ? $tblDirectory->getId() : null ),
                TblDirectory::ENTITY_REMOVE      => null
            ));
        }

        if (null === $Entity) {
            $Entity = new TblDirectory();
            $Entity->setTblPartition($tblPartition);
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setTblDirectory($tblDirectory);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier(strtoupper($Identifier));
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param int $Id
     *
     * @return false|TblPartition
     */
    public function getPartitionById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPartition', $Id);
    }

    /**
     * @param string $Identifier
     *
     * @return false|TblPartition
     */
    public function getPartitionByIdentifier($Identifier)
    {

        return $this->getCachedEntityBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPartition', array(
            TblPartition::ATTR_IDENTIFIER => strtoupper($Identifier)
        ));
    }

    /**
     * @return false|TblPartition[]
     */
    public function getPartitionAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblPartition');
    }

    /**
     * @param int $Id
     *
     * @return false|TblBinary
     */
    public function getBinaryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBinary', $Id);
    }

    /**
     * @return false|TblBinary[]
     */
    public function getBinaryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblBinary');
    }

    /**
     * @param int $Id
     *
     * @return false|TblFile
     */
    public function getFileById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile', $Id);
    }

    /**
     * @return false|TblFile[]
     */
    public function getFileAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblFile');
    }

    /**
     * @param int $Id
     *
     * @return false|TblDirectory
     */
    public function getDirectoryById($Id)
    {

        return $this->getCachedEntityById(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory', $Id);
    }

    /**
     * @param null|TblDirectory $tblDirectory
     *
     * @return false|TblDirectory[]
     */
    public function getDirectoryAllByParent(TblDirectory $tblDirectory = null)
    {

        return $this->getCachedEntityListBy(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory',
            array(
                TblDirectory::ATTR_TBL_DIRECTORY => ( $tblDirectory ? $tblDirectory->getId() : null )
            ));
    }

    /**
     * @return false|TblDirectory[]
     */
    public function getDirectoryAll()
    {

        return $this->getCachedEntityList(__METHOD__, $this->getConnection()->getEntityManager(), 'TblDirectory');
    }

    /**
     * @param string $BinaryBlob
     *
     * @return TblBinary
     */
    public function createBinary($BinaryBlob)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $New = new TblBinary();
        $New->setBinaryBlob($BinaryBlob);
        $Hash = $New->getHash();

        $Entity = $Manager->getEntity('TblBinary')->findOneBy(array(
            TblBinary::ATTR_HASH     => $Hash,
            TblBinary::ENTITY_REMOVE => null
        ));

        if (null === $Entity) {
            $Entity = $New;
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblBinary    $tblBinary
     * @param TblDirectory $tblDirectory
     * @param string       $Name
     * @param string       $Extension
     * @param string       $Type
     * @param string       $Description
     * @param bool         $IsLocked
     *
     * @return TblFile
     */
    public function createFile(
        TblBinary $tblBinary,
        TblDirectory $tblDirectory,
        $Name,
        $Extension,
        $Type,
        $Description = '',
        $IsLocked = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFile')->findOneBy(array(
            TblFile::ATTR_TBL_DIRECTORY => $tblDirectory->getId(),
            TblFile::ATTR_NAME          => $Name,
            TblFile::ATTR_EXTENSION     => $Extension,
            TblFile::ATTR_TYPE          => $Type,
            TblFile::ENTITY_REMOVE      => null
        ));

        if (null === $Entity) {
            $Entity = new TblFile();
            $Entity->setTblBinary($tblBinary);
            $Entity->setTblDirectory($tblDirectory);
            $Entity->setName($Name);
            $Entity->setExtension($Extension);
            $Entity->setType($Type);
            $Entity->setDescription($Description);
            $Entity->setLocked($IsLocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }
        return $Entity;
    }

    /**
     * @param TblFile      $tblFile
     * @param string       $Name
     * @param string       $Description
     * @param string       $FileName
     * @param string       $FileExtension
     * @param string       $FileContent
     * @param string       $FileType
     * @param int          $FileSize
     * @param TblDirectory $tblDirectory
     * @param bool         $IsLocked
     *
     * @return bool
     */
    public function updateFile(
        TblFile $tblFile,
        $Name,
        $Description,
        $FileName,
        $FileExtension,
        $FileContent,
        $FileType,
        $FileSize,
        TblDirectory $tblDirectory = null,
        $IsLocked = false
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblFile $Entity */
        $Entity = $Manager->getEntityById('TblFile', $tblFile->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setTblDirectory(( $tblDirectory ? $tblDirectory : null ));
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setFileName($FileName);
            $Entity->setFileExtension($FileExtension);
            $Entity->setFileContent($FileContent);
            $Entity->setFileType($FileType);
            $Entity->setFileSize($FileSize);
            $Entity->setLocked($IsLocked);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }

    /**
     * @param TblDirectory $tblDirectory
     * @param string       $Name
     * @param string       $Description
     * @param TblDirectory $tblDirectoryParent
     * @param bool         $IsLocked
     * @param string       $Identifier
     *
     * @return bool
     */
    public function updateDirectory(
        TblDirectory $tblDirectory,
        $Name,
        $Description,
        TblDirectory $tblDirectoryParent = null,
        $IsLocked = false,
        $Identifier = ''
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblDirectory $Entity */
        $Entity = $Manager->getEntityById('TblDirectory', $tblDirectory->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setTblDirectory($tblDirectoryParent);
            $Entity->setLocked($IsLocked);
            $Entity->setIdentifier($Identifier);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}
