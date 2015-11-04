<?php
namespace SPHERE\Application\Document\Explorer\Storage\Service;

use SPHERE\Application\Document\Explorer\Storage\Service\Entity\TblFile;
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
        // TODO: Implement setupDatabaseContent() method.
    }

    /**
     * @param int $Id
     *
     * @return false|TblFile
     */
    public function getFileById($Id)
    {

        $Entity = $this->getConnection()->getEntityManager()->getEntityById('TblFile', $Id);
        return ( $Entity ? $Entity : false );
    }

    /**
     * @return false|TblFile[]
     */
    public function getFileAll()
    {

        $EntityList = $this->getConnection()->getEntityManager()->getEntity('TblFile')->findAll();
        return ( !empty( $EntityList ) ? $EntityList : false );
    }

    /**
     * @param string $Name
     * @param string $Description
     * @param string $FileName
     * @param string $FileExtension
     * @param string $FileContent
     * @param string $FileType
     * @param int    $FileSize
     *
     * @return TblFile
     */
    public function createFile($Name, $Description, $FileName, $FileExtension, $FileContent, $FileType, $FileSize)
    {

        $Manager = $this->getConnection()->getEntityManager();

        $Entity = $Manager->getEntity('TblFile')->findOneBy(array(
            TblFile::ATTR_NAME => $Name
        ));

        if (null === $Entity) {
            $Entity = new TblFile();
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setFileName($FileName);
            $Entity->setFileExtension($FileExtension);
            $Entity->setFileContent($FileContent);
            $Entity->setFileType($FileType);
            $Entity->setFileSize($FileSize);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createInsertEntry($this->getConnection()->getDatabase(), $Entity);
        }

        return $Entity;
    }

    /**
     * @param TblFile $tblFile
     * @param string  $Name
     * @param string  $Description
     * @param string  $FileName
     * @param string  $FileExtension
     * @param string  $FileContent
     * @param string  $FileType
     * @param int     $FileSize
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
        $FileSize
    ) {

        $Manager = $this->getConnection()->getEntityManager();
        /** @var null|TblFile $Entity */
        $Entity = $Manager->getEntityById('TblFile', $tblFile->getId());
        if (null !== $Entity) {
            $Protocol = clone $Entity;
            $Entity->setName($Name);
            $Entity->setDescription($Description);
            $Entity->setFileName($FileName);
            $Entity->setFileExtension($FileExtension);
            $Entity->setFileContent($FileContent);
            $Entity->setFileType($FileType);
            $Entity->setFileSize($FileSize);
            $Manager->saveEntity($Entity);
            Protocol::useService()->createUpdateEntry($this->getConnection()->getDatabase(), $Protocol, $Entity);
            return true;
        }
        return false;
    }
}
